<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Convert datetime fields to separate date and time for the form
        if (!empty($data['start_at'])) {
            $startAt = Carbon::parse($data['start_at']);
            $data['booking_date'] = $startAt->toDateString();
            $data['start_time'] = $startAt->format('H:i');
        }

        if (!empty($data['end_at'])) {
            $endAt = Carbon::parse($data['end_at']);
            $data['end_time'] = $endAt->format('H:i');
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Combine booking_date + start_time/end_time into start_at/end_at datetime
        $bookingDate = $data['booking_date'];
        $startTime = $data['start_time'];
        $endTime = $data['end_time'];

        $data['start_at'] = Carbon::parse($bookingDate . ' ' . $startTime . ':00');
        $data['end_at'] = Carbon::parse($bookingDate . ' ' . $endTime . ':00');

        // Remove temporary fields that are not in the database
        unset($data['booking_date']);
        unset($data['start_time']);
        unset($data['end_time']);

        return $data;
    }
}
