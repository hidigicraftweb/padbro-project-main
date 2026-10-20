<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CityService
{
    /**
     * Get all cities with courts count
     */
    public function getAllCities(): Collection
    {
        return City::withCount('courts')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get city by ID or slug
     */
    public function getCity(string $identifier): ?City
    {
        return City::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->withCount('courts')
            ->first();
    }

    /**
     * Format city response for API
     */
    public function formatCityResponse(City $city): array
    {
        return [
            'id' => $city->id,
            'name' => $city->name,
            'slug' => $city->slug,
            'photo_url' => $city->photo ? url(Storage::url($city->photo)) : null,
            'courts_count' => $city->courts_count ?? $city->courts()->count(),
        ];
    }

    /**
     * Format multiple cities for API response
     */
    public function formatCitiesResponse(Collection $cities): array
    {
        return $cities->map(fn(City $city) => $this->formatCityResponse($city))->toArray();
    }
}
