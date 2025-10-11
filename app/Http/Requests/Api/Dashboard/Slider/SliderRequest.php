<?php

namespace App\Http\Requests\Api\Dashboard\Slider;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SliderRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info('SliderRequest');
        $requireOrSometimes = $this->getRequireOrSometimes();
        $slider = $this->route('slider');
        
        // Handle both model instance and string ID
        if (is_object($slider) && isset($slider->id)) {
            $sliderId = $slider->id;
        } elseif (is_string($slider) || is_numeric($slider)) {
            $sliderId = $slider;
        } else {
            $sliderId = null;
        }

        return [
            // 'ar.title' => [
            //     $requireOrSometimes, 
            //     'string', 
            //     'max:255', 
            //     function ($attribute, $value, $fail) use ($sliderId) {
            //         $query = \App\Models\SliderTranslation::where('title', $value)
            //             ->where('locale', 'ar');
                    
            //         if ($sliderId) {
            //             $query->where('slider_id', '!=', $sliderId);
            //         }
                    
            //         if ($query->exists()) {
            //             $fail('The Arabic slider title has already been taken.');
            //         }
            //     }
            // ],
            // 'en.title' => [
            //     $requireOrSometimes, 
            //     'string', 
            //     'max:255', 
            //     function ($attribute, $value, $fail) use ($sliderId) {
            //         $query = \App\Models\SliderTranslation::where('title', $value)
            //             ->where('locale', 'en');
                    
            //         if ($sliderId) {
            //             $query->where('slider_id', '!=', $sliderId);
            //         }
                    
            //         if ($query->exists()) {
            //             $fail('The English slider title has already been taken.');
            //         }
            //     }
            // ],
            'ar.title' => [
                $requireOrSometimes,
                'string',
                'max:255',
                // Rule::unique('slider_translations', 'title')
                //     ->where('locale', 'ar')
                //     ->ignore($sliderId, 'slider_id'),
                Rule::unique('slider_translations', 'title')
                ->where(fn($query) => $query->where('locale', 'ar'))
                ->ignore($sliderId, 'slider_id'),
            ],
        
            'en.title' => [
                $requireOrSometimes,
                'string',
                'max:255',
                // Rule::unique('slider_translations', 'title')
                //     ->where('locale', 'en')
                //     ->ignore($sliderId, 'slider_id'),
                Rule::unique('slider_translations', 'title')
                ->where(fn($query) => $query->where('locale', 'en'))
                ->ignore($sliderId, 'slider_id'),
            ],

            'image' => [$requireOrSometimes,'image','mimes:jpeg,png,jpg,gif,webp','max:5120'], // 5MB ma]x
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
            'ar.title.required' => 'The Arabic slider title is required.',
            'ar.title.string' => 'The Arabic slider title must be a string.',
            'ar.title.max' => 'The Arabic slider title may not be greater than 255 characters.',
            'en.title.required' => 'The English slider title is required.',
            'en.title.string' => 'The English slider title must be a string.',
            'en.title.max' => 'The English slider title may not be greater than 255 characters.',
            'ar.description.string' => 'The Arabic description must be a string.',
            'ar.description.max' => 'The Arabic description may not be greater than 1000 characters.',
            'en.description.string' => 'The English description must be a string.',
            'en.description.max' => 'The English description may not be greater than 1000 characters.',
            'image.required' => 'The slider image is required.',
            'image.image' => 'The slider image must be an image file.',
            'image.mimes' => 'The slider image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The slider image may not be greater than 5MB.',
            'link.string' => 'The slider link must be a string.',
            'link.max' => 'The slider link may not be greater than 500 characters.',
            'position.string' => 'The slider position must be a string.',
            'position.max' => 'The slider position may not be greater than 100 characters.',
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
            'ar.title' => 'Arabic slider title',
            'en.title' => 'English slider title',
            'ar.description' => 'Arabic description',
            'en.description' => 'English description',
            'image' => 'slider image',
            'link' => 'slider link',
            'position' => 'slider position',
            'active' => 'active status',
            'sort_order' => 'sort order',
        ];
    }
}
