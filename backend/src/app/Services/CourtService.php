<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\CourtBookingSlot;
use App\Repositories\CourtRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CourtService
{
    protected CourtRepository $courtRepository;

    public function __construct(CourtRepository $courtRepository)
    {
        $this->courtRepository = $courtRepository;
    }

    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->courtRepository->getPaginated($filters, $perPage);
    }

    public function findById(int $id): ?Court
    {
        return $this->courtRepository->findActiveById($id);
    }

    public function getPopular(int $limit = 5): Collection
    {
        return $this->courtRepository->getPopular($limit);
    }

    public function getFeatured(int $limit = 5): Collection
    {
        return $this->courtRepository->getFeatured($limit);
    }

    /**
     * Get available time slots for a court on a specific date
     */
    public function getAvailableSlots(Court $court, string $date): array
    {
        $dateObj = Carbon::parse($date);
        $dayOfWeek = $dateObj->dayOfWeek;

        // Get court's operating hours for this day
        $timeSlots = $court->timeSlots()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        if ($timeSlots->isEmpty()) {
            return [];
        }

        // Get already booked/locked slots for this court and date
        $bookedSlots = CourtBookingSlot::where('court_id', $court->id)
            ->whereDate('slot_at', $dateObj)
            ->where(function ($query) {
                $query->where('status', 'booked')
                    ->orWhere(function ($q) {
                        $q->where('status', 'locked')
                            ->where(function ($inner) {
                                // Locked with no expiry (waiting for approval) OR not yet expired
                                $inner->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                            });
                    });
            })
            ->pluck('slot_at')
            ->map(fn ($slot) => Carbon::parse($slot)->format('H:i'))
            ->toArray();

        // Build available slots
        $availableSlots = [];

        foreach ($timeSlots as $slot) {
            $start = Carbon::parse($slot->start_time);
            $end = Carbon::parse($slot->end_time);

            while ($start->lt($end)) {
                $timeString = $start->format('H:i');

                $availableSlots[] = [
                    'time' => $timeString,
                    'available' => !in_array($timeString, $bookedSlots),
                ];

                $start->addHour();
            }
        }

        return $availableSlots;
    }

    /**
     * Get booking count for a court in the current month
     */
    public function getBookingsThisMonth(int $courtId): int
    {
        return Booking::where('court_id', $courtId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    /**
     * Format court for API response
     */
    public function formatCourtResponse(Court $court): array
    {
        return [
            'id' => $court->id,
            'name' => $court->name,
            'about' => $court->about,
            'thumbnail' => $court->thumbnail ? Storage::url($court->thumbnail) : null,
            'photos' => $this->formatPhotos($court->photos),
            'features' => $court->features ?? [],
            'material' => $court->material,
            'price_per_hour' => $court->price_per_hour,
            'price_per_hour_formatted' => 'Rp ' . number_format($court->price_per_hour, 0, ',', '.'),
            'address' => $court->address,
            'phone' => $court->phone,
            'status' => $court->status,
            'bookings_this_month' => $this->getBookingsThisMonth($court->id),
            'city' => $court->city ? [
                'id' => $court->city->id,
                'name' => $court->city->name,
            ] : null,
            'category' => $court->category ? [
                'id' => $court->category->id,
                'name' => $court->category->name,
            ] : null,
            'created_at' => $court->created_at?->toISOString(),
        ];
    }

    /**
     * Format court list item for API response (lighter version)
     */
    public function formatCourtListItem(Court $court): array
    {
        return [
            'id' => $court->id,
            'name' => $court->name,
            'thumbnail' => $court->thumbnail ? Storage::url($court->thumbnail) : null,
            'material' => $court->material,
            'price_per_hour' => $court->price_per_hour,
            'price_per_hour_formatted' => 'Rp ' . number_format($court->price_per_hour, 0, ',', '.'),
            'address' => $court->address,
            'city' => $court->city ? [
                'id' => $court->city->id,
                'name' => $court->city->name,
            ] : null,
            'category' => $court->category ? [
                'id' => $court->category->id,
                'name' => $court->category->name,
            ] : null,
        ];
    }

    /**
     * Format photos array with full URLs
     */
    protected function formatPhotos(?array $photos): array
    {
        if (empty($photos)) {
            return [];
        }

        return array_map(fn ($photo) => Storage::url($photo), $photos);
    }
}
