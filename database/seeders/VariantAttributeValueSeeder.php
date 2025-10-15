<?php

namespace Database\Seeders;

use App\Models\ProductVariant;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VariantAttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get attribute values
        $colorValues = AttributeValue::whereHas('attribute.translations', function($query) {
            $query->where('name', 'Color');
        })->get();

        $sizeValues = AttributeValue::whereHas('attribute.translations', function($query) {
            $query->where('name', 'Size');
        })->get();

        $storageValues = AttributeValue::whereHas('attribute.translations', function($query) {
            $query->where('name', 'Storage');
        })->get();

        $materialValues = AttributeValue::whereHas('attribute.translations', function($query) {
            $query->where('name', 'Material');
        })->get();

        // Get all product variants
        $variants = ProductVariant::with('product.translations')->get();

        foreach ($variants as $variant) {
            $sku = $variant->sku;
            $productName = $variant->product->translate('en')->name ?? '';

            // Parse SKU to determine attributes
            $skuParts = explode('-', $sku);

            // Smartphones and Laptops - storage and color
            if (str_contains($productName, 'iPhone') || str_contains($productName, 'Galaxy') || 
                str_contains($productName, 'MacBook') || str_contains($productName, 'VAIO')) {
                
                // Find storage value
                if (count($skuParts) >= 2) {
                    $storageSku = $skuParts[1]; // e.g., "128GB", "256GB", "512GB", "1TB"
                    $storageValue = $storageValues->first(function($value) use ($storageSku) {
                        $valueName = $value->translate('en')->value ?? '';
                        return str_contains($valueName, $storageSku);
                    });

                    if ($storageValue) {
                        DB::table('variant_attribute_values')->insert([
                            'product_variant_id' => $variant->id,
                            'attribute_value_id' => $storageValue->id,
                            // 'created_at' => now(),
                            // 'updated_at' => now(),
                        ]);
                    }
                }

                // Find color value
                if (count($skuParts) >= 3) {
                    $colorSku = $skuParts[2]; // e.g., "BLACK", "WHITE", "SILVER", "SPACE-GRAY"
                    $colorValue = $colorValues->first(function($value) use ($colorSku) {
                        $valueName = $value->translate('en')->value ?? '';
                        return str_contains(strtoupper($valueName), $colorSku) || 
                               str_contains($colorSku, strtoupper($valueName));
                    });

                    if ($colorValue) {
                        DB::table('variant_attribute_values')->insert([
                            'product_variant_id' => $variant->id,
                            'attribute_value_id' => $colorValue->id,
                            // 'created_at' => now(),
                            // 'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Clothing - size and color
            elseif (str_contains($productName, 'T-Shirt') || str_contains($productName, 'Sports Bra')) {
                // Find size value
                if (count($skuParts) >= 2) {
                    $sizeSku = $skuParts[1]; // e.g., "S", "M", "L", "XL"
                    $sizeValue = $sizeValues->first(function($value) use ($sizeSku) {
                        $valueName = $value->translate('en')->value ?? '';
                        return strtoupper($valueName) === $sizeSku;
                    });

                    if ($sizeValue) {
                        DB::table('variant_attribute_values')->insert([
                            'product_variant_id' => $variant->id,
                            'attribute_value_id' => $sizeValue->id,
                            // 'created_at' => now(),
                            // 'updated_at' => now(),
                        ]);
                    }
                }

                // Find color value
                if (count($skuParts) >= 3) {
                    $colorSku = $skuParts[2]; // e.g., "BLACK", "WHITE"
                    $colorValue = $colorValues->first(function($value) use ($colorSku) {
                        $valueName = $value->translate('en')->value ?? '';
                        return str_contains(strtoupper($valueName), $colorSku) || 
                               str_contains($colorSku, strtoupper($valueName));
                    });

                    if ($colorValue) {
                        DB::table('variant_attribute_values')->insert([
                            'product_variant_id' => $variant->id,
                            'attribute_value_id' => $colorValue->id,
                            // 'created_at' => now(),
                            // 'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Shoes - size and color
            elseif (str_contains($productName, 'Air Max') || str_contains($productName, 'Ultraboost')) {
                // Find size value
                if (count($skuParts) >= 2) {
                    $sizeSku = $skuParts[1]; // e.g., "7", "8", "9", "10", "11"
                    $sizeValue = $sizeValues->first(function($value) use ($sizeSku) {
                        $valueName = $value->translate('en')->value ?? '';
                        return $valueName === $sizeSku;
                    });

                    if ($sizeValue) {
                        DB::table('variant_attribute_values')->insert([
                            'product_variant_id' => $variant->id,
                            'attribute_value_id' => $sizeValue->id,
                            // 'created_at' => now(),
                            // 'updated_at' => now(),
                        ]);
                    }
                }

                // Find color value
                if (count($skuParts) >= 3) {
                    $colorSku = $skuParts[2]; // e.g., "BLACK", "WHITE"
                    $colorValue = $colorValues->first(function($value) use ($colorSku) {
                        $valueName = $value->translate('en')->value ?? '';
                        return str_contains(strtoupper($valueName), $colorSku) || 
                               str_contains($colorSku, strtoupper($valueName));
                    });

                    if ($colorValue) {
                        DB::table('variant_attribute_values')->insert([
                            'product_variant_id' => $variant->id,
                            'attribute_value_id' => $colorValue->id,
                            // 'created_at' => now(),
                            // 'updated_at' => now(),
                        ]);
                    }
                }

                // Find material value (for shoes)
                if ($materialValues->isNotEmpty()) {
                    $materialValue = $materialValues->random();
                    DB::table('variant_attribute_values')->insert([
                        'product_variant_id' => $variant->id,
                        'attribute_value_id' => $materialValue->id,
                        // 'created_at' => now(),
                        // 'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('VariantAttributeValue seeder completed successfully!');
        $this->command->info('Created variant-attribute value relationships');
    }
}
