<?php

namespace App\Http\Requests\Api\App\Client\Auth\Register;

use App\Http\Requests\Api\MasterRequest;

class RegisterRequest extends MasterRequest
{


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => [
                'required',
                'regex:/^[+]?[0-9]{7,15}$/',
                'unique:users,phone'
            ],

            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.custom.auth.name.required'),
            'name.max' => __('validation.custom.auth.name.max'),
            'phone.required' => __('validation.custom.auth.phone.required'),
            'phone.max' => __('validation.custom.auth.phone.max'),
            'phone.unique' => __('validation.custom.auth.phone.unique'),
            'email.required' => __('validation.custom.auth.email.required'),
            'email.email' => __('validation.custom.auth.email.email'),
            'email.max' => __('validation.custom.auth.email.max'),
            'email.unique' => __('validation.custom.auth.email.unique'),
            'password.required' => __('validation.custom.auth.password.required'),
            'password.min' => __('validation.custom.auth.password.min'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => __('validation.attributes.name'),
            'phone' => __('validation.attributes.phone'),
            'email' => __('validation.attributes.email'),
            'password' => __('validation.attributes.password'),
        ];
    }
}
