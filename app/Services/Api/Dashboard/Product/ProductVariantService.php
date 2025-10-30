<?php

namespace App\Services\Api\Dashboard\Product;

use App\Models\ProductVariant;
use App\Models\AttributeValue;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = ProductVariant::with([
                'product.translations',
                'product.category.translations',
                'product.brand.translations',
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->integer('product_id'));
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('sku', 'like', "%{$search}%")
                      ->orWhereHas('product.translations', function ($subQ) use ($search) {
                          $subQ->where('name', 'like', "%{$search}%");
                      });
                });
            }

            if ($request->filled('active')) {
                $query->where('active', $request->boolean('active'));
            }

            if ($request->filled('stock_filter')) {
                $stockFilter = $request->get('stock_filter');
                switch ($stockFilter) {
                    case 'in_stock':
                        $query->where('stock', '>', 0);
                        break;
                    case 'out_of_stock':
                        $query->where('stock', '=', 0);
                        break;
                    case 'low_stock':
                        $query->whereRaw('stock <= minimum_stock');
                        break;
                }
            }

            $perPage = $request->integer('per_page', 15);
            $variants = $query->paginate($perPage);

            return $this->successResponse($variants, __('messages.product_variants_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        // dd($data);
        try {
            DB::beginTransaction();

            $variant = ProductVariant::create($data);

            // Assign attribute values if provided
            if (isset($data['attribute_value_ids']) && is_array($data['attribute_value_ids'])) {
                $variant->attributeValues()->attach($data['attribute_value_ids']);
            }

            $variant->load([
                'product.translations',
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            DB::commit();
            return $this->successResponse($variant, __('messages.product_variant_created'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(ProductVariant $productVariant)
    {
        try {
            $productVariant->load([
                'product.translations',
                'product.category.translations',
                'product.brand.translations',
                'attributeValues.attribute.translations',
                'attributeValues.translations',
                // 'inventories'
            ]);

            return $this->successResponse($productVariant, __('messages.product_variant_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function showWithAttributes(ProductVariant $productVariant)
    {
        try {
            $productVariant->load([
                'product.translations',
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            return $this->successResponse($productVariant, 'Product variant with attributes retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve product variant with attributes', ['error' => $e->getMessage()]);
        }
    }

    public function update(ProductVariant $productVariant, array $data)
    {
        try {
            DB::beginTransaction();

            $productVariant->update($data);

            // Update attribute values if provided
            if (isset($data['attribute_value_ids']) && is_array($data['attribute_value_ids'])) {
                $productVariant->attributeValues()->sync($data['attribute_value_ids']);
            }

            $productVariant->load([
                'product.translations',
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            DB::commit();
            return $this->successResponse($productVariant, __('messages.product_variant_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(ProductVariant $productVariant)
    {
        try {
            // Check if variant has orders
            if ($productVariant->orderItems()->count() > 0) {
                return $this->errorResponse('Cannot delete variant with existing orders', 422);
            }

            // Check if variant has cart items
            if ($productVariant->cartItems()->count() > 0) {
                return $this->errorResponse('Cannot delete variant with items in cart', 422);
            }

            $productVariant->delete();
            return $this->successResponse(null, __('messages.product_variant_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function assignAttributes(ProductVariant $productVariant, array $data)
    {
        try {
            if (!isset($data['attribute_value_ids']) || !is_array($data['attribute_value_ids'])) {
                return $this->errorResponse('Attribute value IDs are required', 422);
            }

            // Validate that all attribute values exist
            $attributeValueIds = $data['attribute_value_ids'];
            $existingAttributeValues = AttributeValue::whereIn('id', $attributeValueIds)->pluck('id')->toArray();
            
            if (count($existingAttributeValues) !== count($attributeValueIds)) {
                return $this->errorResponse('Some attribute values do not exist', 422);
            }

            // Check for conflicts (same attribute with multiple values)
            $attributeIds = AttributeValue::whereIn('id', $attributeValueIds)
                ->pluck('attribute_id')
                ->toArray();
            
            if (count($attributeIds) !== count(array_unique($attributeIds))) {
                return $this->errorResponse('Cannot assign multiple values for the same attribute', 422);
            }

            $productVariant->attributeValues()->sync($attributeValueIds);

            $productVariant->load([
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            return $this->successResponse($productVariant, 'Attributes assigned successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to assign attributes', ['error' => $e->getMessage()]);
        }
    }

    public function removeAttributes(ProductVariant $productVariant, array $data)
    {
        try {
            if (!isset($data['attribute_value_ids']) || !is_array($data['attribute_value_ids'])) {
                return $this->errorResponse('Attribute value IDs are required', 422);
            }

            $productVariant->attributeValues()->detach($data['attribute_value_ids']);

            $productVariant->load([
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            return $this->successResponse($productVariant, 'Attributes removed successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove attributes', ['error' => $e->getMessage()]);
        }
    }

    public function attachAttributeValue(ProductVariant $productVariant, int $attributeValueId)
    {
        try {
            // Replace any existing value for the same attribute
            $attribute = AttributeValue::select('attribute_id')->find($attributeValueId);
            if (!$attribute) {
                return $this->errorResponse('Attribute value not found', 404);
            }
            $attributeId = $attribute->attribute_id;

            // Detach existing values for this attribute
            $existingIds = $productVariant->attributeValues()
                ->where('attribute_values.attribute_id', $attributeId)
                ->pluck('attribute_values.id')
                ->toArray();
            if (!empty($existingIds)) {
                $productVariant->attributeValues()->detach($existingIds);
            }

            // Attach new value
            $productVariant->attributeValues()->attach($attributeValueId);

            $productVariant->load([
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            return $this->successResponse($productVariant, 'Attribute value attached successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to attach attribute value', ['error' => $e->getMessage()]);
        }
    }

    public function toggleActive(ProductVariant $productVariant)
    {
        try {
            $productVariant->update(['active' => !$productVariant->active]);
            
            $productVariant->load([
                'product.translations',
                'attributeValues.attribute.translations',
                'attributeValues.translations'
            ]);

            return $this->successResponse($productVariant, 'Product variant status toggled successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to toggle product variant status', ['error' => $e->getMessage()]);
        }
    }
}
