<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Main homepage slider
        Slider::create([
            'link' => '/products/featured',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(60),
            'active' => true,
            'ar' => [
                'title' => 'اكتشف أحدث المنتجات',
            ],
            'en' => [
                'title' => 'Discover Latest Products',
            ]
        ]);

        // Electronics slider
        Slider::create([
            'link' => '/products/electronics',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(45),
            'active' => true,
            'ar' => [
                'title' => 'تقنيات المستقبل في متناول يدك',
            ],
            'en' => [
                'title' => 'Future Technology at Your Fingertips',
            ]
        ]);

        // Fashion slider
        Slider::create([
            'link' => '/products/fashion',
            'start_date' => now()->subDays(3),
            'end_date' => now()->addDays(90),
            'active' => true,
            'ar' => [
                'title' => 'أزياء أنيقة لعصر عصري',
            ],
            'en' => [
                'title' => 'Elegant Fashion for Modern Era',
            ]
        ]);

        // Sports slider
        Slider::create([
            'link' => '/products/sports',
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(120),
            'active' => true,
            'ar' => [
                'title' => 'احترف رياضتك المفضلة',
            ],
            'en' => [
                'title' => 'Master Your Favorite Sport',
            ]
        ]);

        // Home improvement slider
        Slider::create([
            'link' => '/products/home-garden',
            'start_date' => now(),
            'end_date' => now()->addDays(100),
            'active' => true,
            'ar' => [
                'title' => 'اجعل منزلك أكثر جمالاً',
            ],
            'en' => [
                'title' => 'Make Your Home More Beautiful',
            ]
        ]);

        // Books slider
        Slider::create([
            'link' => '/products/books',
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(150),
            'active' => true,
            'ar' => [
                'title' => 'مكتبة شاملة للمعرفة',
            ],
            'en' => [
                'title' => 'Comprehensive Knowledge Library',
            ]
        ]);

        // Beauty & Health slider
        Slider::create([
            'link' => '/products/beauty-health',
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(80),
            'active' => true,
            'ar' => [
                'title' => 'عناية شاملة بجمالك وصحتك',
            ],
            'en' => [
                'title' => 'Complete Care for Your Beauty & Health',
            ]
        ]);

        // Kids & Toys slider
        Slider::create([
            'link' => '/products/kids-toys',
            'start_date' => now()->subDays(4),
            'end_date' => now()->addDays(110),
            'active' => true,
            'ar' => [
                'title' => 'ألعاب تعليمية وممتعة للأطفال',
            ],
            'en' => [
                'title' => 'Educational & Fun Toys for Kids',
            ]
        ]);

        // Automotive slider
        Slider::create([
            'link' => '/products/automotive',
            'start_date' => now()->subDays(6),
            'end_date' => now()->addDays(70),
            'active' => true,
            'ar' => [
                'title' => 'قطع غيار وإكسسوارات السيارات',
            ],
            'en' => [
                'title' => 'Car Parts & Accessories',
            ]
        ]);

        // Expired slider (for testing)
        Slider::create([
            'link' => '/products/old-campaign',
            'start_date' => now()->subDays(40),
            'end_date' => now()->subDays(5),
            'active' => false,
            'ar' => [
                'title' => 'حملة منتهية الصلاحية',
            ],
            'en' => [
                'title' => 'Expired Campaign',
            ]
        ]);

        // Future slider (not yet active)
        Slider::create([
            'link' => '/products/new-year-sale',
            'start_date' => now()->addDays(15),
            'end_date' => now()->addDays(60),
            'active' => true,
            'ar' => [
                'title' => 'عرض رأس السنة الجديدة',
            ],
            'en' => [
                'title' => 'New Year Sale Coming Soon',
            ]
        ]);

        // Inactive slider
        Slider::create([
            'link' => '/products/inactive-slider',
            'start_date' => now()->subDays(8),
            'end_date' => now()->addDays(30),
            'active' => false,
            'ar' => [
                'title' => 'سلايدر غير نشط',
            ],
            'en' => [
                'title' => 'Inactive Slider',
            ]
        ]);

        $this->command->info('Slider seeder completed successfully!');
        $this->command->info('Created ' . Slider::count() . ' sliders');
    }
}
