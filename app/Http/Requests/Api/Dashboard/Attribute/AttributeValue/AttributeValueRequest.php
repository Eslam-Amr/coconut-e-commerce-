<?php

namespace App\Http\Requests\Api\Dashboard\Attribute\AttributeValue;

use App\Http\Requests\Api\MasterRequest;

class AttributeValueRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requeireOrSometime = $this->getRequireOrSometimes();
        $attributeValueId = $this->route('attributeValue');

        return [
            'attribute_id' => [$requeireOrSometime, 'exists:attributes,id'],
            'ar.value' => [
                $requeireOrSometime, 
                'string', 
                'max:255',
                function ($attribute, $value, $fail) use ($attributeValueId) {
                    $attributeId = $this->input('attribute_id');
                    if (!$attributeId) return;

                    $query = \App\Models\AttributeValue::whereHas('translations', function ($q) use ($value) {
                        $q->where('locale', 'ar')->where('value', $value);
                    })->where('attribute_id', $attributeId);

                    if ($attributeValueId) {
                        $query->where('id', '!=', $attributeValueId);
                    }

                    if ($query->exists()) {
                        $fail('The Arabic value already exists for this attribute.');
                    }
                }
            ],
            'en.value' => [
                $requeireOrSometime, 
                'string', 
                'max:255',
                function ($attribute, $value, $fail) use ($attributeValueId) {
                    $attributeId = $this->input('attribute_id');
                    if (!$attributeId) return;

                    $query = \App\Models\AttributeValue::whereHas('translations', function ($q) use ($value) {
                        $q->where('locale', 'en')->where('value', $value);
                    })->where('attribute_id', $attributeId);

                    if ($attributeValueId) {
                        $query->where('id', '!=', $attributeValueId);
                    }

                    if ($query->exists()) {
                        $fail('The English value already exists for this attribute.');
                    }
                }
            ],
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
            'attribute_id.required' => 'The attribute is required.',
            'attribute_id.exists' => 'The selected attribute does not exist.',
            'ar.value.required' => 'The Arabic value is required.',
            'ar.value.string' => 'The Arabic value must be a string.',
            'ar.value.max' => 'The Arabic value may not be greater than 255 characters.',
            'en.value.required' => 'The English value is required.',
            'en.value.string' => 'The English value must be a string.',
            'en.value.max' => 'The English value may not be greater than 255 characters.',
        ];
    }
}
