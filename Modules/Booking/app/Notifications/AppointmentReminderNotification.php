<?php

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

    public function __construct(
        private readonly Booking $booking,
        private readonly string $reminderType = '24h' // '24h' or '1h'
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
        $isOneHour = $this->reminderType === '1h';
        $subjectKey = $isOneHour
            ? 'booking::booking.email.reminder_1h_subject'
            : 'booking::booking.email.reminder_24h_subject';
        $line1Key = $isOneHour
            ? 'booking::booking.email.reminder_1h_line1'
            : 'booking::booking.email.reminder_24h_line1';

        $mail = (new MailMessage)
            ->subject(__($subjectKey))
            ->greeting(__('booking::booking.email.greeting', ['name' => $notifiable->name]))
            ->line(__($line1Key))
            ->line('')
            ->line('---')
            ->line('');

        // Appointment Details
        $mail->line('**' . __('booking::booking.email.appointment_details') . '**')
            ->line('')
            ->line(__('booking::booking.email.doctor_label') . ': **Dr. ' . $this->booking->doctor->name . '**');

        if ($this->booking->doctor->medicalSpecialty) {
            $mail->line(__('booking::booking.email.specialty_label') . ': ' . $this->booking->doctor->medicalSpecialty->name);
        }

        $mail->line(__('booking::booking.email.date_label') . ': **' . $this->booking->booking_date->format('l, F j, Y') . '**')
            ->line(__('booking::booking.email.time_label') . ': **' . $this->booking->start_time->format('H:i') . ' - ' . $this->booking->end_time->format('H:i') . '**')
            ->line(__('booking::booking.email.duration_label') . ': ' . $this->booking->duration . ' ' . __('booking::booking.minutes'))
            ->line('')
            ->line('---')
            ->line('');

        // Video Consultation Link
        if ($this->booking->meeting_link) {
            $mail->line('**' . __('booking::booking.email.video_consultation') . '**')
                ->line(__('booking::booking.email.reminder_join_instruction'))
                ->action(
                    __('booking::booking.email.join_consultation'),
                    $this->booking->hasMeetingRoom()
                        ? route('video.join', ['roomName' => $this->booking->getMeetingRoomName(), 'booking' => $this->booking->id])
                        : $this->booking->meeting_link
                )
                ->line('');
        }

        $mail->line(__('booking::booking.email.reminder_footer'))
            ->line(__('booking::booking.email.thank_you'));

        return $mail;
    }

    /**
     * Get the array representation of the notification (for database).
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
     * Get the Firebase push notification representation.
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
