<?php

namespace Database\Seeders;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Courts: 1-14 (2 per city for 7 cities)
        $bookings = [
            // Paid bookings
            ['court_id' => 1, 'user_id' => 2, 'date' => Carbon::now()->addDays(1), 'start_hour' => 8, 'end_hour' => 10, 'status' => 'paid'],
            ['court_id' => 2, 'user_id' => 3, 'date' => Carbon::now()->addDays(1), 'start_hour' => 10, 'end_hour' => 12, 'status' => 'paid'],
            ['court_id' => 3, 'user_id' => 4, 'date' => Carbon::now()->addDays(2), 'start_hour' => 14, 'end_hour' => 16, 'status' => 'paid'],
            ['court_id' => 4, 'user_id' => 5, 'date' => Carbon::now()->addDays(2), 'start_hour' => 16, 'end_hour' => 18, 'status' => 'paid'],
            ['court_id' => 5, 'user_id' => 6, 'date' => Carbon::now()->addDays(3), 'start_hour' => 9, 'end_hour' => 11, 'status' => 'paid'],
            ['court_id' => 6, 'user_id' => 7, 'date' => Carbon::now()->addDays(3), 'start_hour' => 11, 'end_hour' => 13, 'status' => 'paid'],
            ['court_id' => 7, 'user_id' => 8, 'date' => Carbon::now()->addDays(4), 'start_hour' => 15, 'end_hour' => 17, 'status' => 'paid'],
            ['court_id' => 8, 'user_id' => 9, 'date' => Carbon::now()->addDays(4), 'start_hour' => 17, 'end_hour' => 19, 'status' => 'paid'],

            // Pending bookings
            ['court_id' => 9, 'user_id' => 10, 'date' => Carbon::now()->addDays(5), 'start_hour' => 8, 'end_hour' => 10, 'status' => 'pending'],
            ['court_id' => 10, 'user_id' => 11, 'date' => Carbon::now()->addDays(5), 'start_hour' => 10, 'end_hour' => 12, 'status' => 'pending'],
            ['court_id' => 11, 'user_id' => 12, 'date' => Carbon::now()->addDays(6), 'start_hour' => 14, 'end_hour' => 16, 'status' => 'pending'],
            ['court_id' => 12, 'user_id' => 13, 'date' => Carbon::now()->addDays(6), 'start_hour' => 16, 'end_hour' => 18, 'status' => 'pending'],
            ['court_id' => 13, 'user_id' => 14, 'date' => Carbon::now()->addDays(7), 'start_hour' => 9, 'end_hour' => 12, 'status' => 'pending'],
            ['court_id' => 14, 'user_id' => 15, 'date' => Carbon::now()->addDays(7), 'start_hour' => 13, 'end_hour' => 15, 'status' => 'pending'],

            // Failed bookings (past dates)
            ['court_id' => 1, 'user_id' => 16, 'date' => Carbon::now()->subDays(3), 'start_hour' => 10, 'end_hour' => 12, 'status' => 'failed'],
            ['court_id' => 3, 'user_id' => 17, 'date' => Carbon::now()->subDays(2), 'start_hour' => 14, 'end_hour' => 16, 'status' => 'failed'],
            ['court_id' => 5, 'user_id' => 18, 'date' => Carbon::now()->subDays(1), 'start_hour' => 8, 'end_hour' => 10, 'status' => 'failed'],

            // More paid bookings
            ['court_id' => 7, 'user_id' => 19, 'date' => Carbon::now()->addDays(8), 'start_hour' => 10, 'end_hour' => 13, 'status' => 'paid'],
            ['court_id' => 9, 'user_id' => 20, 'date' => Carbon::now()->addDays(8), 'start_hour' => 15, 'end_hour' => 18, 'status' => 'paid'],
            ['court_id' => 11, 'user_id' => 21, 'date' => Carbon::now()->addDays(9), 'start_hour' => 9, 'end_hour' => 11, 'status' => 'paid'],
        ];

        // Get court prices for calculation (IDR per hour) - matching CourtSeeder
        $courtPrices = [
            1 => 150000, 2 => 180000,   // Jakarta
            3 => 130000, 4 => 120000,   // Bandung
            5 => 100000, 6 => 110000,   // Medan
            7 => 115000, 8 => 125000,   // Bogor
            9 => 95000, 10 => 85000,    // Solo
            11 => 120000, 12 => 140000, // Surabaya
            13 => 140000, 14 => 160000, // Tangerang
        ];

        foreach ($bookings as $booking) {
            $pricePerHour = $courtPrices[$booking['court_id']];
            $totalHours = $booking['end_hour'] - $booking['start_hour'];
            $subTotal = $pricePerHour * $totalHours;
            $taxAmount = (int) round($subTotal * 0.11); // 11% tax
            $grandTotal = $subTotal + $taxAmount;

            // Build full datetime from date + hour
            $startAt = $booking['date']->copy()->setTime($booking['start_hour'], 0, 0);
            $endAt = $booking['date']->copy()->setTime($booking['end_hour'], 0, 0);

            Booking::create([
                'court_id' => $booking['court_id'],
                'user_id' => $booking['user_id'],
                'start_at' => $startAt,
                'end_at' => $endAt,
                'total_hours' => $totalHours,
                'price_per_hour_amount' => $pricePerHour,
                'sub_total_amount' => $subTotal,
                'total_tax_amount' => $taxAmount,
                'grand_total_amount' => $grandTotal,
                'status' => $booking['status'],
            ]);
        }
    }
}
