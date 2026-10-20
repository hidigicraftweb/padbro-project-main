<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('User Details')
                    ->tabs([
                        Tab::make('Profile')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Basic Information')
                                    ->columns(2)
                                    ->schema([
                                        ImageEntry::make('photo')
                                            ->label('Photo')
                                            ->circular()
                                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                                            ->columnSpanFull(),
                                        TextEntry::make('name')
                                            ->label('Full Name')
                                            ->icon('heroicon-o-user'),
                                        TextEntry::make('email')
                                            ->label('Email Address')
                                            ->icon('heroicon-o-envelope'),
                                        TextEntry::make('phone')
                                            ->label('Phone Number')
                                            ->icon('heroicon-o-phone')
                                            ->default('-'),
                                        TextEntry::make('gender')
                                            ->label('Gender')
                                            ->badge()
                                            ->color(fn (?string $state): string => match ($state) {
                                                'male' => 'info',
                                                'female' => 'danger',
                                                default => 'gray',
                                            }),
                                    ]),
                            ]),

                        Tab::make('Account')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Account Status')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('email_verified_at')
                                            ->label('Email Verified')
                                            ->dateTime()
                                            ->placeholder('Not verified')
                                            ->icon('heroicon-o-check-badge'),
                                        TextEntry::make('roles.name')
                                            ->label('Role')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'manager' => 'success',
                                                'customer' => 'info',
                                                default => 'gray',
                                            }),
                                    ]),
                            ]),

                        Tab::make('Metadata')
                            ->icon('heroicon-o-clock')
                            ->schema([
                                Section::make('Timestamps')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime()
                                            ->icon('heroicon-o-calendar'),
                                        TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime()
                                            ->icon('heroicon-o-arrow-path'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
