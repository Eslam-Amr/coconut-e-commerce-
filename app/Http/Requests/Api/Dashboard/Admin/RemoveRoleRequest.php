<?php

namespace App\Http\Requests\Api\Dashboard\Admin;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;

class RemoveRoleRequest extends MasterRequest
{
    use BilingualValidationTrait;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'role_id' => 'required|exists:roles,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'role_id.required' => __('validation.required', ['attribute' => __('messages.role')]),
            'role_id.exists' => __('validation.exists', ['attribute' => __('messages.role')]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'role_id' => __('messages.role'),
        ];
    }
}
