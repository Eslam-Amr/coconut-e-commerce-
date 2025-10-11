<?php

namespace App\Http\Requests\Api\Dashboard\District;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Support\Facades\Log;

class DistrictRequest extends MasterRequest
{
    public function rules(): array
    {
        Log::info('DistrictRequest');
        $requireOrSometimes = $this->getRequireOrSometimes();
        $district = $this->route('district');

        $districtId = null;
        if (is_object($district) && isset($district->id)) {
            $districtId = $district->id;
        } elseif (is_string($district) || is_numeric($district)) {
            $districtId = $district;
        }

        return [
            'ar.name' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($districtId) {
                    $cityId = request()->input('city_id');
                    $query = \App\Models\DistrictTranslation::where('name', $value)
                        ->where('locale', 'ar')
                        ->whereHas('district', function ($q) use ($cityId) {
                            if ($cityId) {
                                $q->where('city_id', $cityId);
                            }
                        });
                    
                    if ($districtId) {
                        $query->where('district_id', '!=', $districtId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The Arabic district name has already been taken.');
                    }
                }
            ],
            'en.name' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($districtId) {
                    $cityId = request()->input('city_id');
                    $query = \App\Models\DistrictTranslation::where('name', $value)
                        ->where('locale', 'en')
                        ->whereHas('district', function ($q) use ($cityId) {
                            if ($cityId) {
                                $q->where('city_id', $cityId);
                            }
                        });
                    
                    if ($districtId) {
                        $query->where('district_id', '!=', $districtId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The English district name has already been taken.');
                    }
                }
            ],
            'city_id' => [$requireOrSometimes, 'exists:cities,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ar.name.required' => 'The Arabic district name is required.',
            'en.name.required' => 'The English district name is required.',
            'city_id.exists' => 'The selected city does not exist.',
        ];
    }
}
