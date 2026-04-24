<?php

/**
 * -----------------------------------------------------------------------------
 * BookingCancelledNotification
 * -----------------------------------------------------------------------------
 *
 * Sent to the doctor when a patient cancels a confirmed booking so the doctor
 * knows the slot has freed up again. Delivered through email + database
 * (bell-icon) channels.
 *
 * Dispatched from `CancelBookingAction`.
 * -----------------------------------------------------------------------------
 */

namespace Modules\Booking\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class BookingCancelledNotification extends Notification
{
    use Queueable;

    /**
     * @param  Booking  $booking  The booking that was just cancelled.
     */
    public function __construct(
        private readonly Booking $booking
    ) {}

    /**
     * Send via email + database (in-app bell). No push here – the doctor
     * portal is web-only.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Build the email to the doctor. Includes the freed slot details and
     * the patient-supplied cancellation reason (if any).
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('booking::booking.email.booking_cancelled_subject'))
            ->greeting(__('booking::booking.email.greeting', ['name' => $notifiable->name]))
            ->line(__('booking::booking.email.booking_cancelled_line1'))
            // Summary of the cancelled appointment.
            ->line(__('booking::booking.email.cancelled_details', [
                'patient' => $this->booking->patient->name,
                'date' => $this->booking->booking_date->format('F j, Y'),
                'time' => $this->booking->start_time->format('H:i'),
            ]))
            // Only include the reason line if the patient actually provided one.
            ->when($this->booking->cancellation_reason, function ($mail) {
                return $mail->line(__('booking::booking.email.cancellation_reason', [
                    'reason' => $this->booking->cancellation_reason,
                ]));
            })
            ->line(__('booking::booking.email.slot_available_again'));
    }

    /**
     * Payload stored in the database/notifications table and displayed in the
     * in-app bell dropdown.
     */
    public function toArray($notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'patient_name' => $this->booking->patient->name,
            'booking_date' => $this->booking->booking_date->toDateString(),
            'start_time' => $this->booking->start_time->format('H:i'),
            'cancellation_reason' => $this->booking->cancellation_reason,
            'message' => __('booking::booking.notification.booking_cancelled'),
        ];
    }
}
