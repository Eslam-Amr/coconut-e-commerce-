<?php

namespace Database\Seeders;

use App\Models\FlashSale;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FlashSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some products and categories to use for flash sales
        $products = Product::take(5)->get();
        $categories = Category::take(2)->get();

        // Create flash sales for products
        foreach ($products as $index => $product) {
            $startDate = now()->addDays($index);
            $endDate = $startDate->copy()->addDays(7);
            
            $flashSale = FlashSale::create([
                'discount' => rand(10, 50), // Random discount between 10-50%
                'max_limit' => rand(50, 200),
                'count' => 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'active' => true,
                'flashable_id' => $product->id,
                'flashable_type' => Product::class,
            ]);

            // Create translations
            $flashSale->translations()->create([
                'locale' => 'en',
                'title' => "Flash Sale - {$product->name}",
            ]);

            $flashSale->translations()->create([
                'locale' => 'ar',
                'title' => "عرض خاطف - {$product->name}",
            ]);
        }

        // Create flash sales for categories
        foreach ($categories as $index => $category) {
            $startDate = now()->addDays($index + 2);
            $endDate = $startDate->copy()->addDays(5);
            
            $flashSale = FlashSale::create([
                'discount' => rand(15, 40),
                'max_limit' => rand(100, 300),
                'count' => 0,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'active' => true,
                'flashable_id' => $category->id,
                'flashable_type' => Category::class,
            ]);

            // Create translations
            $flashSale->translations()->create([
                'locale' => 'en',
                'title' => "Category Flash Sale - {$category->name}",
            ]);

            $flashSale->translations()->create([
                'locale' => 'ar',
                'title' => "عرض خاطف للفئة - {$category->name}",
            ]);
        }

        // Create some expired flash sales for testing
        $expiredFlashSale = FlashSale::create([
            'discount' => 30,
            'max_limit' => 100,
            'count' => 45,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(2),
            'active' => false,
            'flashable_id' => $products->first()->id,
            'flashable_type' => Product::class,
        ]);

        // Create translations for expired flash sale
        $expiredFlashSale->translations()->create([
            'locale' => 'en',
            'title' => 'Expired Flash Sale - Electronics',
        ]);

        $expiredFlashSale->translations()->create([
            'locale' => 'ar',
            'title' => 'عرض خاطف منتهي - الإلكترونيات',
        ]);

        // Create some future flash sales
        $futureFlashSale = FlashSale::create([
            'discount' => 25,
            'max_limit' => 150,
            'count' => 0,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(12),
            'active' => true,
            'flashable_id' => $categories->first()->id,
            'flashable_type' => Category::class,
        ]);

        // Create translations for future flash sale
        $futureFlashSale->translations()->create([
            'locale' => 'en',
            'title' => 'Upcoming Flash Sale - Fashion',
        ]);

        $futureFlashSale->translations()->create([
            'locale' => 'ar',
            'title' => 'عرض خاطف قادم - الأزياء',
        ]);

    }
}
