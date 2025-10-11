<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $attributes = [
            [
                'id' => 1,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Color'],
                    ['locale' => 'ar', 'name' => 'اللون'],
                ],
            ],
            [
                'id' => 2,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Size'],
                    ['locale' => 'ar', 'name' => 'الحجم'],
                ],
            ],
            [
                'id' => 3,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Numeric Size'],
                    ['locale' => 'ar', 'name' => 'المقاس الرقمي'],
                ],
            ],
            [
                'id' => 4,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Storage'],
                    ['locale' => 'ar', 'name' => 'التخزين'],
                ],
            ],
            [
                'id' => 5,
                'translations' => [
                    ['locale' => 'en', 'name' => 'RAM'],
                    ['locale' => 'ar', 'name' => 'الذاكرة العشوائية'],
                ],
            ],
            [
                'id' => 6,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Material'],
                    ['locale' => 'ar', 'name' => 'المادة'],
                ],
            ],
            [
                'id' => 7,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Weight'],
                    ['locale' => 'ar', 'name' => 'الوزن'],
                ],
            ],
            [
                'id' => 8,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Length'],
                    ['locale' => 'ar', 'name' => 'الطول'],
                ],
            ],
            [
                'id' => 9,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Shoe Size (US)'],
                    ['locale' => 'ar', 'name' => 'مقاس الحذاء (أمريكي)'],
                ],
            ],
            [
                'id' => 10,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Screen Size'],
                    ['locale' => 'ar', 'name' => 'حجم الشاشة'],
                ],
            ],
        ];

        foreach ($attributes as $attribute) {
            // Insert attribute
            DB::table('attributes')->insert([
                'id' => $attribute['id'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert translations
            foreach ($attribute['translations'] as $translation) {
                DB::table('attribute_translations')->insert([
                    'attribute_id' => $attribute['id'],
                    'locale' => $translation['locale'],
                    'name' => $translation['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->command->info('Attributes seeded successfully!');
    }
}