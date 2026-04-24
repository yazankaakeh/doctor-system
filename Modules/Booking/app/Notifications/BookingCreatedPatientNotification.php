<?php

/**
 * -----------------------------------------------------------------------------
 * BookingCreatedPatientNotification
 * -----------------------------------------------------------------------------
 *
 * Sent to the PATIENT immediately after they successfully submit a booking
 * request (before it is confirmed by the doctor / payment gateway).
 *
 * Purpose:
 *   - Acknowledge receipt of the request.
 *   - Surface a nicely formatted summary so they can verify the details.
 *   - Provide a quick link to review the booking in their portal.
 *
 * Delivered via mail + database, plus Firebase push when available. Queued
 * so the booking flow isn't blocked by email rendering.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;
use Modules\Notification\App\Channels\SendPushNotificationChannel;

class BookingCreatedPatientNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Booking  $booking  The booking that was just created.
     */
    public function __construct(
        private readonly Booking $booking
    ) {}

    /**
     * Delivery channels. Falls back gracefully when push tokens are missing.
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
     * Build the "booking received" email. Layout:
     *   greeting → intro line → appointment details → status → your notes
     *   → view-booking CTA → footer.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('booking::booking.email.booking_created_subject'))
            ->greeting(__('booking::booking.email.greeting', ['name' => $notifiable->name]))
            ->line(__('booking::booking.email.booking_created_line1'))
            ->line('')
            ->line('---')
            ->line('');

        // -- Appointment details block -------------------------------------
        $mail->line('**'.__('booking::booking.email.appointment_details').'**')
            ->line('')
            ->line(__('booking::booking.email.doctor_label').': **Dr. '.$this->booking->doctor->name.'**');

        // Specialty line is only added when the doctor actually has one.
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

        // Current status (pending/confirmed/…). label() is a human-friendly string.
        $mail->line(__('booking::booking.email.status_label').': '.$this->booking->status->label());

        // Surface the patient's own notes back to them as a confirmation.
        if ($this->booking->notes) {
            $mail->line('')
                ->line('**'.__('booking::booking.email.your_notes').':**')
                ->line($this->booking->notes);
        }

        $mail->line('')
            ->action(
                __('booking::booking.email.view_booking'),
                route('patient.bookings.show', $this->booking)
            )
            ->line('')
            ->line(__('booking::booking.email.booking_created_footer'))
            ->line(__('booking::booking.email.thank_you'));

        return $mail;
    }

    /**
     * In-app notification payload (notifications table / bell dropdown).
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'booking_created',
            'booking_id' => $this->booking->id,
            'doctor_id' => $this->booking->doctor_id,
            'doctor_name' => $this->booking->doctor->name,
            'doctor_specialty' => $this->booking->doctor->medicalSpecialty?->name,
            'booking_date' => $this->booking->booking_date->toDateString(),
            'start_time' => $this->booking->start_time->format('H:i'),
            'end_time' => $this->booking->end_time->format('H:i'),
            'duration' => $this->booking->duration,
            'consultation_fee' => $this->booking->consultation_fee,
            'status' => $this->booking->status->value,
            'status_label' => $this->booking->status->label(),
            'message' => __('booking::booking.notification.booking_created'),
            'url' => route('patient.bookings.show', $this->booking),
        ];
    }

    /**
     * Firebase push payload consumed by SendPushNotificationChannel.
     */
    public function toFireBase(): array
    {
        return [
            'data' => [
                'title' => __('booking::booking.push.booking_created_title'),
                'body' => __('booking::booking.push.booking_created_body', [
                    'doctor' => $this->booking->doctor->name,
                    'date' => $this->booking->booking_date->format('M j, Y'),
                    'time' => $this->booking->start_time->format('H:i'),
                ]),
            ],
            'vibrate' => true,
            'sound' => 'default',
            'click_action' => [
                'type' => 'booking_created',
                'booking_id' => $this->booking->id,
                'url' => route('patient.bookings.show', $this->booking),
            ],
        ];
    }
}
