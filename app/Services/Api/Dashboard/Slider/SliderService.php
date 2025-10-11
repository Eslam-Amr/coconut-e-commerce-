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

            return $this->successResponse($sliders, 'Sliders retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve sliders', ['error' => $e->getMessage()]);
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
        return $this->successResponse($slider, __('messages.sliders.created'), 201);
        // return $this->successResponse(SliderResource::make($slider), __('messages.sliders.created'), 201);
       
            return $this->successResponse($slider, 'Slider created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create slider', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a specific slider
     */
    public function show(Slider $slider)
    {
        try {
            $slider->load(['translations','media']);
            return $this->successResponse($slider, 'Slider retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve slider', ['error' => $e->getMessage()]);
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
            return $this->successResponse($slider, 'Slider updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update slider', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a slider
     */
    public function destroy(Slider $slider)
    {
        try {
            $slider->delete();
            return $this->successResponse(null, 'Slider deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete slider', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle active status of a slider
     */

}
