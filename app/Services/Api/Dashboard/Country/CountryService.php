<?php

namespace App\Services\Api\Dashboard\Country;

use App\Models\Country;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CountryService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = Country::with('translations','cities.translations');

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }


            $perPage = $request->integer('per_page', 15);
            $countries = $query->paginate($perPage);

            return $this->successResponse($countries, __('messages.countries_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $country = Country::create($data);
            return $this->successResponse($country, __('messages.country_created'), 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(Country $country)
    {
        try {
            $country->load('cities.translations','translations');
            return $this->successResponse($country, __('messages.country_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(Country $country, array $data)
    {
        try {
            $country->update($data);
            return $this->successResponse($country, __('messages.country_updated_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Country $country)
    {
        try {
            // Check if country has cities
            if ($country->cities()->count() > 0) {
                return $this->errorResponse(__('messages.cannot_delete_country_with_cities'), 422);
            }

            $country->delete();
            return $this->successResponse(null, __('messages.country_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }
}
