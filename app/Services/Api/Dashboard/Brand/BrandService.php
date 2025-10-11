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

            return $this->successResponse($brands, 'Brands retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve brands', ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $brand = Brand::create($data);
            $brand->load(['translations', 'media']);
            return $this->successResponse($brand, 'Brand created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create brand', ['error' => $e->getMessage()]);
        }
    }

    public function show(Brand $brand)
    {
        try {
            $brand->load(['translations', 'media']);
            return $this->successResponse($brand, 'Brand retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve brand', ['error' => $e->getMessage()]);
        }
    }

    public function update(Brand $brand, array $data)
    {
        try {
            $brand->update($data);
            $brand->load(['translations', 'media']);
            return $this->successResponse($brand, 'Brand updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update brand', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Brand $brand)
    {
        try {
            if ($brand->products()->count() > 0) {
                return $this->errorResponse('Cannot delete brand with products', 422);
            }

            $brand->delete();
            return $this->successResponse(null, 'Brand deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete brand', ['error' => $e->getMessage()]);
        }
    }
}


