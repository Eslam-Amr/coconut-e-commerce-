<?php

namespace App\Services\Api\App\Guest\Brand;

use App\Models\Brand;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class BrandService
{
    use ApiResponseTrait;

    /**
     * Get all active brands
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->integer('per_page', 15);
            $brands = Brand::with(['translations', 'media'])
                ->where('active', true)
                ->orderBy('name')
                ->paginate($perPage);

            return $this->successResponse($brands, 'Brands retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve brands: ' . $e->getMessage());
        }
    }

}
