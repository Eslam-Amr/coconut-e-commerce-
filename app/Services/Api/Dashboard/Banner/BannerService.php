<?php

namespace App\Services\Api\Dashboard\Banner;

use App\Models\Banner;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class BannerService
{
    use ApiResponseTrait;

    /**
     * Get all banners with search, filter, and pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Banner::with(['translations']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }
            
            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            // Filter by active status
            if ($request->has('active')) {
                $query->where('active', $request->boolean('active'));
            }

            // Sort functionality

            // Pagination
            $perPage = $request->get('per_page', 15);
            $banners = $query->paginate($perPage);

            return $this->successResponse($banners, __('messages.banners_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new banner
     */
    public function store(array $data)
    {
        try {
            $banner = Banner::create($data);
            $banner->load(['translations']);
            return $this->successResponse($banner, __('messages.banner_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific banner
     */
    public function show(Banner $banner)
    {
        try {
            $banner->load(['translations']);
            return $this->successResponse($banner, __('messages.banner_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a banner
     */
    public function update(Banner $banner, array $data)
    {
        // dd($banner);
        try {
            $banner->update($data);
            $banner->load(['translations','media']);
            return $this->successResponse($banner, __('messages.banner_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a banner
     */
    public function destroy(Banner $banner)
    {
        try {
            $banner->delete();
            return $this->successResponse(null, __('messages.banner_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

}
