<?php

namespace Modules\Booking\Console;

use Illuminate\Console\Command;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;

class RegenerateMeetingRoom extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'booking:regenerate-meeting-rooms {--booking-id= : Specific booking ID to regenerate}';

    /**
     * The console command description.
     */
    protected $description = 'Regenerate meeting rooms for confirmed bookings with current video configuration (fixes lobby issues)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting meeting room regeneration...');

        $query = Booking::query()
            ->where('status', BookingStatusEnum::CONFIRMED)
            ->whereNotNull('meeting_link')
            ->whereNotNull('meeting_room_name');

        // If specific booking ID is provided
        if ($bookingId = $this->option('booking-id')) {
            $query->where('id', $bookingId);
        }

        $bookings = $query->with(['doctor', 'patient'])->get();

        if ($bookings->isEmpty()) {
            $this->warn('No confirmed bookings with meeting rooms found.');
            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} booking(s) to process.");

        $regenerated = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            try {
                $oldRoomName = $booking->meeting_room_name;

                // Regenerate the meeting room using the current video configuration
                $booking->generateMeetingRoom([
                    'expires_at' => $booking->booking_date->endOfDay(),
                ]);

                $this->info("✓ Booking #{$booking->id}: {$oldRoomName} → {$booking->meeting_room_name}");
                $regenerated++;
            } catch (\Exception $e) {
                $this->error("✗ Booking #{$booking->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Regeneration complete!");
        $this->info("Successfully regenerated: {$regenerated}");

        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
