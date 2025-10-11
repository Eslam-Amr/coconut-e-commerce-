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
                'Attributes retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attributes', ['error' => $e->getMessage()]);
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
                'Attribute created successfully', 
                201
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create attribute', ['error' => $e->getMessage()]);
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
                'Attribute retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attribute', ['error' => $e->getMessage()]);
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
                'Attribute updated successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update attribute', ['error' => $e->getMessage()]);
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
                return $this->badRequestResponse('Cannot delete attribute with existing values');
            }

            $attribute->delete();

            return $this->successNotDataResponse('Attribute deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete attribute', ['error' => $e->getMessage()]);
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
                'Attributes with values retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attributes with values', ['error' => $e->getMessage()]);
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
            ], 'Attribute statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attribute statistics', ['error' => $e->getMessage()]);
        }
    }
}
