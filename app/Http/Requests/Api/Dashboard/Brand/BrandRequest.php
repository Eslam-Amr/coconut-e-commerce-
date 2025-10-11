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

    public function messages(): array
    {
        return [
            'ar.name.required' => 'The Arabic brand name is required.',
            'en.name.required' => 'The English brand name is required.',
            'image.image' => 'The brand image must be an image file.',
            'image.mimes' => 'The brand image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The brand image may not be greater than 5MB.',
            'active.boolean' => 'The active field must be true or false.',
        ];
    }
}


