<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\CourtBookingSlot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingRepository
{
    /**
     * Create a new booking
     */
    public function create(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            return Booking::create($data);
        });
    }

    /**
     * Update booking by model
     */
    public function update(Booking $booking, array $data): Booking
    {
        $booking->update($data);
        return $booking->fresh();
    }

    /**
     * Find booking by ID with relations
     */
    public function findWithRelations(int $id, array $relations = ['court.city', 'court.category']): ?Booking
    {
        return Booking::with($relations)->find($id);
    }

    /**
     * Find booking by ID for a specific user
     */
    public function findForUser(int $bookingId, int $userId, array $relations = ['court.city', 'court.category']): ?Booking
    {
        return Booking::with($relations)
            ->where('id', $bookingId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get the next upcoming booking for a user (paid, future)
     */
    public function getNextUpcoming(int $userId, array $relations = ['court.city', 'court.category']): ?Booking
    {
        return Booking::with($relations)
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->where('start_at', '>', now())
            ->orderBy('start_at', 'asc')
            ->first();
    }

    /**
     * Get user bookings with optional filters
     */
    public function getByUserId(int $userId, array $relations = ['court.city', 'court.category']): Collection
    {
        return Booking::with($relations)
            ->where('user_id', $userId)
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * Get user upcoming bookings (paid, future)
     */
    public function getUpcomingByUserId(int $userId, array $relations = ['court.city', 'court.category']): Collection
    {
        return Booking::with($relations)
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->where('start_at', '>', now())
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * Get user pending bookings
     */
    public function getPendingByUserId(int $userId, array $relations = ['court.city', 'court.category']): Collection
    {
        return Booking::with($relations)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * Get user completed bookings (paid, past)
     */
    public function getCompletedByUserId(int $userId, array $relations = ['court.city', 'court.category']): Collection
    {
        return Booking::with($relations)
            ->where('user_id', $userId)
            ->where('status', 'paid')
            ->where('end_at', '<', now())
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * Get user cancelled/failed bookings
     */
    public function getCancelledByUserId(int $userId, array $relations = ['court.city', 'court.category']): Collection
    {
        return Booking::with($relations)
            ->where('user_id', $userId)
            ->where('status', 'failed')
            ->orderBy('start_at', 'desc')
            ->get();
    }

    /**
     * Check if slots are available (not booked or locked)
     */
    public function hasConflictingSlots(int $courtId, \Carbon\Carbon $startAt, \Carbon\Carbon $endAt): bool
    {
        return CourtBookingSlot::where('court_id', $courtId)
            ->where('slot_at', '>=', $startAt)
            ->where('slot_at', '<', $endAt)
            ->where(function ($query) {
                $query->where('status', 'booked')
                    ->orWhere(function ($q) {
                        $q->where('status', 'locked')
                            ->where(function ($inner) {
                                $inner->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                            });
                    });
            })
            ->exists();
    }

    /**
     * Get the first booking slot
     */
    public function getFirstSlot(Booking $booking): ?CourtBookingSlot
    {
        return $booking->bookingSlots()->first();
    }

    /**
     * Update all booking slots
     */
    public function updateBookingSlots(Booking $booking, array $data): int
    {
        return $booking->bookingSlots()->update($data);
    }
}
