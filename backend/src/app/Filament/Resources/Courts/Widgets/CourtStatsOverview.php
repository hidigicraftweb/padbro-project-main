<?php

namespace App\Filament\Resources\Courts\Widgets;

use App\Models\Booking;
use App\Models\Court;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CourtStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalCourts = Court::count();
        $activeCourts = Court::where('status', 'active')->count();
        $inactiveCourts = Court::where('status', 'inactive')->count();

        $avgPricePerHour = Court::where('status', 'active')->avg('price_per_hour') ?? 0;

        $totalCourtBookings = Booking::count();
        $totalCourtRevenue = Booking::where('status', 'paid')->sum('grand_total_amount');

        $mostPopularCourt = Court::withCount(['bookings' => fn ($q) => $q->where('status', 'paid')])
            ->orderByDesc('bookings_count')
            ->first();

        return [
            Stat::make('Total Courts', $totalCourts)
                ->description('All registered courts')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Active Courts', $activeCourts)
                ->description('Available for booking')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Inactive Courts', $inactiveCourts)
                ->description('Currently unavailable')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Avg. Price/Hour', 'Rp ' . number_format($avgPricePerHour, 0, ',', '.'))
                ->description('Active courts average')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('info'),

            Stat::make('Total Bookings', $totalCourtBookings)
                ->description('All court bookings')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('warning'),

            Stat::make('Most Popular', $mostPopularCourt?->name ?? '-')
                ->description($mostPopularCourt ? $mostPopularCourt->bookings_count . ' bookings' : 'No bookings yet')
                ->descriptionIcon('heroicon-o-star')
                ->color('success'),
        ];
    }
}
