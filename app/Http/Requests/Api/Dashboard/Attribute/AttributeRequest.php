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
    
}
