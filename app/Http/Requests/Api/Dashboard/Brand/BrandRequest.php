<?php

namespace App\Http\Requests\Api\Dashboard\Brand;

use App\Http\Requests\Api\MasterRequest;


class BrandRequest extends MasterRequest
{
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $brand = $this->route('brand');

        $brandId = null;
        if (is_object($brand) && isset($brand->id)) {
            $brandId = $brand->id;
        } elseif (is_string($brand) || is_numeric($brand)) {
            $brandId = $brand;
        }

        return [
            'ar.name' => [
                $requireOrSometimes,
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($brandId) {
                    $query = \App\Models\BrandTranslation::where('name', $value)
                        ->where('locale', 'ar');

                    if ($brandId) {
                        $query->where('brand_id', '!=', $brandId);
                    }

                    if ($query->exists()) {
                        $fail('The Arabic brand name has already been taken.');
                    }
                }
            ],
            'en.name' => [
                $requireOrSometimes,
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($brandId) {
                    $query = \App\Models\BrandTranslation::where('name', $value)
                        ->where('locale', 'en');

                    if ($brandId) {
                        $query->where('brand_id', '!=', $brandId);
                    }

                    if ($query->exists()) {
                        $fail('The English brand name has already been taken.');
                    }
                }
            ],
            'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'active' => ['sometimes', 'boolean'],
            'logo' => ['sometimes', 'string'],
            // 'x' => ['required', 'boolean'],
            // 'y' => ['required', 'boolean'],
        ];
    }

    
}


