<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Repositories\BookingRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookingService
{
    private BookingRepository $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Create a pending booking and lock the time slots
     *
     * @throws \Exception
     */
    public function createPendingBooking(
        int $userId,
        int $courtId,
        string $date,
        int $startHour,
        int $endHour
    ): Booking {
        $court = Court::findOrFail($courtId);

        // Build datetime objects
        $startAt = Carbon::parse("{$date} {$startHour}:00:00");
        $endAt = Carbon::parse("{$date} {$endHour}:00:00");

        // Calculate amounts (business logic)
        $totalHours = $endHour - $startHour;
        $pricePerHour = (int) $court->price_per_hour;
        $subTotal = $totalHours * $pricePerHour;
        $taxAmount = (int) round($subTotal * 0.11);
        $grandTotal = $subTotal + $taxAmount;

        return DB::transaction(function () use (
            $userId, $courtId, $startAt, $endAt, $totalHours,
            $pricePerHour, $subTotal, $taxAmount, $grandTotal
        ) {
            // Check slot availability via repository
            if ($this->bookingRepository->hasConflictingSlots($courtId, $startAt, $endAt)) {
                throw new \Exception('Some time slots are no longer available');
            }

            // Create booking via repository
            return $this->bookingRepository->create([
                'user_id' => $userId,
                'court_id' => $courtId,
                'start_at' => $startAt,
                'end_at' => $endAt,
                'total_hours' => $totalHours,
                'price_per_hour_amount' => $pricePerHour,
                'sub_total_amount' => $subTotal,
                'total_tax_amount' => $taxAmount,
                'grand_total_amount' => $grandTotal,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Submit proof of payment for a pending booking (awaits manager approval)
     *
     * @throws \Exception
     */
    public function submitProofOfPayment(Booking $booking, string $proofOfPaymentPath): Booking
    {
        // Business rule: only pending bookings can submit proof
        if ($booking->status !== 'pending') {
            throw new \Exception('Booking cannot be updated');
        }

        // Business rule: check if booking has expired
        $slot = $this->bookingRepository->getFirstSlot($booking);
        if ($slot && $slot->expires_at && $slot->expires_at < now()) {
            $this->bookingRepository->update($booking, ['status' => 'failed']);
            throw new \Exception('Booking has expired');
        }

        // Save proof of payment
        $this->bookingRepository->update($booking, [
            'proof_of_payment' => $proofOfPaymentPath,
        ]);

        // Remove expiry so it doesn't auto-expire while waiting for approval
        $this->bookingRepository->updateBookingSlots($booking, [
            'expires_at' => null,
        ]);

        return $booking->fresh();
    }

    /**
     * Approve a booking (manager action)
     *
     * @throws \Exception
     */
    public function approveBooking(Booking $booking): Booking
    {
        // Business rule: only pending bookings with proof can be approved
        if ($booking->status !== 'pending' || !$booking->proof_of_payment) {
            throw new \Exception('Booking cannot be approved');
        }

        return $this->bookingRepository->update($booking, ['status' => 'paid']);
    }

    /**
     * Reject a booking (manager action)
     *
     * @throws \Exception
     */
    public function rejectBooking(Booking $booking): Booking
    {
        // Business rule: only pending bookings can be rejected
        if ($booking->status !== 'pending') {
            throw new \Exception('Booking cannot be rejected');
        }

        return $this->bookingRepository->update($booking, ['status' => 'failed']);
    }

    /**
     * Cancel a pending booking and release the slots
     */
    public function cancelBooking(Booking $booking): bool
    {
        // Business rule: only pending bookings can be cancelled
        if ($booking->status !== 'pending') {
            return false;
        }

        $this->bookingRepository->update($booking, ['status' => 'failed']);
        return true;
    }

    /**
     * Get a booking by ID for a specific user
     */
    public function getBookingForUser(int $bookingId, int $userId): ?Booking
    {
        return $this->bookingRepository->findForUser($bookingId, $userId);
    }

    /**
     * Get the next upcoming booking for a user
     */
    public function getNextBooking(int $userId): ?Booking
    {
        return $this->bookingRepository->getNextUpcoming($userId);
    }

    /**
     * Get user bookings with optional status filter
     */
    public function getUserBookings(int $userId, ?string $status = null): Collection
    {
        return match ($status) {
            'upcoming' => $this->bookingRepository->getUpcomingByUserId($userId),
            'pending' => $this->bookingRepository->getPendingByUserId($userId),
            'completed' => $this->bookingRepository->getCompletedByUserId($userId),
            'cancelled' => $this->bookingRepository->getCancelledByUserId($userId),
            default => $this->bookingRepository->getByUserId($userId),
        };
    }

    /**
     * Format booking for API response
     */
    public function formatBookingResponse(Booking $booking): array
    {
        $court = $booking->court;

        return [
            'id' => $booking->id,
            'date' => $booking->start_at->format('Y-m-d'),
            'date_formatted' => $this->formatBookingDate($booking->start_at),
            'start_time' => $booking->start_at->format('H:i'),
            'end_time' => $booking->end_at->format('H:i'),
            'time_slot' => $this->formatShortTime($booking->start_at) . ' - ' . $this->formatShortTime($booking->end_at),
            'total_hours' => $booking->total_hours,
            'price_per_hour' => $booking->price_per_hour_amount,
            'sub_total' => $booking->sub_total_amount,
            'tax_amount' => $booking->total_tax_amount,
            'grand_total' => $booking->grand_total_amount,
            'grand_total_formatted' => 'Rp ' . number_format($booking->grand_total_amount, 0, ',', '.'),
            'status' => $booking->status,
            'court' => [
                'id' => $court->id,
                'name' => $court->name,
                'thumbnail' => $court->thumbnail ? Storage::url($court->thumbnail) : null,
                'material' => $court->material,
                'address' => $court->address,
                'phone' => $court->phone,
                'city' => $court->city ? [
                    'id' => $court->city->id,
                    'name' => $court->city->name,
                ] : null,
                'category' => $court->category ? [
                    'id' => $court->category->id,
                    'name' => $court->category->name,
                ] : null,
            ],
            'created_at' => $booking->created_at->toISOString(),
        ];
    }

    /**
     * Format booking date in a user-friendly way
     * Short format: "Today", "Tomorrow", "Sun", "Jan 20"
     */
    protected function formatBookingDate(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'Today';
        }

        if ($date->isTomorrow()) {
            return 'Tomorrow';
        }

        if ($date->isCurrentWeek()) {
            return $date->format('D'); // Short day: Sun, Mon, Tue
        }

        return $date->format('M j'); // Jan 20, Feb 5
    }

    /**
     * Format time in short format (7PM, 10AM)
     */
    protected function formatShortTime(Carbon $date): string
    {
        $hour = (int) $date->format('g');
        $ampm = $date->format('A');
        return $hour . $ampm; // 7PM, 10AM
    }
}
