<?php

namespace App\Http\Requests\Api\Dashboard\Attribute;

use App\Http\Requests\Api\MasterRequest;

class UpdateAttributeValueRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attribute_id' => ['sometimes', 'exists:attributes,id'],
            'value' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attribute_id.exists' => 'The selected attribute does not exist.',
            'sort_order.integer' => 'The sort order must be an integer.',
            'sort_order.min' => 'The sort order must be at least 0.',
        ];
    }
}
