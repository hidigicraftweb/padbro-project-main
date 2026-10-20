<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'court_id',
        'user_id',
        'start_at',
        'end_at',
        'total_hours',
        'price_per_hour_amount',
        'sub_total_amount',
        'total_tax_amount',
        'grand_total_amount',
        'status',
        'proof_of_payment',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'total_hours' => 'integer',
            'price_per_hour_amount' => 'integer',
            'sub_total_amount' => 'integer',
            'total_tax_amount' => 'integer',
            'grand_total_amount' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        // Create booking slots when booking is created
        static::created(function (Booking $booking) {
            $booking->createBookingSlots();
        });

        // Update booking slots status when booking status changes
        static::updated(function (Booking $booking) {
            if ($booking->isDirty('status')) {
                $booking->updateBookingSlotsStatus();
            }
        });

        // Delete booking slots when booking is deleted
        static::deleting(function (Booking $booking) {
            $booking->bookingSlots()->forceDelete();
        });
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookingSlots(): HasMany
    {
        return $this->hasMany(CourtBookingSlot::class);
    }

    protected function proofOfPaymentUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->attributes['proof_of_payment'] ? Storage::url($this->attributes['proof_of_payment']) : null,
        );
    }

    /**
     * Create booking slots for each hour of the booking
     */
    public function createBookingSlots(): void
    {
        $start = Carbon::parse($this->start_at);
        $end = Carbon::parse($this->end_at);

        // Determine slot status based on booking status
        $slotStatus = $this->status === 'paid' ? 'booked' : 'locked';
        $expiresAt = $slotStatus === 'locked' ? now()->addMinutes(1) : null;

        while ($start->lt($end)) {
            CourtBookingSlot::updateOrCreate(
                [
                    'court_id' => $this->court_id,
                    'slot_at' => $start->toDateTimeString(),
                ],
                [
                    'booking_id' => $this->id,
                    'status' => $slotStatus,
                    'expires_at' => $expiresAt,
                ]
            );

            $start->addHour();
        }
    }

    /**
     * Update booking slots status when booking status changes
     */
    public function updateBookingSlotsStatus(): void
    {
        // If booking is failed/rejected, permanently delete the slots (forceDelete to avoid unique constraint issues)
        if ($this->status === 'failed') {
            $this->bookingSlots()->forceDelete();
            return;
        }

        // paid = booked (permanent), pending = locked (with expiry)
        $slotStatus = $this->status === 'paid' ? 'booked' : 'locked';
        $expiresAt = $slotStatus === 'locked' ? now()->addMinutes(1) : null;

        $this->bookingSlots()->update([
            'status' => $slotStatus,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * Get formatted booking date
     */
    public function getBookingDateAttribute(): string
    {
        return $this->start_at->format('Y-m-d');
    }

    /**
     * Get formatted time slot
     */
    public function getTimeSlotAttribute(): string
    {
        return $this->start_at->format('H:i') . ' - ' . $this->end_at->format('H:i');
    }
}
