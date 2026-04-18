<?php

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

    public function __construct(
        private readonly Booking $booking
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['mail', 'database'];

        // Add push notification if user has tokens
        try {
            if (method_exists($notifiable, 'pushTokens') && $notifiable->pushTokens()->exists()) {
                $channels[] = SendPushNotificationChannel::class;
            }
        } catch (\Exception $e) {
            // Push tokens table may not exist, skip push notifications
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
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

        // Appointment Details Section
        $mail->line('**'.__('booking::booking.email.appointment_details').'**')
            ->line('')
            ->line(__('booking::booking.email.doctor_label').': **Dr. '.$this->booking->doctor->name.'**');

        // Add specialty if available
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

        // Status
        $mail->line(__('booking::booking.email.status_label').': '.$this->booking->status->label());

        // Notes if any
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
     * Get the array representation of the notification (for database).
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
     * Get the Firebase push notification representation.
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
