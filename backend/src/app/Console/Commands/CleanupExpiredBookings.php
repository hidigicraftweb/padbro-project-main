<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\CourtBookingSlot;
use Illuminate\Console\Command;

class CleanupExpiredBookings extends Command
{
    protected $signature = 'bookings:cleanup-expired';

    protected $description = 'Clean up expired pending bookings and release their slots';

    public function handle(): int
    {
        // Find expired locked slots (only those WITH an expiry date)
        // Slots without expiry are waiting for manager approval - don't touch them
        $expiredSlots = CourtBookingSlot::where('status', 'locked')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredSlots->isEmpty()) {
            $this->info('No expired slots found.');
            return Command::SUCCESS;
        }

        // Get unique booking IDs
        $bookingIds = $expiredSlots->pluck('booking_id')->unique()->filter();

        // Delete expired slots
        $deletedSlots = CourtBookingSlot::where('status', 'locked')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deletedSlots} expired slot(s).");

        // Mark associated bookings as failed (only those without proof of payment)
        if ($bookingIds->isNotEmpty()) {
            $updatedBookings = Booking::whereIn('id', $bookingIds)
                ->where('status', 'pending')
                ->whereNull('proof_of_payment')
                ->update(['status' => 'failed']);

            $this->info("Marked {$updatedBookings} booking(s) as failed.");
        }

        return Command::SUCCESS;
    }
}
