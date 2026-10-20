<?php

namespace App\Filament\Resources\Bookings\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalBookings = Booking::count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $paidBookings = Booking::where('status', 'paid')->count();
        $failedBookings = Booking::where('status', 'failed')->count();

        $totalRevenue = Booking::where('status', 'paid')->sum('grand_total_amount');
        $todayBookings = Booking::whereDate('start_at', today())->count();
        $thisMonthRevenue = Booking::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total_amount');

        return [
            Stat::make('Total Bookings', $totalBookings)
                ->description('All time bookings')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),

            Stat::make('Pending Approval', $pendingBookings)
                ->description('Awaiting payment verification')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Confirmed Bookings', $paidBookings)
                ->description('Successfully paid')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Failed Bookings', $failedBookings)
                ->description('Rejected or expired')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('From paid bookings')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('This Month Revenue', 'Rp ' . number_format($thisMonthRevenue, 0, ',', '.'))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}
