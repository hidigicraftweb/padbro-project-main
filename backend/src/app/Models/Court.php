<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Court extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'city_id',
        'category_id',
        'material',
        'price_per_hour',
        'about',
        'photos',
        'features',
        'thumbnail',
        'address',
        'phone',
        'status',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'features' => 'array',
            'price_per_hour' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->name);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && !$model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->name, $model->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->withTrashed()
            ->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(CourtTimeSlot::class);
    }

    public function bookingSlots(): HasMany
    {
        return $this->hasMany(CourtBookingSlot::class);
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->attributes['thumbnail'] ? Storage::url($this->attributes['thumbnail']) : null,
        );
    }

    protected function photosUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $photos = $this->attributes['photos'] ?? null;
                if (!$photos) {
                    return [];
                }
                $photosArray = json_decode($photos, true) ?? [];
                return array_map(fn($photo) => Storage::url($photo), $photosArray);
            },
        );
    }
}
