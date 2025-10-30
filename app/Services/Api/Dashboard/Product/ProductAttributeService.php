<?php

namespace App\Services\Api\Dashboard\Product;

use App\Models\ProductAttribute;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAttributeService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = ProductAttribute::with([
                'product.translations',
                'product.category.translations',
                'attribute.translations',
                'attributeValue.translations'
            ]);

            if ($request->filled('product_id')) {
                $query->where('product_id', $request->integer('product_id'));
            }

            if ($request->filled('attribute_id')) {
                $query->where('attribute_id', $request->integer('attribute_id'));
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product.translations', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('attribute.translations', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('attributeValue.translations', function ($subQ) use ($search) {
                        $subQ->where('value', 'like', "%{$search}%");
                    });
                });
            }

            $perPage = $request->integer('per_page', 15);
            $productAttributes = $query->paginate($perPage);

            return $this->successResponse($productAttributes, __('messages.product_attributes_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            // Check if this combination already exists
            $existing = ProductAttribute::where('product_id', $data['product_id'])
                ->where('attribute_id', $data['attribute_id'])
                ->where('attribute_value_id', $data['attribute_value_id'])
                ->first();

            if ($existing) {
                return $this->errorResponse(__('messages.attribute_value_already_assigned'), 422);
            }

            // Validate that the attribute belongs to the product's category
            $product = Product::with('category')->find($data['product_id']);
            if (!$product || !$product->category) {
                return $this->errorResponse(__('messages.product_or_category_not_found'), 404);
            }

            $attribute = Attribute::find($data['attribute_id']);
            if (!$attribute) {
                return $this->errorResponse(__('messages.attribute_not_belong_to_category'), 422);
            }

            // Validate that the attribute value belongs to the attribute
            $attributeValue = AttributeValue::find($data['attribute_value_id']);
            if (!$attributeValue || $attributeValue->attribute_id !== $attribute->id) {
                return $this->errorResponse(__('messages.attribute_value_not_belong_to_attribute'), 422);
            }

            $productAttribute = ProductAttribute::create($data);

            $productAttribute->load([
                'product.translations',
                'attribute.translations',
                'attributeValue.translations'
            ]);

            DB::commit();
            return $this->successResponse($productAttribute, __('messages.product_attribute_created'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(ProductAttribute $productAttribute)
    {
        try {
            $productAttribute->load([
                'product.translations',
                'product.category.translations',
                'attribute.translations',
                'attributeValue.translations'
            ]);

            return $this->successResponse($productAttribute, __('messages.product_attribute_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(ProductAttribute $productAttribute, array $data)
    {
        try {
            DB::beginTransaction();

            // Check if this combination already exists (excluding current record)
            $existing = ProductAttribute::where('product_id', $data['product_id'])
                ->where('attribute_id', $data['attribute_id'])
                ->where('attribute_value_id', $data['attribute_value_id'])
                ->where('id', '!=', $productAttribute->id)
                ->first();

            if ($existing) {
                return $this->errorResponse(__('messages.attribute_value_already_assigned'), 422);
            }

            // Validate that the attribute belongs to the product's category
            $product = Product::with('category')->find($data['product_id']);
            if (!$product || !$product->category) {
                return $this->errorResponse(__('messages.product_or_category_not_found'), 404);
            }

            $attribute = Attribute::find($data['attribute_id']);
            if (!$attribute) {
                return $this->errorResponse(__('messages.attribute_not_belong_to_category'), 422);
            }

            // Validate that the attribute value belongs to the attribute
            $attributeValue = AttributeValue::find($data['attribute_value_id']);
            if (!$attributeValue || $attributeValue->attribute_id !== $attribute->id) {
                return $this->errorResponse(__('messages.attribute_value_not_belong_to_attribute'), 422);
            }

            $productAttribute->update($data);

            $productAttribute->load([
                'product.translations',
                'attribute.translations',
                'attributeValue.translations'
            ]);

            DB::commit();
            return $this->successResponse($productAttribute, __('messages.product_attribute_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(ProductAttribute $productAttribute)
    {
        try {
            $productAttribute->delete();
            return $this->successResponse(null, __('messages.product_attribute_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function getByProduct($productId, array $filters = [])
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return $this->errorResponse(__('messages.not_found'), 404);
            }

            $query = ProductAttribute::with([
                'attribute.translations',
                'attributeValue.translations'
            ])->where('product_id', $productId);

            if (isset($filters['attribute_id'])) {
                $query->where('attribute_id', $filters['attribute_id']);
            }

            $productAttributes = $query->get();

            return $this->successResponse($productAttributes, __('messages.product_attributes_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function bulkAssign($productId, array $data)
    {
        try {
            DB::beginTransaction();

            $product = Product::with('category')->find($productId);
            if (!$product || !$product->category) {
                return $this->errorResponse(__('messages.product_or_category_not_found'), 404);
            }

            if (!isset($data['attributes']) || !is_array($data['attributes'])) {
                return $this->errorResponse(__('messages.attributes_array_required'), 422);
            }

            $attributes = $data['attributes'];
            $productAttributeIds = [];

            foreach ($attributes as $attributeData) {
                if (!isset($attributeData['attribute_id']) || !isset($attributeData['attribute_value_id'])) {
                    return $this->errorResponse(__('messages.attribute_must_have_ids'), 422);
                }

                $attributeId = $attributeData['attribute_id'];
                $attributeValueId = $attributeData['attribute_value_id'];

                // Validate that the attribute belongs to the product's category
                $attribute = Attribute::find($attributeId);
                if (!$attribute ) {
                    return $this->errorResponse("Attribute ID {$attributeId} does not belong to the product category", 422);
                }

                // Validate that the attribute value belongs to the attribute
                $attributeValue = AttributeValue::find($attributeValueId);
                if (!$attributeValue || $attributeValue->attribute_id !== $attributeId) {
                    return $this->errorResponse("Attribute value ID {$attributeValueId} does not belong to attribute ID {$attributeId}", 422);
                }

                // Check if this combination already exists
                $existing = ProductAttribute::where('product_id', $productId)
                    ->where('attribute_id', $attributeId)
                    ->where('attribute_value_id', $attributeValueId)
                    ->first();

                if (!$existing) {
                    $productAttribute = ProductAttribute::create([
                        'product_id' => $productId,
                        'attribute_id' => $attributeId,
                        'attribute_value_id' => $attributeValueId
                    ]);
                    $productAttributeIds[] = $productAttribute->id;
                }
            }

            // Load the created product attributes with relationships
            $productAttributes = ProductAttribute::with([
                'attribute.translations',
                'attributeValue.translations'
            ])->whereIn('id', $productAttributeIds)->get();

            DB::commit();
            return $this->successResponse($productAttributes, __('messages.product_attributes_assigned_successfully'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function removeAll($productId)
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return $this->errorResponse(__('messages.not_found'), 404);
            }

            $deletedCount = ProductAttribute::where('product_id', $productId)->delete();

            return $this->successResponse(['deleted_count' => $deletedCount], __('messages.all_product_attributes_removed'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function getAvailableAttributes($productId)
    {
        try {
            $product = Product::with('category')->find($productId);
            if (!$product || !$product->category) {
                return $this->errorResponse(__('messages.product_or_category_not_found'), 404);
            }

            // Get all attributes for the product's category
            $attributes = Attribute::with([
                'translations',
                'values.translations'
            ])->get();

            // Get already assigned attributes for this product
            $assignedAttributeIds = ProductAttribute::where('product_id', $productId)
                ->pluck('attribute_id')
                ->toArray();

            // Filter out already assigned attributes
            $availableAttributes = $attributes->reject(function ($attribute) use ($assignedAttributeIds) {
                return in_array($attribute->id, $assignedAttributeIds);
            });

            return $this->successResponse($availableAttributes, __('messages.available_attributes_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }
}
