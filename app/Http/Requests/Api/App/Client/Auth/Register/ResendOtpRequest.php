<?php

namespace App\Http\Requests\Api\App\Client\Auth\Register;

use App\Http\Requests\Api\MasterRequest;

class ResendOtpRequest extends MasterRequest
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
        ];
    }
}
