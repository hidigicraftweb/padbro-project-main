<?php

namespace App\Filament\Resources\Courts\Schemas;

use App\Models\CourtTimeSlot;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CourtInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Court Details')
                    ->tabs([
                        Tab::make('Overview')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                ImageEntry::make('thumbnail')
                                    ->label('Thumbnail')
                                    ->height(250)
                                    ->extraImgAttributes(['class' => 'rounded-lg']),
                                TextEntry::make('name')
                                    ->label('Court Name')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->color('gray')
                                    ->copyable(),
                                TextEntry::make('city.name')
                                    ->label('City')
                                    ->icon('heroicon-o-map-pin'),
                                TextEntry::make('category.name')
                                    ->label('Category')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Indoor' => 'info',
                                        'Outdoor' => 'success',
                                        default => 'gray',
                                    }),
                                TextEntry::make('material')
                                    ->label('Material')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Grass' => 'success',
                                        'Cement' => 'gray',
                                        default => 'gray',
                                    }),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'active' => 'success',
                                        'draft' => 'warning',
                                        default => 'gray',
                                    }),
                            ])
                            ->columns(2),

                        Tab::make('Pricing & Features')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                TextEntry::make('price_per_hour')
                                    ->label('Price per Hour')
                                    ->money('IDR')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),
                                TextEntry::make('bookings_count')
                                    ->label('Total Bookings')
                                    ->state(fn ($record) => $record->bookings()->count())
                                    ->icon('heroicon-o-calendar')
                                    ->color('info'),
                                TextEntry::make('features')
                                    ->label('Features')
                                    ->badge()
                                    ->separator(',')
                                    ->color('info')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('About')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextEntry::make('about')
                                    ->label('Description')
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Gallery')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                ImageEntry::make('photos')
                                    ->label('Court Photos')
                                    ->height(200)
                                    ->extraImgAttributes(['class' => 'rounded-lg'])
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Location')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                TextEntry::make('address')
                                    ->label('Full Address')
                                    ->icon('heroicon-o-map-pin')
                                    ->columnSpanFull(),
                                TextEntry::make('phone')
                                    ->label('Phone Number')
                                    ->icon('heroicon-o-phone')
                                    ->copyable(),
                                TextEntry::make('city.name')
                                    ->label('City')
                                    ->icon('heroicon-o-building-office-2'),
                            ])
                            ->columns(2),

                        Tab::make('Time Slots')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                RepeatableEntry::make('timeSlots')
                                    ->label('Operating Hours')
                                    ->schema([
                                        TextEntry::make('day_of_week')
                                            ->label('Day')
                                            ->formatStateUsing(fn (int $state) => CourtTimeSlot::getDayName($state)),
                                        TextEntry::make('start_time')
                                            ->label('Open')
                                            ->time('H:i'),
                                        TextEntry::make('end_time')
                                            ->label('Close')
                                            ->time('H:i'),
                                        TextEntry::make('is_active')
                                            ->label('Status')
                                            ->badge()
                                            ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')
                                            ->color(fn (bool $state) => $state ? 'success' : 'danger'),
                                    ])
                                    ->columns(4)
                                    ->columnSpanFull(),
                                TextEntry::make('hint')
                                    ->label('')
                                    ->state('Use "Setup Time Slots" button above or scroll down to manage time slots')
                                    ->color('gray')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Metadata')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                TextEntry::make('id')
                                    ->label('Court ID'),
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime(),
                                TextEntry::make('deleted_at')
                                    ->label('Deleted At')
                                    ->dateTime()
                                    ->placeholder('Not deleted'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
