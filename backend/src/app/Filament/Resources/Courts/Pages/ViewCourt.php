<?php

namespace App\Filament\Resources\Courts\Pages;

use App\Filament\Resources\Courts\CourtResource;
use App\Models\CourtTimeSlot;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCourt extends ViewRecord
{
    protected static string $resource = CourtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('setupTimeSlots')
                ->label('Setup Time Slots')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->modalHeading('Setup Operating Hours')
                ->modalDescription('Configure the operating hours for each day of the week.')
                ->modalSubmitActionLabel('Save Time Slots')
                ->form([
                    Repeater::make('time_slots')
                        ->label('Weekly Schedule')
                        ->schema([
                            Select::make('day_of_week')
                                ->label('Day')
                                ->options(CourtTimeSlot::getDayOptions())
                                ->required()
                                ->native(false)
                                ->columnSpan(2),
                            Select::make('start_time')
                                ->label('Open')
                                ->options(self::getTimeOptions())
                                ->required()
                                ->native(false),
                            Select::make('end_time')
                                ->label('Close')
                                ->options(self::getTimeOptions())
                                ->required()
                                ->native(false),
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                        ])
                        ->columns(5)
                        ->defaultItems(0)
                        ->addActionLabel('Add Day')
                        ->reorderable(false)
                        ->default(function () {
                            // Pre-fill with existing time slots
                            $slots = $this->record->timeSlots()->get();
                            if ($slots->isEmpty()) {
                                // Default template: Mon-Sun 10:00-15:00
                                return collect(range(0, 6))->map(fn ($day) => [
                                    'day_of_week' => $day,
                                    'start_time' => '10:00:00',
                                    'end_time' => '15:00:00',
                                    'is_active' => true,
                                ])->toArray();
                            }
                            return $slots->map(fn ($slot) => [
                                'day_of_week' => $slot->day_of_week,
                                'start_time' => $slot->start_time,
                                'end_time' => $slot->end_time,
                                'is_active' => $slot->is_active,
                            ])->toArray();
                        }),
                ])
                ->action(function (array $data) {
                    // Delete existing time slots for this court
                    $this->record->timeSlots()->forceDelete();

                    // Create new time slots
                    foreach ($data['time_slots'] as $slot) {
                        $this->record->timeSlots()->create([
                            'day_of_week' => $slot['day_of_week'],
                            'start_time' => $slot['start_time'],
                            'end_time' => $slot['end_time'],
                            'is_active' => $slot['is_active'],
                        ]);
                    }

                    Notification::make()
                        ->title('Time Slots Updated')
                        ->success()
                        ->body('Operating hours have been saved successfully.')
                        ->send();

                    // Redirect to refresh the page
                    return redirect(CourtResource::getUrl('view', ['record' => $this->record]));
                }),
            EditAction::make(),
        ];
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
