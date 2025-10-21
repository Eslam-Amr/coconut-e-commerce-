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
    

    /**
     * Get custom attributes for validator errors.
     */
    
}
