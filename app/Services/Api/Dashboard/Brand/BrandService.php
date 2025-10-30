<?php

namespace App\Services\Api\Dashboard\Brand;

use App\Models\Brand;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class BrandService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Brand::with(['translations', 'media']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('active')) {
                $query->where('active', $request->boolean('active'));
            }

            $perPage = $request->integer('per_page', 15);
            $brands = $query->paginate($perPage);

            return $this->successResponse($brands, __('messages.brands_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $brand = Brand::create($data);
            $brand->load(['translations', 'media']);
            return $this->successResponse($brand, __('messages.brand_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(Brand $brand)
    {
        try {
            $brand->load(['translations', 'media']);
            return $this->successResponse($brand, __('messages.brand_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(Brand $brand, array $data)
    {
        try {
            $brand->update($data);
            $brand->load(['translations', 'media']);
            return $this->successResponse($brand, __('messages.brand_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Brand $brand)
    {
        try {
            if ($brand->products()->count() > 0) {
                return $this->errorResponse(__('messages.cannot_delete_brand_with_products'), 422);
            }

            $brand->delete();
            return $this->successResponse(null, __('messages.brand_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }
}


