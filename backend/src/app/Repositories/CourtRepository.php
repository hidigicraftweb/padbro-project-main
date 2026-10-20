<?php

namespace App\Repositories;

use App\Models\Court;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CourtRepository
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->applyFilters(Court::query(), $filters)
            ->with(['city', 'category'])
            ->where('status', 'active')
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Court
    {
        return Court::with(['city', 'category', 'timeSlots'])
            ->find($id);
    }

    public function findActiveById(int $id): ?Court
    {
        return Court::with(['city', 'category', 'timeSlots'])
            ->where('status', 'active')
            ->find($id);
    }

    public function getPopular(int $limit = 5): Collection
    {
        return Court::withCount(['bookings' => fn ($q) => $q->where('status', 'paid')])
            ->with(['city', 'category'])
            ->where('status', 'active')
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();
    }

    public function getFeatured(int $limit = 5): Collection
    {
        return Court::with(['city', 'category'])
            ->where('status', 'active')
            ->where('is_featured', true)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getByCityId(int $cityId, int $perPage = 10): LengthAwarePaginator
    {
        return Court::with(['city', 'category'])
            ->where('city_id', $cityId)
            ->where('status', 'active')
            ->latest()
            ->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Filter by city
        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        // Filter by category (indoor/outdoor)
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by material (grass/cement)
        if (!empty($filters['material'])) {
            $query->where('material', $filters['material']);
        }

        // Search by name, material, city name, or category name
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('material', 'like', '%' . $search . '%')
                    ->orWhereHas('city', fn ($cityQuery) =>
                        $cityQuery->where('name', 'like', '%' . $search . '%')
                    )
                    ->orWhereHas('category', fn ($categoryQuery) =>
                        $categoryQuery->where('name', 'like', '%' . $search . '%')
                    );
            });
        }

        // Filter by price range
        if (!empty($filters['min_price'])) {
            $query->where('price_per_hour', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price_per_hour', '<=', $filters['max_price']);
        }

        return $query;
    }
}
