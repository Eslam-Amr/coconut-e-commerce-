<?php

namespace App\Http\Requests\Api\Dashboard\Admin;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;

class RevokePermissionRequest extends MasterRequest
{
    use BilingualValidationTrait;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'permission_id' => 'required|exists:permissions,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'permission_id.required' => __('validation.required', ['attribute' => __('messages.permission')]),
            'permission_id.exists' => __('validation.exists', ['attribute' => __('messages.permission')]),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'permission_id' => __('messages.permission'),
        ];
    }
}
