<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\CourtTimeSlot;
use Illuminate\Database\Seeder;

class CourtTimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        // Default operating hours for all courts
        // Most courts open 06:00-22:00 on weekdays, 07:00-21:00 on weekends
        $weekdaySchedule = [
            ['start_time' => '06:00', 'end_time' => '22:00'],
        ];

        $weekendSchedule = [
            ['start_time' => '07:00', 'end_time' => '21:00'],
        ];

        $courts = Court::all();

        foreach ($courts as $court) {
            // Monday to Friday (1-5)
            for ($day = 1; $day <= 5; $day++) {
                foreach ($weekdaySchedule as $schedule) {
                    CourtTimeSlot::create([
                        'court_id' => $court->id,
                        'day_of_week' => $day,
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'is_active' => true,
                    ]);
                }
            }

            // Saturday and Sunday (6, 0)
            foreach ([6, 0] as $day) {
                foreach ($weekendSchedule as $schedule) {
                    CourtTimeSlot::create([
                        'court_id' => $court->id,
                        'day_of_week' => $day,
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
