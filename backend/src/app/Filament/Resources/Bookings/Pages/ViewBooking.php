<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Approve Booking')
                ->modalDescription('Are you sure you want to approve this booking? The status will be changed to paid.')
                ->modalSubmitActionLabel('Yes, Approve')
                ->action(function () {
                    $this->record->update(['status' => 'paid']);

                    Notification::make()
                        ->title('Booking Approved')
                        ->success()
                        ->body('Booking #' . $this->record->id . ' has been approved successfully.')
                        ->send();
                }),
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Reject Booking')
                ->modalDescription('Are you sure you want to reject this booking? The status will be changed to failed.')
                ->modalSubmitActionLabel('Yes, Reject')
                ->action(function () {
                    $this->record->update(['status' => 'failed']);

                    Notification::make()
                        ->title('Booking Rejected')
                        ->danger()
                        ->body('Booking #' . $this->record->id . ' has been rejected.')
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
