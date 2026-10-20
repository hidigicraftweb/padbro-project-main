<?php

namespace App\Filament\Resources\Courts\Schemas;

use App\Models\Category;
use App\Models\City;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class CourtForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Basic Information')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),
                            Select::make('city_id')
                                ->label('City')
                                ->options(City::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Select::make('category_id')
                                ->label('Category')
                                ->options(Category::pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                            Select::make('material')
                                ->options([
                                    'Grass' => 'Grass',
                                    'Cement' => 'Cement',
                                ])
                                ->required(),
                            Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'active' => 'Active',
                                ])
                                ->default('draft')
                                ->required(),
                            Radio::make('is_featured')
                                ->label('Featured Court')
                                ->options([
                                    true => 'Featured',
                                    false => 'Not Featured',
                                ])
                                ->default(false)
                                ->inline()
                                ->inlineLabel(false)
                                ->required(),
                        ])
                        ->columns(2),

                    Step::make('Pricing & Details')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            TextInput::make('price_per_hour')
                                ->label('Price per Hour (IDR)')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->minValue(0),
                            CheckboxList::make('features')
                                ->options([
                                    'Parking Lot' => 'Parking Lot',
                                    'Canteen' => 'Canteen',
                                    'Locker Room' => 'Locker Room',
                                    'Shower' => 'Shower',
                                    'Free WiFi' => 'Free WiFi',
                                    'Gear Rental' => 'Gear Rental',
                                    'Air Conditioning' => 'Air Conditioning',
                                    'Pro Shop' => 'Pro Shop',
                                    'Lighting' => 'Lighting',
                                    'Seating Area' => 'Seating Area',
                                ])
                                ->columns(2)
                                ->columnSpanFull(),
                            Textarea::make('about')
                                ->label('About')
                                ->rows(4)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Step::make('Media')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            FileUpload::make('thumbnail')
                                ->label('Thumbnail Image')
                                ->image()
                                ->directory('courts/thumbnails')
                                ->imageEditor()
                                ->maxSize(2048),
                            FileUpload::make('photos')
                                ->label('Gallery Photos')
                                ->image()
                                ->multiple()
                                ->reorderable()
                                ->directory('courts/photos')
                                ->imageEditor()
                                ->maxSize(2048)
                                ->maxFiles(10),
                        ])
                        ->columns(2),

                    Step::make('Location & Contact')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Textarea::make('address')
                                ->label('Full Address')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull(),
                            TextInput::make('phone')
                                ->label('Phone Number')
                                ->tel()
                                ->maxLength(20),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
            ]);
    }
}
