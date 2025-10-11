<?php

namespace App\Http\Requests\Api\General\Profile;

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
            'new_phone' => 'required|string',
            'otp' => 'required|string|size:4',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'new_phone.required' => 'رقم الهاتف الجديد مطلوب.',
            'new_phone.regex' => 'رقم الهاتف يجب أن يكون 11 رقم.',
            'otp.required' => 'رمز التحقق مطلوب.',
            'otp.size' => 'رمز التحقق يجب أن يكون 4 أرقام.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'new_phone' => 'رقم الهاتف الجديد',
            'otp' => 'رمز التحقق',
        ];
    }
}
