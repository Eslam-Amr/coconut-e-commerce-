<?php

namespace App\Services\Api\Dashboard\Slider;

use App\Models\Slider;
use App\Services\Utilities\MediaService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SliderService
{
    use ApiResponseTrait;
    public function __construct(private MediaService $mediaService)
    {
    }
    /**
     * Get all sliders with search, filter, and pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Slider::with(['translations','media']);

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
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

            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $sliders = $query->paginate($perPage);

            return $this->successResponse($sliders, __('messages.sliders_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new slider
     */
    public function store(array $data)
    {
        // dd($data);
        try {
            $slider = DB::transaction(function () use ($data) {
            $sliderData = Arr::except($data, ['image']);
            $slider = Slider::create($sliderData);
            // $mediaInfo = $this->mediaService->storeImage($request->file('image'), $slider, 'sliders');
            // if ($data['image']) {
            //     $this->mediaService->storeImage(
            //         $data['image'],
            //         $slider,
            //         'sliders'
            //     );
            // }
            // $slider->load('media');
            return $slider;

        });
        return $this->successResponse($slider, __('messages.slider_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific slider
     */
    public function show(Slider $slider)
    {
        try {
            $slider->load(['translations','media']);
            return $this->successResponse($slider, __('messages.slider_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a slider
     */
    public function update(Slider $slider, array $data)
    {
        try {
            $slider->update($data);
            $slider->load(['translations']);
            return $this->successResponse($slider, __('messages.slider_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a slider
     */
    public function destroy(Slider $slider)
    {
        try {
            $slider->delete();
            return $this->successResponse(null, __('messages.slider_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle active status of a slider
     */

}
