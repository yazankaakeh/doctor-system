<?php

namespace Modules\Booking\Console;

use Illuminate\Console\Command;
use Modules\Booking\Actions\Booking\CreateBookingConversationAction;
use Modules\Booking\Enums\BookingStatusEnum;
use Modules\Booking\Models\Booking;

class CreateMissingConversations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'booking:create-missing-conversations';

    /**
     * The console command description.
     */
    protected $description = 'Create conversations for confirmed bookings that do not have one';

    /**
     * Execute the console command.
     */
    public function handle(CreateBookingConversationAction $action): int
    {
        $bookings = Booking::where('status', BookingStatusEnum::CONFIRMED)
            ->whereDoesntHave('conversation')
            ->with(['doctor', 'patient'])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No bookings without conversations found.');
            return self::SUCCESS;
        }

        $this->info("Found {$bookings->count()} confirmed booking(s) without conversations.");

        $created = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            try {
                $conversation = $action->handle($booking);
                $this->info("✓ Created conversation for booking #{$booking->id}");
                $created++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to create conversation for booking #{$booking->id}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Created: {$created}");
        if ($failed > 0) {
            $this->warn("  Failed: {$failed}");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
