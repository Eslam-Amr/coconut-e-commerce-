<?php

namespace App\Services\Api\Dashboard\District;

use App\Models\District;
use App\Models\DistrictTranslation;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistrictService
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $query = District::with(['city.country', 'city.translations', 'translations']);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->whereHas('translations', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->get('city_id'));
            }

            if ($request->filled('country_id')) {
                $query->whereHas('city', function ($q) use ($request) {
                    $q->where('country_id', $request->get('country_id'));
                });
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            if (in_array($sortBy, ['city_id', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = $request->integer('per_page', 15);
            $districts = $query->paginate($perPage);

            return $this->successResponse($districts, __('messages.districts_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function store(array $data)
    {
        try {
            DB::beginTransaction();
            // dd($data);
            $district = District::create($data);

            // Create or update translations if provided
            // foreach (['ar', 'en'] as $locale) {
            //     if (!empty($data[$locale]['name'] ?? null)) {
            //         DistrictTranslation::updateOrCreate(
            //             [
            //                 'district_id' => $district->id,
            //                 'locale' => $locale,
            //             ],
            //             [
            //                 'name' => $data[$locale]['name'],
            //             ]
            //         );
            //     }
            // }

            $district->load(['city.country', 'city.translations', 'translations']);
            DB::commit();

            return $this->successResponse($district, __('messages.district_created'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.creation_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function show(District $district)
    {
        try {
            $district->load(['city.country', 'city.translations', 'translations']);
            return $this->successResponse($district, __('messages.district_retrieved_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.retrieval_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function update(District $district, array $data)
    {
        try {
            DB::beginTransaction();


            $district->update($data);

            // foreach (['ar', 'en'] as $locale) {
            //     if (!empty($data[$locale]['name'] ?? null)) {
            //         DistrictTranslation::updateOrCreate(
            //             [
            //                 'district_id' => $district->id,
            //                 'locale' => $locale,
            //             ],
            //             [
            //                 'name' => $data[$locale]['name'],
            //             ]
            //         );
            //     }
            // }

            $district->load(['city.country', 'city.translations', 'translations']);
            DB::commit();

            return $this->successResponse($district, __('messages.district_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->serverErrorResponse(__('messages.update_failed'), ['error' => $e->getMessage()]);
        }
    }

    public function destroy(District $district)
    {
        try {
            $district->delete();
            return $this->successResponse(null, __('messages.district_deleted_successfully'));
        } catch (\Exception $e) {
            return $this->serverErrorResponse(__('messages.deletion_failed'), ['error' => $e->getMessage()]);
        }
    }
}
