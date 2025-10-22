<?php

namespace App\Services\Api\Dashboard\StaticPage;

use App\Models\StaticPage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StaticPageService
{
    use ApiResponseTrait;

    /**
     * Get all static pages with search, filter, and pagination
     */
    public function index(Request $request)
    {
        try {
            $query = StaticPage::with(['translations']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
                });
            }


            // Sort functionality
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $staticPages = $query->paginate($perPage);

            return $this->successResponse($staticPages, 'Static pages retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve static pages', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new static page
     */
    public function store(array $data)
    {
        try {
            $staticPage = StaticPage::create($data);
            $staticPage->load(['translations']);
            return $this->successResponse($staticPage, 'Static page created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create static page', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific static page
     */
    public function show(StaticPage $staticPage)
    {
        try {
            $staticPage->load(['translations']);
            return $this->successResponse($staticPage, 'Static page retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve static page', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a static page
     */
    public function update(StaticPage $staticPage, array $data)
    {
        try {
            $staticPage->update($data);
            $staticPage->load(['translations']);
            return $this->successResponse($staticPage, 'Static page updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update static page', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a static page
     */
    public function destroy(StaticPage $staticPage)
    {
        try {
            $staticPage->delete();
            return $this->successResponse(null, 'Static page deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete static page', ['error' => $e->getMessage()]);
        }
    }
}
