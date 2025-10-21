<?php

namespace App\Services\Api\App\Guest\StaticPage;

use App\Models\StaticPage;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StaticPageService
{
    use ApiResponseTrait;

    /**
     * Get all static pages
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->integer('per_page', 15);

            $paginatedPages = StaticPage::paginate($perPage);
            return $this->successResponsePaginated(
                $paginatedPages,
                'Static pages retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve static pages: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific static page by slug
     */
    public function show($title)
    {
        try {
            $staticPage = StaticPage::whereHas('translations', function ($query) use ($title) {
                // Normalize input: replace _ and - with spaces, then lowercase it
                $normalizedTitle = strtolower(str_replace(['_', '-'], ' ', $title));
            
                $query->whereRaw('LOWER(REPLACE(REPLACE(title, "_", " "), "-", " ")) LIKE ?', ['%' . $normalizedTitle . '%']);
            })->first();
            
         

            return $this->successResponse($staticPage, 'Static page retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve static page: ' . $e->getMessage());
        }
    }
}
