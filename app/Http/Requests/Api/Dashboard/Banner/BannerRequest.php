<?php

namespace App\Http\Requests\Api\Dashboard\Banner;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;

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
}
