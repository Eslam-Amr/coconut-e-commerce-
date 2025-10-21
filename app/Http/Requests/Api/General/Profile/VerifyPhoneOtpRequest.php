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
    

    /**
     * Get custom attributes for validator errors.
     */
    
}
