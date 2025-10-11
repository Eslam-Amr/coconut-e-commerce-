<?php

namespace App\Http\Requests\Api\General\Profile;

use App\Http\Requests\Api\MasterRequest;

class DeleteAccountRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => 'required|string'

        ];
    }
}
