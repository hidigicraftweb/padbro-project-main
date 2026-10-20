<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Jakarta',
            'Bandung',
            'Medan',
            'Bogor',
            'Solo',
            'Surabaya',
            'Tangerang',
        ];

        foreach ($cities as $city) {
            City::firstOrCreate([
                'name' => $city,
            ]);
        }
    }
}
