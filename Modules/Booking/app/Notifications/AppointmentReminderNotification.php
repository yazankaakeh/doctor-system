<?php

/**
 * -----------------------------------------------------------------------------
 * AppointmentReminderNotification (Patient)
 * -----------------------------------------------------------------------------
 *
 * Notification sent to the PATIENT before a confirmed appointment to remind
 * them about the upcoming consultation. Dispatched by the
 * `SendAppointmentRemindersCommand` scheduler in two waves:
 *
 *   - 24 hours before the appointment (`reminderType = "24h"`)
 *   - 1  hour  before the appointment (`reminderType = "1h"`)
 *
 * Channels used:
 *   - `mail`                        → email with video room button
 *   - `database`                    → Laravel notifications table (bell icon)
 *   - `SendPushNotificationChannel` → Firebase push (if the user has tokens)
 *
 * The "already sent" flags live on the Booking model (reminder_24h_sent_at,
 * reminder_1h_sent_at) so we never double-send.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;
use Modules\Notification\App\Channels\SendPushNotificationChannel;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Booking  $booking       The booking the reminder is about.
     * @param  string   $reminderType  Either '24h' (day-before) or '1h' (final nudge).
     */
    public function __construct(
        private readonly Booking $booking,
        private readonly string $reminderType = '24h' // '24h' or '1h'
    ) {}

    /**
     * Determine which channels this notification should be sent on.
     *
     * Mail + database are always used. Push notifications are added only
     * when the notifiable actually has Firebase tokens registered and the
     * push_tokens table exists (safe-guard for fresh installations).
     */
    public function via($notifiable): array
    {
        $channels = ['mail', 'database'];

        // Guard against environments where push tokens aren't configured.
        try {
            if (method_exists($notifiable, 'pushTokens') && $notifiable->pushTokens()->exists()) {
                $channels[] = SendPushNotificationChannel::class;
            }
        } catch (\Exception $e) {
            // push_tokens table missing — silently skip the push channel.
        }

        return $channels;
    }

    /**
     * Build the email version of the reminder.
     *
     * The subject/intro line switches between "24 hours" and "1 hour" wording
     * based on $reminderType. The body always includes:
     *   - appointment block (doctor, specialty, date/time, duration)
     *   - video consultation CTA (if a meeting room is attached)
     *   - footer + thank-you
     */
    public function toMail($notifiable): MailMessage
    {
        // Decide wording based on the reminder window.
        $isOneHour  = $this->reminderType === '1h';
        $subjectKey = $isOneHour
            ? 'booking::booking.email.reminder_1h_subject'
            : 'booking::booking.email.reminder_24h_subject';
        $line1Key   = $isOneHour
            ? 'booking::booking.email.reminder_1h_line1'
            : 'booking::booking.email.reminder_24h_line1';

        // Build the base mail message with the localized greeting + intro.
        $mail = (new MailMessage)
            ->subject(__($subjectKey))
            ->greeting(__('booking::booking.email.greeting', ['name' => $notifiable->name]))
            ->line(__($line1Key))
            ->line('')
            ->line('---')
            ->line('');

        // -- Appointment Details block -------------------------------------
        $mail->line('**'.__('booking::booking.email.appointment_details').'**')
            ->line('')
            ->line(__('booking::booking.email.doctor_label').': **Dr. '.$this->booking->doctor->name.'**');

        // Specialty is optional — some doctors don't have one configured.
        if ($this->booking->doctor->medicalSpecialty) {
            $mail->line(__('booking::booking.email.specialty_label').': '.$this->booking->doctor->medicalSpecialty->name);
        }

        $mail->line(__('booking::booking.email.date_label').': **'.$this->booking->booking_date->format('l, F j, Y').'**')
            ->line(__('booking::booking.email.time_label').': **'.$this->booking->start_time->format('H:i').' - '.$this->booking->end_time->format('H:i').'**')
            ->line(__('booking::booking.email.duration_label').': '.$this->booking->duration.' '.__('booking::booking.minutes'))
            ->line('')
            ->line('---')
            ->line('');

        // -- Video consultation CTA ----------------------------------------
        // When the booking has a Jitsi room attached, surface a prominent
        // "Join consultation" button. If the internal room helper is
        // available we route through our own wrapper (which enforces auth &
        // logs attendance); otherwise we fall back to the raw meeting link.
        if ($this->booking->meeting_link) {
            $mail->line('**'.__('booking::booking.email.video_consultation').'**')
                ->line(__('booking::booking.email.reminder_join_instruction'))
                ->action(
                    __('booking::booking.email.join_consultation'),
                    $this->booking->hasMeetingRoom()
                        ? route('video.join', ['roomName' => $this->booking->getMeetingRoomName(), 'booking' => $this->booking->id])
                        : $this->booking->meeting_link
                )
                ->line('');
        }

        // Footer + sign-off.
        $mail->line(__('booking::booking.email.reminder_footer'))
            ->line(__('booking::booking.email.thank_you'));

        return $mail;
    }

    /**
     * Payload persisted in the `notifications` table and surfaced in the
     * in-app bell dropdown. Keep this shape stable — the patient UI reads
     * these keys directly.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'appointment_reminder',
            'reminder_type' => $this->reminderType,
            'booking_id' => $this->booking->id,
            'doctor_id' => $this->booking->doctor_id,
            'doctor_name' => $this->booking->doctor->name,
            'booking_date' => $this->booking->booking_date->toDateString(),
            'start_time' => $this->booking->start_time->format('H:i'),
            'end_time' => $this->booking->end_time->format('H:i'),
            'meeting_link' => $this->booking->meeting_link,
            'meeting_room_name' => $this->booking->meeting_room_name,
            'message' => $this->reminderType === '1h'
                ? __('booking::booking.notification.reminder_1h')
                : __('booking::booking.notification.reminder_24h'),
            'url' => route('patient.bookings.show', $this->booking),
        ];
    }

    /**
     * Build the Firebase push notification payload.
     *
     * Returns a structure expected by `SendPushNotificationChannel`: a
     * title/body pair + a "click_action" describing which deep-link the
     * mobile app should open when the user taps the notification.
     */
    public function toFireBase(): array
    {
        $titleKey = $this->reminderType === '1h'
            ? 'booking::booking.push.reminder_1h_title'
            : 'booking::booking.push.reminder_24h_title';
        $bodyKey = $this->reminderType === '1h'
            ? 'booking::booking.push.reminder_1h_body'
            : 'booking::booking.push.reminder_24h_body';

        return [
            'data' => [
                'title' => __($titleKey),
                'body' => __($bodyKey, [
                    'doctor' => $this->booking->doctor->name,
                    'date' => $this->booking->booking_date->format('M j, Y'),
                    'time' => $this->booking->start_time->format('H:i'),
                ]),
            ],
            'vibrate' => true,
            'sound' => 'default',
            'click_action' => [
                'type' => 'appointment_reminder',
                'booking_id' => $this->booking->id,
                'url' => route('patient.bookings.show', $this->booking),
            ],
        ];
    }
}
