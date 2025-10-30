<?php

namespace App\Services\Api\Dashboard\Attribute;

use App\Http\Resources\AttributeResource;
use App\Models\Attribute;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class AttributeService
{
    use ApiResponseTrait;

    /**
     * Get paginated list of attributes with filters
     */
    public function index(Request $request)
    {
        try {
            $query = Attribute::with([
                'translations',
                'values.translations'
            ]);

            // Search by name in translations
            if ($request->has('search') && $request->search) {
                $query->whereHas('translations', function ($q) use ($request) {
                    $q->whereRaw('UPPER(name) LIKE ?', ['%' . strtoupper($request->search) . '%']);
                });
            }

            $attributes = $query->paginate($request->get('per_page', 15));

            return $this->successResponsePaginated(
                AttributeResource::collection($attributes), 
                __('messages.attributes_retrieved_successfully')
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new attribute
     */
    public function store(array $data)
    {
        try {
            $attribute = Attribute::create($data);
            $attribute->load(['translations']);

            return $this->successResponse(
                new AttributeResource($attribute), 
                __('messages.attribute_created'), 
                201
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific attribute
     */
    public function show(Attribute $attribute)
    {
        try {
            $attribute->load(['values.translations']);

            return $this->successResponse(
                new AttributeResource($attribute), 
                __('messages.attribute_retrieved_successfully')
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update an attribute
     */
    public function update(Attribute $attribute, array $data)
    {
        try {
            $attribute->update($data);
            $attribute->load(['translations', 'values.translations']);

            return $this->successResponse(
                new AttributeResource($attribute), 
                __('messages.attribute_updated_successfully')
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete an attribute
     */
    public function destroy(Attribute $attribute)
    {
        try {
            // Check if attribute has values
            if ($attribute->values()->count() > 0) {
                return $this->badRequestResponse(__('messages.cannot_delete_attribute_with_values'));
            }

            $attribute->delete();

            return $this->successNotDataResponse(__('messages.attribute_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if attribute can be deleted
     */
    public function canDelete(Attribute $attribute): bool
    {
        return $attribute->values()->count() === 0;
    }

    /**
     * Get attributes with their values
     */
    public function getWithValues(Request $request = null)
    {
        try {
            $query = Attribute::with(['translations', 'values.translations']);

            if ($request && $request->has('search') && $request->search) {
                $query->whereHas('translations', function ($q) use ($request) {
                    $q->whereRaw('UPPER(name) LIKE ?', ['%' . strtoupper($request->search) . '%']);
                });
            }

            $attributes = $query->get();

            return $this->successResponse(
                AttributeResource::collection($attributes),
                __('messages.attributes_with_values_retrieved_successfully')
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get attribute statistics
     */
    public function getStatistics()
    {
        try {
            $totalAttributes = Attribute::count();
            $attributesWithValues = Attribute::whereHas('values')->count();
            $attributesWithoutValues = $totalAttributes - $attributesWithValues;

            return $this->successResponse([
                'total_attributes' => $totalAttributes,
                'attributes_with_values' => $attributesWithValues,
                'attributes_without_values' => $attributesWithoutValues,
            ], __('messages.attribute_statistics_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }
}
