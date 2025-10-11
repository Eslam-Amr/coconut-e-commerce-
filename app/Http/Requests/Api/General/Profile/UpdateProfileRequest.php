<?php

namespace App\Http\Requests\Api\General\Profile;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
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
            'name.required' => __('validation.custom.auth.name.required'),
            'name.max' => __('validation.custom.auth.name.max'),
            'email.required' => __('validation.custom.auth.email.required'),
            'email.email' => __('validation.custom.auth.email.email'),
            'email.max' => __('validation.custom.auth.email.max'),
            'email.unique' => __('validation.custom.auth.email.unique'),
            'image.image' => __('validation.custom.profile.image.image'),
            'image.mimes' => __('validation.custom.profile.image.mimes'),
            'image.max' => __('validation.custom.profile.image.max'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'email' => __('validation.attributes.email'),
            'image' => __('validation.attributes.image'),
        ];
    }
}
