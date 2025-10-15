<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::with('translations')->get();

        foreach ($products as $product) {
            $productName = $product->translate('en')->name ?? '';
            $basePrice = $product->base_price;

            // Smartphones - create variants with different storage and colors
            if (str_contains($productName, 'iPhone') || str_contains($productName, 'Galaxy')) {
                $variants = [
                    ['sku' => $product->id . '-128GB-BLACK', 'price' => $basePrice, 'stock' => 15, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-128GB-WHITE', 'price' => $basePrice, 'stock' => 12, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-256GB-BLACK', 'price' => $basePrice + 100, 'stock' => 10, 'minimum_stock' => 3],
                    ['sku' => $product->id . '-256GB-WHITE', 'price' => $basePrice + 100, 'stock' => 8, 'minimum_stock' => 3],
                    ['sku' => $product->id . '-512GB-BLACK', 'price' => $basePrice + 200, 'stock' => 5, 'minimum_stock' => 2],
                ];

                foreach ($variants as $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'minimum_stock' => $variantData['minimum_stock'],
                        'active' => true,
                    ]);
                }
            }

            // Laptops - create variants with different storage and colors
            elseif (str_contains($productName, 'MacBook') || str_contains($productName, 'VAIO')) {
                $variants = [
                    ['sku' => $product->id . '-256GB-SILVER', 'price' => $basePrice, 'stock' => 8, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-256GB-SPACE-GRAY', 'price' => $basePrice, 'stock' => 6, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-512GB-SILVER', 'price' => $basePrice + 200, 'stock' => 5, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-512GB-SPACE-GRAY', 'price' => $basePrice + 200, 'stock' => 4, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-1TB-SILVER', 'price' => $basePrice + 400, 'stock' => 2, 'minimum_stock' => 1],
                ];

                foreach ($variants as $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'minimum_stock' => $variantData['minimum_stock'],
                        'active' => true,
                    ]);
                }
            }

            // Clothing - create variants with different sizes and colors
            elseif (str_contains($productName, 'T-Shirt') || str_contains($productName, 'Sports Bra')) {
                $variants = [
                    ['sku' => $product->id . '-S-BLACK', 'price' => $basePrice, 'stock' => 25, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-S-WHITE', 'price' => $basePrice, 'stock' => 20, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-M-BLACK', 'price' => $basePrice, 'stock' => 30, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-M-WHITE', 'price' => $basePrice, 'stock' => 25, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-L-BLACK', 'price' => $basePrice, 'stock' => 20, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-L-WHITE', 'price' => $basePrice, 'stock' => 15, 'minimum_stock' => 5],
                    ['sku' => $product->id . '-XL-BLACK', 'price' => $basePrice, 'stock' => 10, 'minimum_stock' => 3],
                    ['sku' => $product->id . '-XL-WHITE', 'price' => $basePrice, 'stock' => 8, 'minimum_stock' => 3],
                ];

                foreach ($variants as $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'minimum_stock' => $variantData['minimum_stock'],
                        'active' => true,
                    ]);
                }
            }

            // Shoes - create variants with different sizes and colors
            elseif (str_contains($productName, 'Air Max') || str_contains($productName, 'Ultraboost')) {
                $variants = [
                    ['sku' => $product->id . '-7-BLACK', 'price' => $basePrice, 'stock' => 10, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-7-WHITE', 'price' => $basePrice, 'stock' => 8, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-8-BLACK', 'price' => $basePrice, 'stock' => 12, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-8-WHITE', 'price' => $basePrice, 'stock' => 10, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-9-BLACK', 'price' => $basePrice, 'stock' => 15, 'minimum_stock' => 3],
                    ['sku' => $product->id . '-9-WHITE', 'price' => $basePrice, 'stock' => 12, 'minimum_stock' => 3],
                    ['sku' => $product->id . '-10-BLACK', 'price' => $basePrice, 'stock' => 10, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-10-WHITE', 'price' => $basePrice, 'stock' => 8, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-11-BLACK', 'price' => $basePrice, 'stock' => 6, 'minimum_stock' => 2],
                    ['sku' => $product->id . '-11-WHITE', 'price' => $basePrice, 'stock' => 5, 'minimum_stock' => 2],
                ];

                foreach ($variants as $variantData) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'minimum_stock' => $variantData['minimum_stock'],
                        'active' => true,
                    ]);
                }
            }

            // For products without specific variants, create a default variant
            else {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $product->id . '-DEFAULT',
                    'price' => $basePrice,
                    'stock' => $product->total_quantity,
                    'minimum_stock' => 5,
                    'active' => true,
                ]);
            }
        }

        $this->command->info('ProductVariant seeder completed successfully!');
        $this->command->info('Created ' . ProductVariant::count() . ' product variants');
    }
}
