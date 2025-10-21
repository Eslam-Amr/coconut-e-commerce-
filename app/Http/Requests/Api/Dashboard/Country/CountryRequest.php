<?php

namespace App\Http\Requests\Api\Dashboard\Country;

use App\Http\Requests\Api\MasterRequest;


class CountryRequest extends MasterRequest
{
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $country = $this->route('country');

        $countryId = null;
        if (is_object($country) && isset($country->id)) {
            $countryId = $country->id;
        } elseif (is_string($country) || is_numeric($country)) {
            $countryId = $country;
        }

        return [ 'ar.name' => [
            $requireOrSometimes, 
            'string', 
            'max:255', 
            function ($attribute, $value, $fail) use ($countryId) {
                $query = \App\Models\CountryTranslation::where('name', $value)
                    ->where('locale', 'ar');
                
                if ($countryId) {
                    $query->where('country_id', '!=', $countryId);
                }
                
                if ($query->exists()) {
                    $fail('The Arabic country name has already been taken.');
                }
            }
        ],
        'en.name' => [
            $requireOrSometimes, 
            'string', 
            'max:255', 
            function ($attribute, $value, $fail) use ($countryId) {
                $query = \App\Models\CountryTranslation::where('name', $value)
                    ->where('locale', 'en');
                
                if ($countryId) {
                    $query->where('country_id', '!=', $countryId);
                }
                
                if ($query->exists()) {
                    $fail('The English country name has already been taken.');
                }
            }
        ],
            
        ];
    }

    
}
