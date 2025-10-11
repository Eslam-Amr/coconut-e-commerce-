<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main categories (parent categories)
        $electronics = Category::create([
            'ar' => ['name' => 'إلكترونيات'],
            'en' => ['name' => 'Electronics'],
            'icon' => 'electronics-icon',
            'parent_id' => null,
            'active' => true,
        ]);

        $clothing = Category::create([
            'ar' => ['name' => 'ملابس'],
            'en' => ['name' => 'Clothing'],
            'icon' => 'clothing-icon',
            'parent_id' => null,
            'active' => true,
        ]);

        $home = Category::create([
            'ar' => ['name' => 'المنزل والحديقة'],
            'en' => ['name' => 'Home & Garden'],
            'icon' => 'home-icon',
            'parent_id' => null,
            'active' => true,
        ]);

        $sports = Category::create([
            'ar' => ['name' => 'رياضة'],
            'en' => ['name' => 'Sports'],
            'icon' => 'sports-icon',
            'parent_id' => null,
            'active' => true,
        ]);

        $books = Category::create([
            'ar' => ['name' => 'كتب'],
            'en' => ['name' => 'Books'],
            'icon' => 'books-icon',
            'parent_id' => null,
            'active' => true,
        ]);

        // Create subcategories for Electronics
        Category::create([
            'ar' => ['name' => 'هواتف ذكية'],
            'en' => ['name' => 'Smartphones'],
            'icon' => 'smartphone-icon',
            'parent_id' => $electronics->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'أجهزة كمبيوتر محمولة'],
            'en' => ['name' => 'Laptops'],
            'icon' => 'laptop-icon',
            'parent_id' => $electronics->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'أجهزة لوحية'],
            'en' => ['name' => 'Tablets'],
            'icon' => 'tablet-icon',
            'parent_id' => $electronics->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'سماعات وأجهزة صوتية'],
            'en' => ['name' => 'Audio & Headphones'],
            'icon' => 'headphones-icon',
            'parent_id' => $electronics->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'كاميرات'],
            'en' => ['name' => 'Cameras'],
            'icon' => 'camera-icon',
            'parent_id' => $electronics->id,
            'active' => true,
        ]);

        // Create subcategories for Clothing
        Category::create([
            'ar' => ['name' => 'ملابس رجالية'],
            'en' => ['name' => 'Men\'s Clothing'],
            'icon' => 'mens-clothing-icon',
            'parent_id' => $clothing->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'ملابس نسائية'],
            'en' => ['name' => 'Women\'s Clothing'],
            'icon' => 'womens-clothing-icon',
            'parent_id' => $clothing->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'ملابس أطفال'],
            'en' => ['name' => 'Kids\' Clothing'],
            'icon' => 'kids-clothing-icon',
            'parent_id' => $clothing->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'أحذية'],
            'en' => ['name' => 'Shoes'],
            'icon' => 'shoes-icon',
            'parent_id' => $clothing->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'إكسسوارات'],
            'en' => ['name' => 'Accessories'],
            'icon' => 'accessories-icon',
            'parent_id' => $clothing->id,
            'active' => true,
        ]);

        // Create subcategories for Home & Garden
        Category::create([
            'ar' => ['name' => 'أثاث'],
            'en' => ['name' => 'Furniture'],
            'icon' => 'furniture-icon',
            'parent_id' => $home->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'مطبخ وطعام'],
            'en' => ['name' => 'Kitchen & Dining'],
            'icon' => 'kitchen-icon',
            'parent_id' => $home->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'فراش وحمام'],
            'en' => ['name' => 'Bedding & Bath'],
            'icon' => 'bedding-icon',
            'parent_id' => $home->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'حديقة وخارجي'],
            'en' => ['name' => 'Garden & Outdoor'],
            'icon' => 'garden-icon',
            'parent_id' => $home->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'ديكور منزلي'],
            'en' => ['name' => 'Home Decor'],
            'icon' => 'decor-icon',
            'parent_id' => $home->id,
            'active' => true,
        ]);

        // Create subcategories for Sports
        Category::create([
            'ar' => ['name' => 'لياقة بدنية'],
            'en' => ['name' => 'Fitness & Exercise'],
            'icon' => 'fitness-icon',
            'parent_id' => $sports->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'رياضات خارجية'],
            'en' => ['name' => 'Outdoor Sports'],
            'icon' => 'outdoor-icon',
            'parent_id' => $sports->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'رياضات جماعية'],
            'en' => ['name' => 'Team Sports'],
            'icon' => 'team-sports-icon',
            'parent_id' => $sports->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'رياضات مائية'],
            'en' => ['name' => 'Water Sports'],
            'icon' => 'water-sports-icon',
            'parent_id' => $sports->id,
            'active' => true,
        ]);

        // Create subcategories for Books
        Category::create([
            'ar' => ['name' => 'خيال'],
            'en' => ['name' => 'Fiction'],
            'icon' => 'fiction-icon',
            'parent_id' => $books->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'غير خيال'],
            'en' => ['name' => 'Non-Fiction'],
            'icon' => 'non-fiction-icon',
            'parent_id' => $books->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'كتب أطفال'],
            'en' => ['name' => 'Children\'s Books'],
            'icon' => 'children-books-icon',
            'parent_id' => $books->id,
            'active' => true,
        ]);

        Category::create([
            'ar' => ['name' => 'تعليمية'],
            'en' => ['name' => 'Educational'],
            'icon' => 'educational-icon',
            'parent_id' => $books->id,
            'active' => true,
        ]);

        // Create some inactive categories for testing
        Category::create([
            'ar' => ['name' => 'إلكترونيات متوقفة'],
            'en' => ['name' => 'Discontinued Electronics'],
            'icon' => 'discontinued-icon',
            'parent_id' => $electronics->id,
            'active' => false,
        ]);

        Category::create([
            'ar' => ['name' => 'ملابس موسمية'],
            'en' => ['name' => 'Seasonal Clothing'],
            'icon' => 'seasonal-icon',
            'parent_id' => $clothing->id,
            'active' => false,
        ]);

        // Create some third-level categories (sub-subcategories)
        $smartphones = Category::whereHas('translations', function($query) {
            $query->where('name', 'Smartphones');
        })->first();

        if ($smartphones) {
            Category::create([
                'ar' => ['name' => 'آيفون'],
                'en' => ['name' => 'iPhone'],
                'icon' => 'iphone-icon',
                'parent_id' => $smartphones->id,
                'active' => true,
            ]);

            Category::create([
                'ar' => ['name' => 'هواتف أندرويد'],
                'en' => ['name' => 'Android Phones'],
                'icon' => 'android-icon',
                'parent_id' => $smartphones->id,
                'active' => true,
            ]);

            Category::create([
                'ar' => ['name' => 'هواتف تقليدية'],
                'en' => ['name' => 'Feature Phones'],
                'icon' => 'feature-phone-icon',
                'parent_id' => $smartphones->id,
                'active' => true,
            ]);
        }

        $mensClothing = Category::whereHas('translations', function($query) {
            $query->where('name', 'Men\'s Clothing');
        })->first();

        if ($mensClothing) {
            Category::create([
                'ar' => ['name' => 'قمصان'],
                'en' => ['name' => 'T-Shirts'],
                'icon' => 'tshirt-icon',
                'parent_id' => $mensClothing->id,
                'active' => true,
            ]);

            Category::create([
                'ar' => ['name' => 'جينز'],
                'en' => ['name' => 'Jeans'],
                'icon' => 'jeans-icon',
                'parent_id' => $mensClothing->id,
                'active' => true,
            ]);

            Category::create([
                'ar' => ['name' => 'قمصان'],
                'en' => ['name' => 'Shirts'],
                'icon' => 'shirt-icon',
                'parent_id' => $mensClothing->id,
                'active' => true,
            ]);
        }

        $this->command->info('Category seeder completed successfully!');
        $this->command->info('Created ' . Category::count() . ' categories');
    }
}