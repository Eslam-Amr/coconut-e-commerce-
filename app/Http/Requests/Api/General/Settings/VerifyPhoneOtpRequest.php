<?php

namespace App\Http\Requests\Api\General\Settings;

use App\Http\Requests\Api\MasterRequest;

class VerifyPhoneOtpRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'otp' => 'required|string|size:6',
            'new_phone' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'otp.required' => 'رمز التحقق مطلوب.',
            'otp.string' => 'رمز التحقق يجب أن يكون نص.',
            'otp.size' => 'رمز التحقق يجب أن يكون 6 أرقام.',
            'new_phone.required' => 'رقم الهاتف الجديد مطلوب.',
            'new_phone.string' => 'رقم الهاتف يجب أن يكون نص.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'otp' => 'رمز التحقق',
            'new_phone' => 'رقم الهاتف الجديد',
        ];
    }
}
