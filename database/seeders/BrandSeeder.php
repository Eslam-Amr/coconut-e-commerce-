<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'logo' => 'brands/apple-logo.png',
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Apple'],
                    ['locale' => 'ar', 'name' => 'أبل'],
                ]
            ],
            [
                'logo' => 'brands/samsung-logo.png',
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Samsung'],
                    ['locale' => 'ar', 'name' => 'سامسونج'],
                ]
            ],
            [
                'logo' => 'brands/nike-logo.png',
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Nike'],
                    ['locale' => 'ar', 'name' => 'نايك'],
                ]
            ],
            [
                'logo' => 'brands/adidas-logo.png',
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Adidas'],
                    ['locale' => 'ar', 'name' => 'أديداس'],
                ]
            ],
            [
                'logo' => 'brands/sony-logo.png',
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Sony'],
                    ['locale' => 'ar', 'name' => 'سوني'],
                ]
            ],
        ];

        foreach ($brands as $brandData) {
            $brand = DB::table('brands')->insertGetId([
                'logo' => $brandData['logo'],
                'active' => $brandData['active'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            foreach ($brandData['translations'] as $translation) {
                DB::table('brand_translations')->insert([
                    'brand_id' => $brand,
                    'locale' => $translation['locale'],
                    'name' => $translation['name'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}