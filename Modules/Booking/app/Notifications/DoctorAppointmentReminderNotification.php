<?php

/**
 * -----------------------------------------------------------------------------
 * DoctorAppointmentReminderNotification
 * -----------------------------------------------------------------------------
 *
 * Reminder sent to the DOCTOR before a confirmed appointment (sibling to
 * AppointmentReminderNotification which targets the patient).
 *
 * Delivered in two waves by the scheduler:
 *   - 24h before the appointment (reminderType = "24h")
 *   - 1h  before the appointment (reminderType = "1h")
 *
 * The `doctor_reminder_24h_sent_at` / `doctor_reminder_1h_sent_at` columns
 * on the Booking model record which waves have already been dispatched to
 * prevent double-sending.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;
use Modules\Notification\App\Channels\SendPushNotificationChannel;

class DoctorAppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Booking  $booking  Upcoming appointment.
     * @param  string  $reminderType  '24h' or '1h' – which wave we're in.
     */
    public function __construct(
        private readonly Booking $booking,
        private readonly string $reminderType = '24h' // '24h' or '1h'
    ) {}

    /**
     * Delivery channels. Push is only added when tokens exist.
     */
    public function via($notifiable): array
    {
        $channels = ['mail', 'database'];

        try {
            if (method_exists($notifiable, 'pushTokens') && $notifiable->pushTokens()->exists()) {
                $channels[] = SendPushNotificationChannel::class;
            }
        } catch (\Exception $e) {
            // push_tokens table missing — silently skip push.
        }

        return $channels;
    }

    /**
     * Build the email sent to the doctor. Layout:
     *   greeting → intro line → patient details → appointment details →
     *   patient notes (if any) → video CTA (if any) → footer.
     */
    public function toMail($notifiable): MailMessage
    {
        // Choose subject/intro based on which reminder wave we're in.
        $isOneHour = $this->reminderType === '1h';
        $subjectKey = $isOneHour
            ? 'booking::booking.email.doctor_reminder_1h_subject'
            : 'booking::booking.email.doctor_reminder_24h_subject';
        $line1Key = $isOneHour
            ? 'booking::booking.email.doctor_reminder_1h_line1'
            : 'booking::booking.email.doctor_reminder_24h_line1';

        $mail = (new MailMessage)
            ->subject(__($subjectKey))
            ->greeting(__('booking::booking.email.greeting', ['name' => 'Dr. '.$notifiable->name]))
            ->line(__($line1Key))
            ->line('')
            ->line('---')
            ->line('');

        // -- Patient contact block -----------------------------------------
        // Helpful so the doctor can phone/email the patient if anything comes up.
        $mail->line('**'.__('booking::booking.email.patient_details').'**')
            ->line('')
            ->line(__('booking::booking.email.patient_name_label').': **'.$this->booking->patient->name.'**');

        // Phone / email are optional — only include when present.
        if ($this->booking->patient->phone) {
            $mail->line(__('booking::booking.email.phone_label').': '.$this->booking->patient->phone);
        }

        if ($this->booking->patient->email) {
            $mail->line(__('booking::booking.email.email_label').': '.$this->booking->patient->email);
        }

        $mail->line('')
            ->line('---')
            ->line('');

        // -- Appointment summary -------------------------------------------
        $mail->line('**'.__('booking::booking.email.appointment_details').'**')
            ->line('')
            ->line(__('booking::booking.email.date_label').': **'.$this->booking->booking_date->format('l, F j, Y').'**')
            ->line(__('booking::booking.email.time_label').': **'.$this->booking->start_time->format('H:i').' - '.$this->booking->end_time->format('H:i').'**')
            ->line(__('booking::booking.email.duration_label').': '.$this->booking->duration.' '.__('booking::booking.minutes'));

        // Include any notes the patient left (symptoms, context, etc.).
        if ($this->booking->notes) {
            $mail->line('')
                ->line('---')
                ->line('')
                ->line('**'.__('booking::booking.email.patient_notes').':**')
                ->line($this->booking->notes);
        }

        $mail->line('')
            ->line('---')
            ->line('');

        // -- Video consultation CTA ----------------------------------------
        // Prefer the internal route so we can log when the doctor joins.
        if ($this->booking->meeting_link) {
            $mail->line('**'.__('booking::booking.email.video_consultation').'**')
                ->action(
                    __('booking::booking.email.join_consultation'),
                    $this->booking->hasMeetingRoom()
                        ? route('video.join', ['roomName' => $this->booking->getMeetingRoomName(), 'booking' => $this->booking->id])
                        : $this->booking->meeting_link
                )
                ->line('');
        }

        $mail->line(__('booking::booking.email.doctor_reminder_footer'))
            ->line(__('booking::booking.email.thank_you'));

        return $mail;
    }

    /**
     * Database / in-app bell payload for the doctor.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'doctor_appointment_reminder',
            'reminder_type' => $this->reminderType,
            'booking_id' => $this->booking->id,
            'patient_id' => $this->booking->patient_id,
            'patient_name' => $this->booking->patient->name,
            'patient_phone' => $this->booking->patient->phone,
            'booking_date' => $this->booking->booking_date->toDateString(),
            'start_time' => $this->booking->start_time->format('H:i'),
            'end_time' => $this->booking->end_time->format('H:i'),
            'meeting_link' => $this->booking->meeting_link,
            'message' => $this->reminderType === '1h'
                ? __('booking::booking.notification.doctor_reminder_1h')
                : __('booking::booking.notification.doctor_reminder_24h'),
            'url' => route('doctor.bookings.show', $this->booking),
        ];
    }

    /**
     * Firebase push payload for the doctor (mobile app).
     */
    public function toFireBase(): array
    {
        $titleKey = $this->reminderType === '1h'
            ? 'booking::booking.push.doctor_reminder_1h_title'
            : 'booking::booking.push.doctor_reminder_24h_title';
        $bodyKey = $this->reminderType === '1h'
            ? 'booking::booking.push.doctor_reminder_1h_body'
            : 'booking::booking.push.doctor_reminder_24h_body';

        return [
            'data' => [
                'title' => __($titleKey),
                'body' => __($bodyKey, [
                    'patient' => $this->booking->patient->name,
                    'date' => $this->booking->booking_date->format('M j, Y'),
                    'time' => $this->booking->start_time->format('H:i'),
                ]),
            ],
            'vibrate' => true,
            'sound' => 'default',
            'click_action' => [
                'type' => 'doctor_appointment_reminder',
                'booking_id' => $this->booking->id,
                'url' => route('doctor.bookings.show', $this->booking),
            ],
        ];
    }
}
