<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create manager user
        $manager = User::create([
            'name' => 'Admin Manager',
            'email' => 'manager@padbro.com',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'phone' => '081234567890',
        ]);
        $manager->assignRole('manager');

        // Create customer users
        $customers = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'gender' => 'male', 'phone' => '081234567001'],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'gender' => 'female', 'phone' => '081234567002'],
            ['name' => 'Michael Johnson', 'email' => 'michael@example.com', 'gender' => 'male', 'phone' => '081234567003'],
            ['name' => 'Emily Davis', 'email' => 'emily@example.com', 'gender' => 'female', 'phone' => '081234567004'],
            ['name' => 'David Wilson', 'email' => 'david@example.com', 'gender' => 'male', 'phone' => '081234567005'],
            ['name' => 'Sarah Brown', 'email' => 'sarah@example.com', 'gender' => 'female', 'phone' => '081234567006'],
            ['name' => 'James Taylor', 'email' => 'james@example.com', 'gender' => 'male', 'phone' => '081234567007'],
            ['name' => 'Jessica Anderson', 'email' => 'jessica@example.com', 'gender' => 'female', 'phone' => '081234567008'],
            ['name' => 'Robert Thomas', 'email' => 'robert@example.com', 'gender' => 'male', 'phone' => '081234567009'],
            ['name' => 'Amanda Jackson', 'email' => 'amanda@example.com', 'gender' => 'female', 'phone' => '081234567010'],
            ['name' => 'William White', 'email' => 'william@example.com', 'gender' => 'male', 'phone' => '081234567011'],
            ['name' => 'Ashley Harris', 'email' => 'ashley@example.com', 'gender' => 'female', 'phone' => '081234567012'],
            ['name' => 'Christopher Martin', 'email' => 'chris@example.com', 'gender' => 'male', 'phone' => '081234567013'],
            ['name' => 'Stephanie Garcia', 'email' => 'stephanie@example.com', 'gender' => 'female', 'phone' => '081234567014'],
            ['name' => 'Daniel Martinez', 'email' => 'daniel@example.com', 'gender' => 'male', 'phone' => '081234567015'],
            ['name' => 'Nicole Robinson', 'email' => 'nicole@example.com', 'gender' => 'female', 'phone' => '081234567016'],
            ['name' => 'Matthew Clark', 'email' => 'matthew@example.com', 'gender' => 'male', 'phone' => '081234567017'],
            ['name' => 'Jennifer Lewis', 'email' => 'jennifer@example.com', 'gender' => 'female', 'phone' => '081234567018'],
            ['name' => 'Andrew Lee', 'email' => 'andrew@example.com', 'gender' => 'male', 'phone' => '081234567019'],
            ['name' => 'Michelle Walker', 'email' => 'michelle@example.com', 'gender' => 'female', 'phone' => '081234567020'],
        ];

        foreach ($customers as $customerData) {
            $customer = User::create([
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'password' => Hash::make('password'),
                'gender' => $customerData['gender'],
                'phone' => $customerData['phone'],
            ]);
            $customer->assignRole('customer');
        }
    }
}
