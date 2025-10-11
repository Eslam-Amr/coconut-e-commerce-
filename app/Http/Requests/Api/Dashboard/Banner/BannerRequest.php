<?php

namespace App\Http\Requests\Api\Dashboard\Banner;

use App\Http\Requests\Api\MasterRequest;

class BannerRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $banner = $this->route('banner');
        
        // Handle both model instance and string ID
        if (is_object($banner) && isset($banner->id)) {
            $bannerId = $banner->id;
        } elseif (is_string($banner) || is_numeric($banner)) {
            $bannerId = $banner;
        } else {
            $bannerId = null;
        }

        return [
            'ar.title' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($bannerId) {
                    $query = \App\Models\BannerTranslation::where('title', $value)
                        ->where('locale', 'ar');
                    
                    if ($bannerId) {
                        $query->where('banner_id', '!=', $bannerId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The Arabic banner title has already been taken.');
                    }
                }
            ],
            'en.title' => [
                $requireOrSometimes, 
                'string', 
                'max:255', 
                function ($attribute, $value, $fail) use ($bannerId) {
                    $query = \App\Models\BannerTranslation::where('title', $value)
                        ->where('locale', 'en');
                    
                    if ($bannerId) {
                        $query->where('banner_id', '!=', $bannerId);
                    }
                    
                    if ($query->exists()) {
                        $fail('The English banner title has already been taken.');
                    }
                }
            ],
            'image' => [$requireOrSometimes, 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
            'link' => ['nullable', 'string', 'max:500'],
            'active' => ['nullable', 'boolean'],
            'start_date' => [$requireOrSometimes, 'date'],
            'end_date' => [$requireOrSometimes, 'date'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ar.title.required' => 'The Arabic banner title is required.',
            'ar.title.string' => 'The Arabic banner title must be a string.',
            'ar.title.max' => 'The Arabic banner title may not be greater than 255 characters.',
            'en.title.required' => 'The English banner title is required.',
            'en.title.string' => 'The English banner title must be a string.',
            'en.title.max' => 'The English banner title may not be greater than 255 characters.',
            'ar.description.string' => 'The Arabic description must be a string.',
            'ar.description.max' => 'The Arabic description may not be greater than 1000 characters.',
            'en.description.string' => 'The English description must be a string.',
            'en.description.max' => 'The English description may not be greater than 1000 characters.',
            'image.required' => 'The banner image is required.',
            'image.image' => 'The banner image must be an image file.',
            'image.mimes' => 'The banner image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The banner image may not be greater than 5MB.',
            'link.string' => 'The banner link must be a string.',
            'link.max' => 'The banner link may not be greater than 500 characters.',
            'position.string' => 'The banner position must be a string.',
            'position.max' => 'The banner position may not be greater than 100 characters.',
            'active.boolean' => 'The active field must be true or false.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'The end date must be a valid date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ar.title' => 'Arabic banner title',
            'en.title' => 'English banner title',
            'ar.description' => 'Arabic description',
            'en.description' => 'English description',
            'image' => 'banner image',
            'link' => 'banner link',
            'active' => 'active status',
            'start_date' => 'start date',
            'end_date' => 'end date',
        ];
    }
}
