<?php

namespace Modules\Booking\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;
use Modules\Booking\Notifications\AppointmentReminderNotification;
use Modules\Booking\Notifications\DoctorAppointmentReminderNotification;

class SendAppointmentRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:send-reminders
                            {--type=all : Type of reminders to send (24h, 1h, or all)}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminder notifications to patients and doctors';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        $this->info('Starting appointment reminder notifications...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $sentCount = [
            'patient_24h' => 0,
            'patient_1h' => 0,
            'doctor_24h' => 0,
            'doctor_1h' => 0,
        ];

        // Send 24-hour reminders
        if (in_array($type, ['all', '24h'])) {
            $this->info('Processing 24-hour reminders...');
            $sent = $this->send24HourReminders($dryRun);
            $sentCount['patient_24h'] = $sent['patient'];
            $sentCount['doctor_24h'] = $sent['doctor'];
        }

        // Send 1-hour reminders
        if (in_array($type, ['all', '1h'])) {
            $this->info('Processing 1-hour reminders...');
            $sent = $this->send1HourReminders($dryRun);
            $sentCount['patient_1h'] = $sent['patient'];
            $sentCount['doctor_1h'] = $sent['doctor'];
        }

        // Summary
        $this->newLine();
        $this->info('=== Summary ===');
        $this->table(
            ['Type', 'Patient Reminders', 'Doctor Reminders'],
            [
                ['24-hour', $sentCount['patient_24h'], $sentCount['doctor_24h']],
                ['1-hour', $sentCount['patient_1h'], $sentCount['doctor_1h']],
                ['Total', $sentCount['patient_24h'] + $sentCount['patient_1h'], $sentCount['doctor_24h'] + $sentCount['doctor_1h']],
            ]
        );

        $this->info('Appointment reminders completed.');

        return self::SUCCESS;
    }

    /**
     * Send 24-hour reminders for appointments happening tomorrow.
     */
    private function send24HourReminders(bool $dryRun): array
    {
        $now = Carbon::now();
        $targetStart = $now->copy()->addHours(23);
        $targetEnd = $now->copy()->addHours(25);

        // Find confirmed bookings within the 24-hour window that haven't received 24h reminder
        $bookings = $this->getBookingsInTimeRange($targetStart, $targetEnd, 'reminder_24h_sent_at', 'doctor_reminder_24h_sent_at');

        $patientCount = 0;
        $doctorCount = 0;

        foreach ($bookings as $booking) {
            // Send patient reminder
            if (! $booking->reminder_24h_sent_at) {
                if ($dryRun) {
                    $this->line("  [DRY] Would send 24h patient reminder for booking #{$booking->id} to {$booking->patient->name}");
                } else {
                    $booking->patient->notify(new AppointmentReminderNotification($booking, '24h'));
                    $booking->update(['reminder_24h_sent_at' => now()]);
                    $this->line("  Sent 24h patient reminder for booking #{$booking->id}");
                }
                $patientCount++;
            }

            // Send doctor reminder
            if (! $booking->doctor_reminder_24h_sent_at) {
                if ($dryRun) {
                    $this->line("  [DRY] Would send 24h doctor reminder for booking #{$booking->id} to Dr. {$booking->doctor->name}");
                } else {
                    $booking->doctor->notify(new DoctorAppointmentReminderNotification($booking, '24h'));
                    $booking->update(['doctor_reminder_24h_sent_at' => now()]);
                    $this->line("  Sent 24h doctor reminder for booking #{$booking->id}");
                }
                $doctorCount++;
            }
        }

        return ['patient' => $patientCount, 'doctor' => $doctorCount];
    }

    /**
     * Send 1-hour reminders for appointments happening soon.
     */
    private function send1HourReminders(bool $dryRun): array
    {
        $now = Carbon::now();
        $targetStart = $now->copy()->addMinutes(50);
        $targetEnd = $now->copy()->addMinutes(70);

        // Find confirmed bookings within the 1-hour window that haven't received 1h reminder
        $bookings = $this->getBookingsInTimeRange($targetStart, $targetEnd, 'reminder_1h_sent_at', 'doctor_reminder_1h_sent_at');

        $patientCount = 0;
        $doctorCount = 0;

        foreach ($bookings as $booking) {
            // Send patient reminder
            if (! $booking->reminder_1h_sent_at) {
                if ($dryRun) {
                    $this->line("  [DRY] Would send 1h patient reminder for booking #{$booking->id} to {$booking->patient->name}");
                } else {
                    $booking->patient->notify(new AppointmentReminderNotification($booking, '1h'));
                    $booking->update(['reminder_1h_sent_at' => now()]);
                    $this->line("  Sent 1h patient reminder for booking #{$booking->id}");
                }
                $patientCount++;
            }

            // Send doctor reminder
            if (! $booking->doctor_reminder_1h_sent_at) {
                if ($dryRun) {
                    $this->line("  [DRY] Would send 1h doctor reminder for booking #{$booking->id} to Dr. {$booking->doctor->name}");
                } else {
                    $booking->doctor->notify(new DoctorAppointmentReminderNotification($booking, '1h'));
                    $booking->update(['doctor_reminder_1h_sent_at' => now()]);
                    $this->line("  Sent 1h doctor reminder for booking #{$booking->id}");
                }
                $doctorCount++;
            }
        }

        return ['patient' => $patientCount, 'doctor' => $doctorCount];
    }

    /**
     * Get bookings within a specific time range that need reminders.
     */
    private function getBookingsInTimeRange(
        Carbon $targetStart,
        Carbon $targetEnd,
        string $patientReminderColumn,
        string $doctorReminderColumn
    ) {
        return Booking::query()
            ->with(['patient', 'doctor.medicalSpecialty'])
            ->where('status', BookingStatusEnum::CONFIRMED)
            ->where(function ($query) use ($targetStart, $targetEnd) {
                // Compare full datetime (date + time)
                $query->whereRaw(
                    "CONCAT(booking_date, ' ', TIME(start_time)) >= ?",
                    [$targetStart->format('Y-m-d H:i:s')]
                )
                    ->whereRaw(
                        "CONCAT(booking_date, ' ', TIME(start_time)) <= ?",
                        [$targetEnd->format('Y-m-d H:i:s')]
                    );
            })
            ->where(function ($query) use ($patientReminderColumn, $doctorReminderColumn) {
                // Either patient or doctor reminder not sent
                $query->whereNull($patientReminderColumn)
                    ->orWhereNull($doctorReminderColumn);
            })
            ->get();
    }
}
