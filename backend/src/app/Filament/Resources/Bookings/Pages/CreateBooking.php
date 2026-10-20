<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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
