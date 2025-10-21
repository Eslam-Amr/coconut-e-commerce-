<?php

namespace App\Http\Requests\Api\General\Settings;

use App\Http\Requests\Api\MasterRequest;


class ChangePhoneRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'new_phone' => 'required|string|unique:users,phone',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    

    /**
     * Get custom attributes for validator errors.
     */
    
}
