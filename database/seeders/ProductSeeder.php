<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some categories and brands for products
        $smartphones = Category::whereHas('translations', function($query) {
            $query->where('name', 'Smartphones');
        })->first();

        $laptops = Category::whereHas('translations', function($query) {
            $query->where('name', 'Laptops');
        })->first();

        $mensClothing = Category::whereHas('translations', function($query) {
            $query->where('name', 'Men\'s Clothing');
        })->first();

        $womensClothing = Category::whereHas('translations', function($query) {
            $query->where('name', 'Women\'s Clothing');
        })->first();

        $shoes = Category::whereHas('translations', function($query) {
            $query->where('name', 'Shoes');
        })->first();

        $apple = Brand::whereHas('translations', function($query) {
            $query->where('name', 'Apple');
        })->first();

        $samsung = Brand::whereHas('translations', function($query) {
            $query->where('name', 'Samsung');
        })->first();

        $nike = Brand::whereHas('translations', function($query) {
            $query->where('name', 'Nike');
        })->first();

        $adidas = Brand::whereHas('translations', function($query) {
            $query->where('name', 'Adidas');
        })->first();

        $sony = Brand::whereHas('translations', function($query) {
            $query->where('name', 'Sony');
        })->first();

        // Electronics Products
        $products = [
            // Smartphones
            [
                'category_id' => $smartphones->id,
                'brand_id' => $apple->id,
                'base_price' => 999.99,
                'total_quantity' => 50,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'iPhone 15 Pro', 'description' => 'Latest iPhone with advanced camera system and A17 Pro chip'],
                    ['locale' => 'ar', 'name' => 'آيفون 15 برو', 'description' => 'أحدث آيفون مع نظام كاميرا متقدم ومعالج A17 Pro'],
                ]
            ],
            [
                'category_id' => $smartphones->id,
                'brand_id' => $samsung->id,
                'base_price' => 899.99,
                'total_quantity' => 75,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Samsung Galaxy S24', 'description' => 'Premium Android smartphone with AI-powered features'],
                    ['locale' => 'ar', 'name' => 'سامسونج جالاكسي S24', 'description' => 'هاتف أندرويد متميز مع ميزات مدعومة بالذكاء الاصطناعي'],
                ]
            ],
            [
                'category_id' => $smartphones->id,
                'brand_id' => $samsung->id,
                'base_price' => 699.99,
                'total_quantity' => 100,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Samsung Galaxy A55', 'description' => 'Mid-range smartphone with excellent camera and performance'],
                    ['locale' => 'ar', 'name' => 'سامسونج جالاكسي A55', 'description' => 'هاتف ذكي متوسط المدى مع كاميرا وأداء ممتاز'],
                ]
            ],

            // Laptops
            [
                'category_id' => $laptops->id,
                'brand_id' => $apple->id,
                'base_price' => 1299.99,
                'total_quantity' => 25,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'MacBook Air M3', 'description' => 'Ultra-thin laptop with M3 chip and all-day battery life'],
                    ['locale' => 'ar', 'name' => 'ماك بوك إير M3', 'description' => 'لابتوب فائق النحافة مع معالج M3 وعمر بطارية طويل'],
                ]
            ],
            [
                'category_id' => $laptops->id,
                'brand_id' => $sony->id,
                'base_price' => 1099.99,
                'total_quantity' => 30,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Sony VAIO Pro', 'description' => 'High-performance laptop for professionals and creators'],
                    ['locale' => 'ar', 'name' => 'سوني VAIO برو', 'description' => 'لابتوب عالي الأداء للمحترفين والمبدعين'],
                ]
            ],

            // Clothing
            [
                'category_id' => $mensClothing->id,
                'brand_id' => $nike->id,
                'base_price' => 29.99,
                'total_quantity' => 200,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Nike Dri-FIT T-Shirt', 'description' => 'Comfortable athletic t-shirt with moisture-wicking technology'],
                    ['locale' => 'ar', 'name' => 'قميص نايك Dri-FIT', 'description' => 'قميص رياضي مريح مع تقنية امتصاص الرطوبة'],
                ]
            ],
            [
                'category_id' => $womensClothing->id,
                'brand_id' => $nike->id,
                'base_price' => 39.99,
                'total_quantity' => 150,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Nike Women\'s Sports Bra', 'description' => 'High-support sports bra for active women'],
                    ['locale' => 'ar', 'name' => 'حمالة صدر رياضية نسائية نايك', 'description' => 'حمالة صدر رياضية عالية الدعم للنساء النشطات'],
                ]
            ],

            // Shoes
            [
                'category_id' => $shoes->id,
                'brand_id' => $nike->id,
                'base_price' => 129.99,
                'total_quantity' => 80,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Nike Air Max 270', 'description' => 'Comfortable running shoes with Max Air cushioning'],
                    ['locale' => 'ar', 'name' => 'نايك إير ماكس 270', 'description' => 'حذاء جري مريح مع وسائد هوائية ماكس'],
                ]
            ],
            [
                'category_id' => $shoes->id,
                'brand_id' => $adidas->id,
                'base_price' => 89.99,
                'total_quantity' => 120,
                'active' => true,
                'translations' => [
                    ['locale' => 'en', 'name' => 'Adidas Ultraboost 22', 'description' => 'Premium running shoes with Boost technology'],
                    ['locale' => 'ar', 'name' => 'أديداس ألترابوست 22', 'description' => 'حذاء جري متميز مع تقنية بوست'],
                ]
            ],

            // Some inactive products for testing
            [
                'category_id' => $smartphones->id,
                'brand_id' => $apple->id,
                'base_price' => 799.99,
                'total_quantity' => 0,
                'active' => false,
                'translations' => [
                    ['locale' => 'en', 'name' => 'iPhone 13 (Discontinued)', 'description' => 'Previous generation iPhone model'],
                    ['locale' => 'ar', 'name' => 'آيفون 13 (متوقف)', 'description' => 'طراز آيفون من الجيل السابق'],
                ]
            ],
        ];

        foreach ($products as $productData) {
            $translations = $productData['translations'];
            unset($productData['translations']);

            $product = Product::create($productData);

            // Create translations
            foreach ($translations as $translation) {
                DB::table('product_translations')->insert([
                    'product_id' => $product->id,
                    'locale' => $translation['locale'],
                    'name' => $translation['name'],
                    'description' => $translation['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Product seeder completed successfully!');
        $this->command->info('Created ' . Product::count() . ' products');
    }
}
