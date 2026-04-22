<?php

namespace Modules\Doctor\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Doctor\Models\MedicalExamination;
use Modules\Notification\App\Channels\SendDBChannel;
use Modules\Notification\App\Channels\SendPushNotificationChannel;

/**
 * Patient-facing notification fired when a doctor submits / finalizes a
 * medical examination (i.e. the visit is DONE and a prescription /
 * impression is now available).
 *
 * Delivery channels:
 *   - mail            : email summary with a link to the prescription
 *   - SendDBChannel   : writes into the custom `notifications` table
 *                       (which the bell dropdown reads from) — triggers
 *                       the Reverb broadcast via the model's booted()
 *                       hook, so the UI updates in real-time
 *   - push (optional) : Firebase push when the patient has a token
 *
 * NOTE: we use the custom SendDBChannel (instead of Laravel's default
 * 'database' channel) because the `notifications` table in this app has
 * an auto-increment id + boolean `read` column, which is incompatible
 * with Laravel's default UUID + `read_at` schema.
 */
class ExaminationCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly MedicalExamination $examination,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        $channels = ['mail', SendDBChannel::class];

        try {
            if (method_exists($notifiable, 'pushTokens') && $notifiable->pushTokens()->exists()) {
                $channels[] = SendPushNotificationChannel::class;
            }
        } catch (\Throwable $e) {
            // push_tokens table may not exist / migration missing — skip push
        }

        return $channels;
    }

    /**
     * Email delivery.
     */
    public function toMail($notifiable): MailMessage
    {
        $patientName = $notifiable->name ?? '';
        $doctorName = optional($this->examination->doctor)->name ?? '';

        return (new MailMessage)
            ->subject('Your examination has been completed')
            ->greeting('Hello '.$patientName)
            ->line('Your medical examination with Dr. '.$doctorName.' has been completed.')
            ->line('Your prescription and medical impression are now available in your patient portal.')
            ->action('View Prescription', url('/'))
            ->line('Thank you for using our service.');
    }

    /**
     * Custom DB channel payload — goes into the app's `notifications`
     * table and is picked up by the bell dropdown + Echo broadcast.
     */
    public function toDB($notifiable): array
    {
        $doctorName = optional($this->examination->doctor)->name ?? '';

        return [
            'type' => self::class,
            'data' => [
                'title' => 'Examination completed',
                'message' => 'Dr. '.$doctorName.' finalized your examination. Your prescription is ready.',
                'examination_id' => $this->examination->id,
                'doctor_id' => $this->examination->doctor_id,
                'doctor_name' => $doctorName,
            ],
            'action_key' => 'examination_id',
            'action_value' => (string) $this->examination->id,
        ];
    }

    /**
     * Firebase push payload (used by SendPushNotificationChannel).
     */
    public function toFireBase(): array
    {
        $doctorName = optional($this->examination->doctor)->name ?? '';

        return [
            'data' => [
                'title' => 'Examination completed',
                'body' => 'Dr. '.$doctorName.' finalized your examination. Tap to view your prescription.',
            ],
            'vibrate' => true,
            'sound' => 'default',
            'click_action' => [
                'type' => 'examination_completed',
                'examination_id' => (string) $this->examination->id,
            ],
        ];
    }
}
