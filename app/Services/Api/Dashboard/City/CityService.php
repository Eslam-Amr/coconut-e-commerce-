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

            return $this->successResponse($cities, __('messages.cities_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();
            
            $city = City::create($data);


            $city->load(['country', 'translations']);
            DB::commit();

            return $this->successResponse($city, __('messages.city_created'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(City $city)
    {
        try {
            $city->load(['country', 'translations', 'districts']);
            return $this->successResponse($city, __('messages.city_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(City $city, array $data)
    {
        try {
            DB::beginTransaction();


            $city->update($data);
            $city->load(['country', 'translations']);
            DB::commit();

            return $this->successResponse($city, __('messages.city_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(City $city)
    {
        try {
            // Check if city has districts
            if ($city->districts()->count() > 0) {
                return $this->errorResponse(__('messages.cannot_delete_city_with_districts'), 422);
            }

            $city->delete();
            return $this->successResponse(null, __('messages.city_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }
}
