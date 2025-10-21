<?php

namespace App\Http\Requests\Api\App\Client\Auth\Login;

use App\Http\Requests\Api\MasterRequest;

class LoginRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'phone' => [
            //     'required',
            //     'regex:/^[+]?[0-9]{7,15}$/',
            //     'exists:users,phone'
            // ],
            'phone' => [
                'required_without:email',   // phone is required if email is not present
                'regex:/^[+]?[0-9]{7,15}$/',
                'exists:users,phone'
            ],
            'email' => [
                'required_without:phone',   // email is required if phone is not present
                'email',
                'exists:users,email'
            ],
            'password' => [
                'required',
                'string',
            ],
        ];
    }
   
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        if (!$this->phone && !$this->email) {
            // attach error ONLY once
            $validator->errors()->add('phone_or_email', __('validation.custom.auth.phone_or_email_required'));
        }
    });
}

/**
 * Get custom messages for validator errors.
 */


/**
 * Get custom attributes for validator errors.
 */

}
