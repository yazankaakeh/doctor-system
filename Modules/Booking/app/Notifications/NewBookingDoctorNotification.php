<?php

/**
 * -----------------------------------------------------------------------------
 * NewBookingDoctorNotification
 * -----------------------------------------------------------------------------
 *
 * Notifies the DOCTOR as soon as a patient creates a booking on one of their
 * availability slots. Used to prompt the doctor to confirm, reschedule, or
 * decline.
 *
 * Delivered through:
 *   - mail       → full appointment + patient details
 *   - database   → bell-icon entry in the doctor portal
 *   - push       → Firebase notification when tokens are available
 *
 * Queued so the patient-facing request isn't blocked.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;
use Modules\Notification\App\Channels\SendPushNotificationChannel;

class NewBookingDoctorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Booking  $booking  The new booking that needs the doctor's attention.
     */
    public function __construct(
        private readonly Booking $booking
    ) {}

    /**
     * Delivery channels. Push added only when the doctor has Firebase tokens.
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
     * Build the new-booking email to the doctor. Layout:
     *   greeting → intro → patient details → appointment → notes → CTA.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('booking::booking.email.new_booking_subject'))
            ->greeting(__('booking::booking.email.greeting', ['name' => 'Dr. '.$notifiable->name]))
            ->line(__('booking::booking.email.new_booking_line1'))
            ->line('')
            ->line('---')
            ->line('');

        // -- Patient contact info ------------------------------------------
        $mail->line('**'.__('booking::booking.email.patient_details').'**')
            ->line('')
            ->line(__('booking::booking.email.patient_name_label').': **'.$this->booking->patient->name.'**');

        // Phone / email optional — only render when available.
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
            ->line(__('booking::booking.email.duration_label').': '.$this->booking->duration.' '.__('booking::booking.minutes'))
            ->line(__('booking::booking.email.fee_label').': **$'.number_format($this->booking->consultation_fee, 2).'**');

        // Include any notes the patient provided (symptoms / context).
        if ($this->booking->notes) {
            $mail->line('')
                ->line('---')
                ->line('')
                ->line('**'.__('booking::booking.email.patient_notes').':**')
                ->line($this->booking->notes);
        }

        $mail->line('')
            ->action(
                __('booking::booking.email.view_booking'),
                route('doctor.bookings.show', $this->booking)
            )
            ->line('')
            ->line(__('booking::booking.email.new_booking_footer'))
            ->line(__('booking::booking.email.thank_you'));

        return $mail;
    }

    /**
     * Database / in-app bell payload for the doctor dashboard.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_booking',
            'booking_id' => $this->booking->id,
            'patient_id' => $this->booking->patient_id,
            'patient_name' => $this->booking->patient->name,
            'patient_phone' => $this->booking->patient->phone,
            'booking_date' => $this->booking->booking_date->toDateString(),
            'start_time' => $this->booking->start_time->format('H:i'),
            'end_time' => $this->booking->end_time->format('H:i'),
            'duration' => $this->booking->duration,
            'consultation_fee' => $this->booking->consultation_fee,
            'notes' => $this->booking->notes,
            'message' => __('booking::booking.notification.new_booking'),
            'url' => route('doctor.bookings.show', $this->booking),
        ];
    }

    /**
     * Firebase push payload (doctor mobile app). `click_action.url` deep-links
     * straight to the booking detail page.
     */
    public function toFireBase(): array
    {
        return [
            'data' => [
                'title' => __('booking::booking.push.new_booking_title'),
                'body' => __('booking::booking.push.new_booking_body', [
                    'patient' => $this->booking->patient->name,
                    'date' => $this->booking->booking_date->format('M j, Y'),
                    'time' => $this->booking->start_time->format('H:i'),
                ]),
            ],
            'vibrate' => true,
            'sound' => 'default',
            'click_action' => [
                'type' => 'new_booking',
                'booking_id' => $this->booking->id,
                'url' => route('doctor.bookings.show', $this->booking),
            ],
        ];
    }
}
