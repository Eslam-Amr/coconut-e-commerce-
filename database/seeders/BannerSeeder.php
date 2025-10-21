<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main banner for homepage
        Banner::create([
            'link' => '/products/electronics',
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(30),
            'active' => true,
            'ar' => [
                'title' => 'عروض الإلكترونيات المذهلة',
            ],
            'en' => [
                'title' => 'Amazing Electronics Offers',
            ]
        ]);

        // Summer sale banner
        Banner::create([
            'link' => '/products/summer-sale',
            'start_date' => now()->subDays(3),
            'end_date' => now()->addDays(45),
            'active' => true,
            'ar' => [
                'title' => 'تخفيضات الصيف الكبرى',
            ],
            'en' => [
                'title' => 'Summer Sale - Up to 50% Off',
            ]
        ]);

        // Fashion banner
        Banner::create([
            'link' => '/products/fashion',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(60),
            'active' => true,
            'ar' => [
                'title' => 'مجموعة الأزياء الجديدة',
            ],
            'en' => [
                'title' => 'New Fashion Collection',
            ]
        ]);

        // Sports banner
        Banner::create([
            'link' => '/products/sports',
            'start_date' => now(),
            'end_date' => now()->addDays(90),
            'active' => true,
            'ar' => [
                'title' => 'معدات الرياضة الاحترافية',
            ],
            'en' => [
                'title' => 'Professional Sports Equipment',
            ]
        ]);

        // Home & Garden banner
        Banner::create([
            'link' => '/products/home-garden',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(75),
            'active' => true,
            'ar' => [
                'title' => 'أدوات المنزل والحديقة',
            ],
            'en' => [
                'title' => 'Home & Garden Tools',
            ]
        ]);

        // Books banner
        Banner::create([
            'link' => '/products/books',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(120),
            'active' => true,
            'ar' => [
                'title' => 'مكتبة الكتب الرقمية',
            ],
            'en' => [
                'title' => 'Digital Book Library',
            ]
        ]);

        // Expired banner (for testing)
        Banner::create([
            'link' => '/products/old-sale',
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1),
            'active' => false,
            'ar' => [
                'title' => 'عرض منتهي الصلاحية',
            ],
            'en' => [
                'title' => 'Expired Offer',
            ]
        ]);

        // Future banner (not yet active)
        Banner::create([
            'link' => '/products/black-friday',
            'start_date' => now()->addDays(30),
            'end_date' => now()->addDays(45),
            'active' => true,
            'ar' => [
                'title' => 'عرض الجمعة السوداء القادمة',
            ],
            'en' => [
                'title' => 'Upcoming Black Friday Sale',
            ]
        ]);

        // Inactive banner
        Banner::create([
            'link' => '/products/inactive',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'active' => false,
            'ar' => [
                'title' => 'إعلان غير نشط',
            ],
            'en' => [
                'title' => 'Inactive Banner',
            ]
        ]);

        // Special offer banner
        Banner::create([
            'link' => '/products/special-offer',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
            'active' => true,
            'ar' => [
                'title' => 'عرض خاص محدود الوقت',
            ],
            'en' => [
                'title' => 'Limited Time Special Offer',
            ]
        ]);

        $this->command->info('Banner seeder completed successfully!');
        $this->command->info('Created ' . Banner::count() . ' banners');
    }
}
