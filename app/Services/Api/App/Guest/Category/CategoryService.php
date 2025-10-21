<?php

namespace App\Services\Api\App\Guest\Category;

use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CategoryService
{
    use ApiResponseTrait;

    /**
     * Get all active categories
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->integer('per_page', 15);
            $categories = Category::with(['translations', 'media'])
                ->where('active', true)
                ->paginate($perPage);

            return $this->successResponse($categories, 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve categories: ' . $e->getMessage());
        }
    }
}
