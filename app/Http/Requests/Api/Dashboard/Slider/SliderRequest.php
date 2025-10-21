<?php

namespace App\Http\Requests\Api\Dashboard\Slider;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
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
    

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    
}
