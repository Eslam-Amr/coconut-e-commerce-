<?php

namespace App\Http\Requests\Api\Dashboard\City;

use App\Http\Requests\Api\MasterRequest;

class CityRequest extends MasterRequest
{
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $city = $this->route('city');

        $cityId = null;
        if (is_object($city) && isset($city->id)) {
            $cityId = $city->id;
        } elseif (is_string($city) || is_numeric($city)) {
            $cityId = $city;
        }

        return [
            'ar.name' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($cityId) {
                    $query = \App\Models\CityTranslation::where('name', $value)
                        ->where('locale', 'ar');
                    
                    if ($cityId) {
                        $query->where('city_id', '!=', $cityId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The Arabic city name has already been taken.');
                    }
                }
            ],
            'en.name' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($cityId) {
                    $query = \App\Models\CityTranslation::where('name', $value)
                        ->where('locale', 'en');
                    
                    if ($cityId) {
                        $query->where('city_id', '!=', $cityId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The English city name has already been taken.');
                    }
                }
            ],
            'country_id' => [$requireOrSometimes, 'exists:countries,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ar.name.required' => 'The Arabic city name is required.',
            'en.name.required' => 'The English city name is required.',
            'country_id.exists' => 'The selected country does not exist.',
        ];
    }
}
