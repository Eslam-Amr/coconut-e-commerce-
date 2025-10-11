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

            return $this->successResponse($countries, 'Countries retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve countries', ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            $country = Country::create($data);
            return $this->successResponse($country, 'Country created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create country', ['error' => $e->getMessage()]);
        }
    }

    public function show(Country $country)
    {
        try {
            $country->load('cities.translations','translations');
            return $this->successResponse($country, 'Country retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve country', ['error' => $e->getMessage()]);
        }
    }

    public function update(Country $country, array $data)
    {
        try {
            $country->update($data);
            return $this->successResponse($country, 'Country updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update country', ['error' => $e->getMessage()]);
        }
    }

    public function destroy(Country $country)
    {
        try {
            // Check if country has cities
            if ($country->cities()->count() > 0) {
                return $this->errorResponse('Cannot delete country with cities', 422);
            }

            $country->delete();
            return $this->successResponse(null, 'Country deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete country', ['error' => $e->getMessage()]);
        }
    }
}
