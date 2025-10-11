<?php

namespace App\Services\Api\Dashboard\City;

use App\Models\City;
use App\Models\CityTranslation;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CityService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = City::with(['country', 'translations']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('country_id')) {
                $query->where('country_id', $request->get('country_id'));
            }

          

            $perPage = $request->integer('per_page', 15);
            $cities = $query->paginate($perPage);

            return $this->successResponse($cities, 'Cities retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve cities', ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();
            
            $city = City::create($data);


            $city->load(['country', 'translations']);
            DB::commit();

            return $this->successResponse($city, 'City created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Failed to create city', ['error' => $e->getMessage()]);
        }
    }

    public function show(City $city)
    {
        try {
            $city->load(['country', 'translations', 'districts']);
            return $this->successResponse($city, 'City retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve city', ['error' => $e->getMessage()]);
        }
    }

    public function update(City $city, array $data)
    {
        try {
            DB::beginTransaction();


            $city->update($data);
            $city->load(['country', 'translations']);
            DB::commit();

            return $this->successResponse($city, 'City updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse('Failed to update city', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(City $city)
    {
        try {
            // Check if city has districts
            if ($city->districts()->count() > 0) {
                return $this->errorResponse('Cannot delete city with districts', 422);
            }

            $city->delete();
            return $this->successResponse(null, 'City deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete city', ['error' => $e->getMessage()]);
        }
    }
}
