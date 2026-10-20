<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourtBookingSlot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slot_at',
        'court_id',
        'booking_id',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'slot_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
