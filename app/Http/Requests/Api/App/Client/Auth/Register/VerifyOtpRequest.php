<?php

namespace App\Http\Requests\Api\App\Client\Auth\Register;

use App\Http\Requests\Api\MasterRequest;
use App\Services\Utilities\OtpService;

class VerifyOtpRequest extends MasterRequest
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
                'exists:otps,otp_code',
                'size:' . OtpService::MAX_NUMBER_OF_OTP
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'phone.required' => __('validation.custom.auth.phone.required'),
            'phone.exists' => __('validation.custom.auth.phone.exists'),
            'otp.required' => __('validation.custom.auth.otp.required'),
            'otp.exists' => __('validation.custom.auth.otp.invalid'),
            'otp.size' => __('validation.custom.auth.otp.digits'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'phone' => __('validation.attributes.phone'),
            'otp' => __('validation.attributes.otp'),
        ];
    }
}
