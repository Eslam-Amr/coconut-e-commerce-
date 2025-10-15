<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\ProductAttribute;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get attributes
        $colorAttribute = Attribute::whereHas('translations', function($query) {
            $query->where('name', 'Color');
        })->first();

        $sizeAttribute = Attribute::whereHas('translations', function($query) {
            $query->where('name', 'Size');
        })->first();

        $storageAttribute = Attribute::whereHas('translations', function($query) {
            $query->where('name', 'Storage');
        })->first();

        $materialAttribute = Attribute::whereHas('translations', function($query) {
            $query->where('name', 'Material');
        })->first();

        // Get attribute values
        $colorValues = AttributeValue::where('attribute_id', $colorAttribute->id)->get();
        $sizeValues = AttributeValue::where('attribute_id', $sizeAttribute->id)->get();
        $storageValues = AttributeValue::where('attribute_id', $storageAttribute->id)->get();
        $materialValues = AttributeValue::where('attribute_id', $materialAttribute->id)->get();

        // Get products
        $products = Product::with('translations')->get();

        foreach ($products as $product) {
            $productName = $product->translate('en')->name ?? '';

            // Smartphones - assign storage and color attributes
            if (str_contains($productName, 'iPhone') || str_contains($productName, 'Galaxy')) {
                // Assign storage attribute
                if ($storageValues->isNotEmpty()) {
                    $randomStorage = $storageValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $storageAttribute->id,
                        'attribute_value_id' => $randomStorage->id,
                    ]);
                }

                // Assign color attribute
                if ($colorValues->isNotEmpty()) {
                    $randomColor = $colorValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $colorAttribute->id,
                        'attribute_value_id' => $randomColor->id,
                    ]);
                }
            }

            // Laptops - assign storage and color attributes
            if (str_contains($productName, 'MacBook') || str_contains($productName, 'VAIO')) {
                // Assign storage attribute
                if ($storageValues->isNotEmpty()) {
                    $randomStorage = $storageValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $storageAttribute->id,
                        'attribute_value_id' => $randomStorage->id,
                    ]);
                }

                // Assign color attribute
                if ($colorValues->isNotEmpty()) {
                    $randomColor = $colorValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $colorAttribute->id,
                        'attribute_value_id' => $randomColor->id,
                    ]);
                }
            }

            // Clothing - assign size and color attributes
            if (str_contains($productName, 'T-Shirt') || str_contains($productName, 'Sports Bra')) {
                // Assign size attribute
                if ($sizeValues->isNotEmpty()) {
                    $randomSize = $sizeValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $sizeAttribute->id,
                        'attribute_value_id' => $randomSize->id,
                    ]);
                }

                // Assign color attribute
                if ($colorValues->isNotEmpty()) {
                    $randomColor = $colorValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $colorAttribute->id,
                        'attribute_value_id' => $randomColor->id,
                    ]);
                }
            }

            // Shoes - assign size and color attributes
            if (str_contains($productName, 'Air Max') || str_contains($productName, 'Ultraboost')) {
                // Assign size attribute
                if ($sizeValues->isNotEmpty()) {
                    $randomSize = $sizeValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $sizeAttribute->id,
                        'attribute_value_id' => $randomSize->id,
                    ]);
                }

                // Assign color attribute
                if ($colorValues->isNotEmpty()) {
                    $randomColor = $colorValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $colorAttribute->id,
                        'attribute_value_id' => $randomColor->id,
                    ]);
                }

                // Assign material attribute
                if ($materialValues->isNotEmpty()) {
                    $randomMaterial = $materialValues->random();
                    ProductAttribute::create([
                        'product_id' => $product->id,
                        'attribute_id' => $materialAttribute->id,
                        'attribute_value_id' => $randomMaterial->id,
                    ]);
                }
            }
        }

        $this->command->info('ProductAttribute seeder completed successfully!');
        $this->command->info('Created ' . ProductAttribute::count() . ' product attributes');
    }
}
