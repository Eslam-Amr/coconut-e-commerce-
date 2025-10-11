<?php

namespace App\Services\Api\Dashboard\Category;

use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CategoryService
{
    use ApiResponseTrait;

    /**
     * Get all categories with search, filter, and pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Category::with(['parent.translations', 'children.translations', 'translations']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('icon', 'like', "%{$search}%");
                });
            }

            // Filter by parent_id
            if ($request->has('parent_id')) {
                if ($request->parent_id === 'null' || $request->parent_id === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            // Filter by active status
            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            // Sort functionality
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if (in_array($sortBy, ['name', 'icon', 'parent_id', 'active', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $categories = $query->paginate($perPage);

            return $this->successResponse($categories, 'Categories retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve categories', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new category
     */
    public function store(array $data)
    {
        try {
            $category = Category::create($data);
            $category->load(['parent.translations', 'children.translations', 'translations']);
            return $this->successResponse($category, 'Category created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create category', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific category
     */
    public function show(Category $category)
    {
        try {
            $category->load(['parent.translations', 'children.translations', 'products', 'attributes', 'translations']);
            return $this->successResponse($category, 'Category retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve category', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a category
     */
    public function update(Category $category, array $data)
    {
        // dd($data);
        try {
            $category->update($data);
            $category->load(['parent.translations', 'children.translations', 'products', 'translations']);
            return $this->successResponse($category, 'Category updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update category', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a category
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has children
            if ($category->children()->count() > 0) {
                return $this->errorResponse('Cannot delete category with subcategories', 422);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return $this->errorResponse('Cannot delete category with products', 422);
            }

            // Check if category has attributes
            if ($category->attributes()->count() > 0) {
                return $this->errorResponse('Cannot delete category with attributes', 422);
            }

            $category->delete();
            return $this->successResponse(null, 'Category deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete category', ['error' => $e->getMessage()]);
        }
    }
}
