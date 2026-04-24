<?php

/**
 * -----------------------------------------------------------------------------
 * BookingConfirmedNotification (Patient)
 * -----------------------------------------------------------------------------
 *
 * Sent to the PATIENT the moment their booking transitions to CONFIRMED
 * (typically right after a successful payment or when the doctor approves
 * the request).
 *
 * Contents:
 *   - Appointment summary (doctor, specialty, date, time, duration, fee)
 *   - Video consultation CTA (meeting link / Jitsi room)
 *
 * Channels: mail, database, and Firebase push (when tokens are available).
 * Queued so the confirmation HTTP response stays snappy.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;
use Modules\Notification\App\Channels\SendPushNotificationChannel;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Booking  $booking  The booking that was just confirmed.
     */
    public function __construct(
        private readonly Booking $booking
    ) {}

    /**
     * Delivery channels. Mail + database are always used; push is added
     * when the notifiable has Firebase tokens registered.
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
     * Build the confirmation email (appointment summary + video CTA).
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('booking::booking.email.booking_confirmed_subject'))
            ->greeting(__('booking::booking.email.greeting', ['name' => $notifiable->name]))
            ->line(__('booking::booking.email.booking_confirmed_line1'))
            ->line('')
            ->line('---')
            ->line('');

        // -- Appointment Details block -------------------------------------
        $mail->line('**'.__('booking::booking.email.appointment_details').'**')
            ->line('')
            ->line(__('booking::booking.email.doctor_label').': **Dr. '.$this->booking->doctor->name.'**');

        // Specialty is optional.
        if ($this->booking->doctor->medicalSpecialty) {
            $mail->line(__('booking::booking.email.specialty_label').': '.$this->booking->doctor->medicalSpecialty->name);
        }

        $mail->line(__('booking::booking.email.date_label').': **'.$this->booking->booking_date->format('l, F j, Y').'**')
            ->line(__('booking::booking.email.time_label').': **'.$this->booking->start_time->format('H:i').' - '.$this->booking->end_time->format('H:i').'**')
            ->line(__('booking::booking.email.duration_label').': '.$this->booking->duration.' '.__('booking::booking.minutes'))
            ->line(__('booking::booking.email.fee_label').': **$'.number_format($this->booking->consultation_fee, 2).'**')
            ->line('')
            ->line('---')
            ->line('');

        // -- Video consultation CTA ---------------------------------------
        // Show a "Join" button that routes through our in-app wrapper when
        // the booking has a proper Jitsi room, else fall back to the raw link.
        if ($this->booking->meeting_link) {
            $mail->line('**'.__('booking::booking.email.video_consultation').'**')
                ->line(__('booking::booking.email.video_consultation_ready'))
                ->action(
                    __('booking::booking.email.join_consultation'),
                    $this->booking->hasMeetingRoom()
                        ? route('video.join', ['roomName' => $this->booking->getMeetingRoomName(), 'booking' => $this->booking->id])
                        : $this->booking->meeting_link
                )
                ->line('');
        }

        $mail->line(__('booking::booking.email.confirmed_footer'))
            ->line(__('booking::booking.email.thank_you'));

        return $mail;
    }

    /**
     * In-app notification payload (notifications table / bell dropdown).
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'booking_confirmed',
            'booking_id' => $this->booking->id,
            'doctor_id' => $this->booking->doctor_id,
            'doctor_name' => $this->booking->doctor->name,
            'booking_date' => $this->booking->booking_date->toDateString(),
            'start_time' => $this->booking->start_time->format('H:i'),
            'end_time' => $this->booking->end_time->format('H:i'),
            'meeting_link' => $this->booking->meeting_link,
            'meeting_room_name' => $this->booking->meeting_room_name,
            'message' => __('booking::booking.notification.booking_confirmed'),
            'url' => route('patient.bookings.show', $this->booking),
        ];
    }

    /**
     * Firebase push payload – consumed by SendPushNotificationChannel.
     * `click_action` tells the mobile app where to deep-link on tap.
     */
    public function toFireBase(): array
    {
        return [
            'data' => [
                'title' => __('booking::booking.push.booking_confirmed_title'),
                'body' => __('booking::booking.push.booking_confirmed_body', [
                    'doctor' => $this->booking->doctor->name,
                    'date' => $this->booking->booking_date->format('M j, Y'),
                    'time' => $this->booking->start_time->format('H:i'),
                ]),
            ],
            'vibrate' => true,
            'sound' => 'default',
            'click_action' => [
                'type' => 'booking_confirmed',
                'booking_id' => $this->booking->id,
                'url' => route('patient.bookings.show', $this->booking),
            ],
        ];
    }
}
