<?php

namespace Database\Seeders;

use App\Models\Court;
use Illuminate\Database\Seeder;

class CourtSeeder extends Seeder
{
    public function run(): void
    {
        // Cities: 1=Jakarta, 2=Bandung, 3=Medan, 4=Bogor, 5=Solo, 6=Surabaya, 7=Tangerang
        $courts = [
            // Jakarta courts
            [
                'name' => 'Padel Arena Jakarta',
                'city_id' => 1,
                'category_id' => 1,
                'material' => 'Grass',
                'price_per_hour' => 150000,
                'about' => 'Premium indoor padel court with air conditioning and professional lighting.',
                'features' => ['Air Conditioning', 'Shower', 'Locker Room', 'Parking Lot', 'Canteen'],
                'address' => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone' => '021-1234567',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Jakarta Padel Club',
                'city_id' => 1,
                'category_id' => 2,
                'material' => 'Cement',
                'price_per_hour' => 180000,
                'about' => 'Elite padel club with professional coaching and tournaments.',
                'features' => ['Air Conditioning', 'Pro Shop', 'Lighting', 'Seating Area', 'Canteen'],
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'phone' => '021-2345678',
                'status' => 'active',
                'is_featured' => true,
            ],
            // Bandung courts
            [
                'name' => 'Bandung Padel Center',
                'city_id' => 2,
                'category_id' => 1,
                'material' => 'Grass',
                'price_per_hour' => 130000,
                'about' => 'Modern indoor facility with international standard court.',
                'features' => ['Air Conditioning', 'Shower', 'Locker Room', 'Pro Shop'],
                'address' => 'Jl. Dago No. 78, Bandung',
                'phone' => '022-3456789',
                'status' => 'active',
                'is_featured' => true,
            ],
            [
                'name' => 'Dago Padel Arena',
                'city_id' => 2,
                'category_id' => 2,
                'material' => 'Cement',
                'price_per_hour' => 120000,
                'about' => 'Beautiful outdoor padel court with mountain view.',
                'features' => ['Parking Lot', 'Canteen', 'Seating Area'],
                'address' => 'Jl. Dago Atas No. 56, Bandung',
                'phone' => '022-4567890',
                'status' => 'active',
            ],
            // Medan courts
            [
                'name' => 'Medan Padel Sports',
                'city_id' => 3,
                'category_id' => 2,
                'material' => 'Cement',
                'price_per_hour' => 100000,
                'about' => 'Affordable outdoor padel court for all skill levels.',
                'features' => ['Parking Lot', 'Gear Rental', 'Lighting'],
                'address' => 'Jl. Gatot Subroto No. 90, Medan',
                'phone' => '061-4567890',
                'status' => 'active',
            ],
            [
                'name' => 'Medan Padel Club',
                'city_id' => 3,
                'category_id' => 1,
                'material' => 'Grass',
                'price_per_hour' => 110000,
                'about' => 'Premium indoor facility with full amenities.',
                'features' => ['Air Conditioning', 'Shower', 'Locker Room', 'Canteen'],
                'address' => 'Jl. Imam Bonjol No. 45, Medan',
                'phone' => '061-5678901',
                'status' => 'active',
            ],
            // Bogor courts
            [
                'name' => 'Bogor Padel Garden',
                'city_id' => 4,
                'category_id' => 2,
                'material' => 'Grass',
                'price_per_hour' => 115000,
                'about' => 'Green outdoor court surrounded by beautiful gardens.',
                'features' => ['Parking Lot', 'Canteen', 'Seating Area'],
                'address' => 'Jl. Pajajaran No. 56, Bogor',
                'phone' => '0251-4567890',
                'status' => 'active',
            ],
            [
                'name' => 'Bogor Padel Arena',
                'city_id' => 4,
                'category_id' => 1,
                'material' => 'Cement',
                'price_per_hour' => 125000,
                'about' => 'Modern indoor padel facility near botanical garden.',
                'features' => ['Air Conditioning', 'Parking Lot', 'Shower', 'Pro Shop'],
                'address' => 'Jl. Raya Bogor No. 89, Bogor',
                'phone' => '0251-5678901',
                'status' => 'active',
            ],
            // Solo courts
            [
                'name' => 'Solo Padel Club',
                'city_id' => 5,
                'category_id' => 1,
                'material' => 'Grass',
                'price_per_hour' => 95000,
                'about' => 'Family-friendly indoor padel facility in the heart of Solo.',
                'features' => ['Air Conditioning', 'Parking Lot', 'Canteen', 'Free WiFi'],
                'address' => 'Jl. Slamet Riyadi No. 123, Solo',
                'phone' => '0271-1234567',
                'status' => 'active',
            ],
            [
                'name' => 'Solo Padel Center',
                'city_id' => 5,
                'category_id' => 2,
                'material' => 'Cement',
                'price_per_hour' => 85000,
                'about' => 'Budget-friendly outdoor court for recreational play.',
                'features' => ['Parking Lot', 'Gear Rental', 'Lighting'],
                'address' => 'Jl. Adi Sucipto No. 45, Solo',
                'phone' => '0271-2345678',
                'status' => 'active',
            ],
            // Surabaya courts
            [
                'name' => 'Surabaya Padel Club',
                'city_id' => 6,
                'category_id' => 2,
                'material' => 'Cement',
                'price_per_hour' => 120000,
                'about' => 'Beautiful outdoor padel court with scenic garden view.',
                'features' => ['Parking Lot', 'Canteen', 'Seating Area'],
                'address' => 'Jl. Pemuda No. 45, Surabaya',
                'phone' => '031-2345678',
                'status' => 'active',
            ],
            [
                'name' => 'Surabaya Padel Arena',
                'city_id' => 6,
                'category_id' => 1,
                'material' => 'Grass',
                'price_per_hour' => 140000,
                'about' => 'Premium indoor court with professional lighting.',
                'features' => ['Air Conditioning', 'Shower', 'Locker Room', 'Pro Shop'],
                'address' => 'Jl. Basuki Rahmat No. 67, Surabaya',
                'phone' => '031-3456789',
                'status' => 'active',
                'is_featured' => true,
            ],
            // Tangerang courts
            [
                'name' => 'Tangerang Padel Park',
                'city_id' => 7,
                'category_id' => 2,
                'material' => 'Grass',
                'price_per_hour' => 140000,
                'about' => 'Modern outdoor complex with multiple courts.',
                'features' => ['Parking Lot', 'Canteen', 'Lighting', 'Seating Area'],
                'address' => 'Jl. Raya Serpong No. 89, Tangerang',
                'phone' => '021-8901234',
                'status' => 'active',
            ],
            [
                'name' => 'BSD Padel Club',
                'city_id' => 7,
                'category_id' => 1,
                'material' => 'Cement',
                'price_per_hour' => 160000,
                'about' => 'Premium indoor facility in BSD City.',
                'features' => ['Air Conditioning', 'Shower', 'Locker Room', 'Pro Shop', 'Canteen'],
                'address' => 'Jl. BSD Raya No. 123, Tangerang',
                'phone' => '021-9012345',
                'status' => 'active',
                'is_featured' => true,
            ],
        ];

        foreach ($courts as $court) {
            Court::create([
                'name' => $court['name'],
                'city_id' => $court['city_id'],
                'category_id' => $court['category_id'],
                'material' => $court['material'],
                'price_per_hour' => $court['price_per_hour'],
                'about' => $court['about'],
                'features' => $court['features'],
                'address' => $court['address'],
                'phone' => $court['phone'],
                'status' => $court['status'],
                'is_featured' => $court['is_featured'] ?? false,
            ]);
        }
    }
}
