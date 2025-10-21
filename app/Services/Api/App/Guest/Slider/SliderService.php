<?php

namespace App\Services\Api\App\Guest\Slider;

use App\Models\Slider;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class SliderService
{
    use ApiResponseTrait;

    /**
     * Get all active sliders
     */
    public function index(Request $request)
    {
        try {
            $query = Slider::where('active', true)
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
            $sliders = $query->paginate($perPage);

            return $this->successResponsePaginated(
                $sliders,
                __('messages.retrieved_successfully')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.retrieval_failed') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get a specific slider by ID
     */
    public function show($id)
    {
        try {
            $slider = Slider::where('id', $id)
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

            if (!$slider) {
                return $this->errorResponse(__('messages.not_found'), 404);
            }

            return $this->successResponse($slider, __('messages.retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->errorResponse(__('messages.retrieval_failed') . ': ' . $e->getMessage());
        }
    }
}
