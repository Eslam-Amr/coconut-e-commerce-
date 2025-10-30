<?php

namespace App\Services\Api\Dashboard\Attribute\AttributeValue;

use App\Http\Resources\AttributeValueResource;
use App\Models\AttributeValue;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AttributeValueService
{
    use ApiResponseTrait;

    /**
     * Get paginated list of attribute values with filters
     */
    public function index(Request $request)
    {
        try {
            $query = AttributeValue::with(['attribute.translations', 'translations']);

            // Search by value or label
            if ($request->has('search') && $request->search) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('translations', function ($q) use ($request) {
                        $q->whereRaw('UPPER(value) LIKE ?', ['%' . strtoupper($request->search) . '%']);
                    });
                });
            }

            // Filter by attribute
            if ($request->has('attribute_id') && $request->attribute_id) {
                $query->where('attribute_id', $request->attribute_id);
            }

            $attributeValues = $query->paginate($request->get('per_page', 15));

            return $this->successResponsePaginated(
                $attributeValues, 
                __('messages.attribute_values_retrieved_successfully'),
                200,
                AttributeValueResource::class
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new attribute value
     */
    public function store(array $data)
    {
        try {
            $attributeValue = AttributeValue::create($data);
            $attributeValue->load(['attribute']);

            return $this->successResponse($attributeValue, __('messages.attribute_value_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific attribute value
     */
    public function show(AttributeValue $attributeValue)
    {
        try {
            $attributeValue->load(['attribute', 'translations', 'attribute.translations']);

            return $this->successResponse(new AttributeValueResource($attributeValue), __('messages.attribute_value_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update an attribute value
     */
    public function update(AttributeValue $attributeValue, array $data)
    {
        try {
            $attributeValue->update($data);
            $attributeValue->load(['attribute.translations', 'translations']);

            return $this->successResponse($attributeValue, __('messages.attribute_value_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete an attribute value
     */
    public function destroy(AttributeValue $attributeValue)
    {
        try {
            // Check if attribute value is being used by product variants
            if ($attributeValue->productVariants()->count() > 0) {
                return $this->badRequestResponse(__('messages.cannot_delete_attribute_value_used_by_variants'));
            }

            $attributeValue->delete();

            return $this->successNotDataResponse(__('messages.attribute_value_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if attribute value can be deleted
     */
    public function canDelete(AttributeValue $attributeValue): bool
    {
        return $attributeValue->productVariants()->count() === 0;
    }

    /**
     * Get attribute values by attribute ID
     */
    public function getByAttributeId(int $attributeId, Request $request = null)
    {
        try {
            $query = AttributeValue::with(['translations'])
                ->where('attribute_id', $attributeId);

            if ($request && $request->has('search') && $request->search) {
                $query->whereHas('translations', function ($q) use ($request) {
                    $q->whereRaw('UPPER(value) LIKE ?', ['%' . strtoupper($request->search) . '%']);
                });
            }

            $attributeValues = $query->get();

            return $this->successResponse(
                AttributeValueResource::collection($attributeValues),
                __('messages.attribute_values_retrieved_successfully')
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }
}
