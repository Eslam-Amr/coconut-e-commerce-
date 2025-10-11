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
                'Attribute values retrieved successfully',
                200,
                AttributeValueResource::class
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attribute values', ['error' => $e->getMessage()]);
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

            return $this->successResponse($attributeValue, 'Attribute value created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create attribute value', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific attribute value
     */
    public function show(AttributeValue $attributeValue)
    {
        try {
            $attributeValue->load(['attribute', 'translations', 'attribute.translations']);

            return $this->successResponse(new AttributeValueResource($attributeValue), 'Attribute value retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attribute value', ['error' => $e->getMessage()]);
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

            return $this->successResponse($attributeValue, 'Attribute value updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update attribute value', ['error' => $e->getMessage()]);
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
                return $this->badRequestResponse('Cannot delete attribute value that is being used by product variants');
            }

            $attributeValue->delete();

            return $this->successNotDataResponse('Attribute value deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete attribute value', ['error' => $e->getMessage()]);
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
                'Attribute values retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attribute values', ['error' => $e->getMessage()]);
        }
    }
}
