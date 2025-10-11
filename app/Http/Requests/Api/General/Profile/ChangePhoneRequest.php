<?php

namespace App\Http\Requests\Api\General\Profile;

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
    public function messages(): array
    {
        return [
            'new_phone.required' => 'رقم الهاتف الجديد مطلوب.',
            'new_phone.unique' => 'رقم الهاتف الجديد مستخدم بالفعل.',
            'new_phone.regex' => 'رقم الهاتف يجب أن يكون 11 رقم.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'new_phone' => 'رقم الهاتف الجديد',
        ];
    }
}
