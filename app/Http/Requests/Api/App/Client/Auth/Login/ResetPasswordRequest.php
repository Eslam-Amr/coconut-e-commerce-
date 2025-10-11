<?php

namespace App\Http\Requests\Api\App\Client\Auth\Login;


use App\Http\Requests\Api\MasterRequest;

class ResetPasswordRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'regex:/^[+]?[0-9]{7,15}$/',
                'exists:users,phone'
            ],
            'otp' => [
                'required',
                'string',
                'exists:otps,otp_code'
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed'
            ],
            'password_confirmation' => [
                'required',
                'string',
                'min:6'
            ],
        ];
    }
}
