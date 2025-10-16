<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Address;
use App\Models\Country;
use App\Models\City;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Egypt country and Cairo city (assuming they exist)
        $egypt =  Country::whereHas('translations', function ($q) {
            $q->where('name', 'Saudi Arabia');
        })->first();
        $cairo = City::whereHas('translations', function ($q) {
            $q->where('name', 'Riyadh');
        })->first();

        if (!$egypt || !$cairo) {
            $this->command->error('Egypt country or Cairo city not found. Please run country and city seeders first.');
            return;
        }

        $addresses = [
            [
                'user_id' => 1,
                'country_id' => $egypt->id,
                'city_id' => $cairo->id,
                'name' => 'Home Address',
                'phone' => '+201234567890',
                'longitude' => 31.2357,
                'latitude' => 30.0444,
                'description' => 'Main residence address',
                'code' => 'HOME001',
            ],
            [
                'user_id' => 1,
                'country_id' => $egypt->id,
                'city_id' => $cairo->id,
                'name' => 'Office Address',
                'phone' => '+201234567891',
                'longitude' => 31.6789,
                'latitude' => 30.0123,
                'description' => 'Work office location',
                'code' => 'OFF001',
            ],
            [
                'user_id' => 1,
                'country_id' => $egypt->id,
                'city_id' => $cairo->id,
                'name' => 'Family House',
                'phone' => '+201234567892',
                'longitude' => 31.2194,
                'latitude' => 30.0626,
                'description' => 'Family residence in Zamalek',
                'code' => 'FAM001',
            ],
            [
                'user_id' => 1,
                'country_id' => $egypt->id,
                'city_id' => $cairo->id,
                'name' => 'Weekend Villa',
                'phone' => '+201234567893',
                'longitude' => 31.4567,
                'latitude' => 30.0789,
                'description' => 'Weekend getaway villa',
                'code' => 'VIL001',
            ],
            [
                'user_id' => 1,
                'country_id' => $egypt->id,
                'city_id' => $cairo->id,
                'name' => 'Emergency Contact',
                'phone' => '+201234567894',
                'longitude' => 31.3456,
                'latitude' => 30.1234,
                'description' => 'Emergency contact address',
                'code' => 'EMR001',
            ],
        ];

        foreach ($addresses as $addressData) {
            Address::create($addressData);
        }

        $this->command->info('Created 5 addresses for user ID 1');
    }
}