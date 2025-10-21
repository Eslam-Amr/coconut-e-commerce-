<?php

namespace App\Http\Requests\Api\General\Settings;

use App\Http\Requests\Api\MasterRequest;


class ChangePasswordRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    

    /**
     * Get custom attributes for validator errors.
     */
    
}
