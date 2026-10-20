<?php

namespace App\Filament\Resources\Courts\RelationManagers;

use App\Models\CourtTimeSlot;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;

class TimeSlotsRelationManager extends RelationManager
{
    protected static string $relationship = 'timeSlots';

    protected static ?string $title = 'Time Slots';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('day_of_week')
                    ->label('Day of Week')
                    ->options(CourtTimeSlot::getDayOptions())
                    ->required()
                    ->native(false),
                Select::make('start_time')
                    ->label('Start Time')
                    ->options(self::getTimeOptions())
                    ->required()
                    ->native(false),
                Select::make('end_time')
                    ->label('End Time')
                    ->options(self::getTimeOptions())
                    ->required()
                    ->native(false),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Day')
                    ->formatStateUsing(fn (int $state) => CourtTimeSlot::getDayName($state))
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Start')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('End')
                    ->time('H:i'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('day_of_week')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getTimeOptions(): array
    {
        $options = [];
        for ($hour = 10; $hour <= 15; $hour++) {
            $time = sprintf('%02d:00:00', $hour);
            $label = sprintf('%02d:00', $hour);
            $options[$time] = $label;
        }
        return $options;
    }
}
