<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Booking Details')
                    ->tabs([
                        Tab::make('Booking Info')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Booking ID')
                                    ->weight('bold')
                                    ->copyable(),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('court.name')
                                    ->label('Court')
                                    ->icon('heroicon-o-building-office'),
                                TextEntry::make('court.city.name')
                                    ->label('City')
                                    ->icon('heroicon-o-map-pin'),
                                TextEntry::make('start_at')
                                    ->label('Booking Date')
                                    ->date('l, d F Y')
                                    ->icon('heroicon-o-calendar'),
                                TextEntry::make('time_slot')
                                    ->label('Time Slot')
                                    ->state(fn ($record) => $record->time_slot)
                                    ->icon('heroicon-o-clock'),
                                TextEntry::make('total_hours')
                                    ->label('Duration')
                                    ->suffix(' hours'),
                            ])
                            ->columns(2),

                        Tab::make('Customer')
                            ->icon('heroicon-o-user')
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Customer Name')
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('user.email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable(),
                                TextEntry::make('user.phone')
                                    ->label('Phone')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                                TextEntry::make('user.gender')
                                    ->label('Gender')
                                    ->badge(),
                            ])
                            ->columns(2),

                        Tab::make('Payment')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                TextEntry::make('price_per_hour_amount')
                                    ->label('Price per Hour')
                                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                                TextEntry::make('sub_total_amount')
                                    ->label('Sub Total')
                                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                                TextEntry::make('total_tax_amount')
                                    ->label('Tax (11%)')
                                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                                TextEntry::make('grand_total_amount')
                                    ->label('Grand Total')
                                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),
                                ImageEntry::make('proof_of_payment')
                                    ->label('Proof of Payment')
                                    ->height(300)
                                    ->extraImgAttributes(['class' => 'rounded-lg'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(4),

                        Tab::make('Court Details')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                TextEntry::make('court.name')
                                    ->label('Court Name'),
                                TextEntry::make('court.category.name')
                                    ->label('Category')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Indoor' => 'info',
                                        'Outdoor' => 'success',
                                        default => 'gray',
                                    }),
                                TextEntry::make('court.material')
                                    ->label('Material')
                                    ->badge(),
                                TextEntry::make('court.price_per_hour')
                                    ->label('Current Price/Hour')
                                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                                TextEntry::make('court.address')
                                    ->label('Address')
                                    ->columnSpanFull(),
                                TextEntry::make('court.phone')
                                    ->label('Court Phone')
                                    ->copyable(),
                            ])
                            ->columns(2),

                        Tab::make('Timestamps')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Booking Created')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime(),
                                TextEntry::make('deleted_at')
                                    ->label('Deleted At')
                                    ->dateTime()
                                    ->placeholder('Not deleted'),
                            ])
                            ->columns(3),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
