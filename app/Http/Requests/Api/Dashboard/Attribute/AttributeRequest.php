<?php

namespace App\Http\Requests\Api\Dashboard\Attribute;

use App\Http\Requests\Api\MasterRequest;

class AttributeRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requeireOrSometime = $this->getRequireOrSometimes();

        // dd($this->route('attribute'));
        $attributeId = $this->route('attribute');
        return [
            'ar.name' => [$requeireOrSometime, 'string', 'max:255', 'unique:attribute_translations,name,' . $attributeId],
            'en.name' => [$requeireOrSometime, 'string', 'max:255', 'unique:attribute_translations,name,' . $attributeId],
            // 'ar.name' => [$requeireOrSometime, 'string', 'max:255'],
            // 'en.name' => [$requeireOrSometime, 'string', 'max:255'],
            
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
            'ar.name.required' => 'The Arabic attribute name is required.',
            'ar.name.string' => 'The Arabic attribute name must be a string.',
            'ar.name.max' => 'The Arabic attribute name may not be greater than 255 characters.',
            'en.name.required' => 'The English attribute name is required.',
            'en.name.string' => 'The English attribute name must be a string.',
            'en.name.max' => 'The English attribute name may not be greater than 255 characters.',
        ];
    }
}
