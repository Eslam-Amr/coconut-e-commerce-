<?php

namespace App\Services\Api\Dashboard\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;

class VariantAttributeService
{
    use ApiResponseTrait;

    

    /**
     * Create variants for all possible combinations
     */
    public function createVariantsForCombinations($productId, array $data)
    {
        try {
            DB::beginTransaction();

            $product = Product::find($productId);
            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            // Expect precomputed combinations in request data
            $combinations = $data['combinations'] ?? [];
            if (!is_array($combinations) || empty($combinations)) {
                return $this->errorResponse('Combinations array is required', 422);
            }

            $createdVariants = [];
            $baseSku = $data['base_sku'] ?? $product->id . '-';
            $basePrice = $data['base_price'] ?? $product->base_price;
            $stock = $data['stock'] ?? 0;
            $minimumStock = $data['minimum_stock'] ?? 0;

            foreach ($combinations as $index => $combination) {
                $sku = $baseSku . ($index + 1);
                $price = $basePrice + ($data['price_increment'] ?? 0) * $index;

                $variant = ProductVariant::create([
                    'product_id' => $productId,
                    'sku' => $sku,
                    'price' => $price,
                    'stock' => $stock,
                    'minimum_stock' => $minimumStock,
                    'active' => $data['active'] ?? true
                ]);

                // Assign attribute values
                $attributeValueIds = collect($combination)->pluck('attribute_value_id')->toArray();
                $variant->attributeValues()->attach($attributeValueIds);

                $variant->load([
                    'attributeValues.attribute.translations',
                    'attributeValues.translations'
                ]);

                $createdVariants[] = $variant;
            }

            DB::commit();
            return $this->successResponse($createdVariants, 'Variants created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Failed to create variants', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Validate variant attribute combinations
     */
    public function validateVariantCombinations($productId, array $combinations)
    {
        try {
            $product = Product::with('category')->find($productId);
            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $errors = [];
            $validCombinations = [];

            foreach ($combinations as $index => $combination) {
                $combinationErrors = [];

                // Check if all attribute values exist and belong to the product's category
                foreach ($combination['attribute_value_ids'] as $attributeValueId) {
                    $attributeValue = AttributeValue::with('attribute')->find($attributeValueId);
                    
                    if (!$attributeValue) {
                        $combinationErrors[] = "Attribute value ID {$attributeValueId} does not exist";
                        continue;
                    }

                    // if ($attributeValue->attribute->category_id !== $product->category_id) {
                    //     $combinationErrors[] = "Attribute value ID {$attributeValueId} does not belong to the product category";
                    // }
                }

                // Check for duplicate attributes
                $attributeIds = AttributeValue::whereIn('id', $combination['attribute_value_ids'])
                    ->pluck('attribute_id')
                    ->toArray();

                if (count($attributeIds) !== count(array_unique($attributeIds))) {
                    $combinationErrors[] = "Cannot assign multiple values for the same attribute";
                }

                if (!empty($combinationErrors)) {
                    $errors["combination_{$index}"] = $combinationErrors;
                } else {
                    $validCombinations[] = $combination;
                }
            }

            return $this->successResponse([
                'valid_combinations' => $validCombinations,
                'errors' => $errors,
                'is_valid' => empty($errors)
            ], 'Validation completed');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to validate combinations', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get variant summary for a product
     */
    public function getVariantSummary($productId)
    {
        try {
            $product = Product::with([
                'variants.attributeValues.attribute.translations',
                'variants.attributeValues.translations',
                'category.attributes.values.translations'
            ])->find($productId);

            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $summary = [
                'product_id' => $product->id,
                'total_variants' => $product->variants->count(),
                'active_variants' => $product->variants->where('active', true)->count(),
                'total_stock' => $product->variants->sum('stock'),
                'low_stock_variants' => $product->variants->filter(function ($variant) {
                    return $variant->stock <= $variant->minimum_stock;
                })->count(),
                'attributes_used' => $product->variants->flatMap(function ($variant) {
                    return $variant->attributeValues->map(function ($attributeValue) {
                        return [
                            'attribute_id' => $attributeValue->attribute->id,
                            'attribute_name' => $attributeValue->attribute->name,
                            'value_id' => $attributeValue->id,
                            'value' => $attributeValue->value
                        ];
                    });
                })->unique('attribute_id')->values(),
                'price_range' => [
                    'min' => $product->variants->min('price'),
                    'max' => $product->variants->max('price')
                ]
            ];

            return $this->successResponse($summary, 'Variant summary retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to get variant summary', ['error' => $e->getMessage()]);
        }
    }

    
}
