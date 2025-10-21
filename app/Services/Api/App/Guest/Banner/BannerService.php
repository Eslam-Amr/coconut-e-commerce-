<?php

namespace App\Services\Api\App\Guest\Banner;

use App\Models\Banner;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class BannerService
{
    use ApiResponseTrait;

    /**
     * Get all active banners
     */
    public function index(Request $request)
    {
        try {
            $query = Banner::where('active', true)
                ->where(function ($q) {
                    $q->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                });

            // Pagination
            $perPage = $request->integer('per_page', 15);
            $banners = $query->paginate($perPage);

            return $this->successResponsePaginated(
                $banners,
                __('messages.retrieved_successfully')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.retrieval_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get a specific banner by ID
     */
    public function show($id)
    {
        try {
            $banner = Banner::where('id', $id)
                ->where('active', true)
                ->where(function ($q) {
                    $q->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                })
                ->first();

            if (!$banner) {
                return $this->errorResponse(__('messages.not_found'), 404);
            }

            return $this->successResponse($banner, __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.retrieval_failed') . ': ' . $e->getMessage());
        }
    }
}
