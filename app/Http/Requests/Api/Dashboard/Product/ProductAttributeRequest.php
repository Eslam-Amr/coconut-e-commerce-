<?php

namespace App\Http\Requests\Api\Dashboard\Product;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductAttributeRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        // dd($this->route('product_attribute'));
        $productAttributeId = $this->route('product_attribute') ? $this->route('product_attribute') : null;
        return [
            'product_id' => [
                $requireOrSometimes,
                'integer',
                'exists:products,id'
            ],
            'attribute_id' => [
                $requireOrSometimes,
                'integer',
                'exists:attributes,id'
            ],
            'attribute_value_id' => [
                $requireOrSometimes,
                'integer',
                'exists:attribute_values,id'
            ],
            'attributes' => [
                // $requireOrSometimes,
                'sometimes',
                'array',
                'min:1'
            ],
            'attributes.*.attribute_id' => [
                'required_with:attributes',
                'integer',
                'exists:attributes,id'
            ],
            'attributes.*.attribute_value_id' => [
                'required_with:attributes',
                'integer',
                'exists:attribute_values,id'
            ]
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
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'attribute_id.required' => 'Attribute is required.',
            'attribute_id.exists' => 'Selected attribute does not exist.',
            'attribute_value_id.required' => 'Attribute value is required.',
            'attribute_value_id.exists' => 'Selected attribute value does not exist.',
            'attributes.array' => 'Attributes must be an array.',
            'attributes.min' => 'At least one attribute is required.',
            'attributes.*.attribute_id.required_with' => 'Attribute ID is required for each attribute.',
            'attributes.*.attribute_id.integer' => 'Attribute ID must be an integer.',
            'attributes.*.attribute_id.exists' => 'Selected attribute does not exist.',
            'attributes.*.attribute_value_id.required_with' => 'Attribute value ID is required for each attribute.',
            'attributes.*.attribute_value_id.integer' => 'Attribute value ID must be an integer.',
            'attributes.*.attribute_value_id.exists' => 'Selected attribute value does not exist.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'attribute_id' => 'attribute',
            'attribute_value_id' => 'attribute value',
            'attributes' => 'attributes',
            'attributes.*.attribute_id' => 'attribute ID',
            'attributes.*.attribute_value_id' => 'attribute value ID'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate that the attribute belongs to the product's category
            if ($this->has('product_id') && $this->has('attribute_id')) {
                $productId = $this->input('product_id');
                $attributeId = $this->input('attribute_id');

                $product = \App\Models\Product::with('category')->find($productId);
                if ($product && $product->category) {
                    $attribute = \App\Models\Attribute::find($attributeId);
                    if (!$attribute || $attribute->category_id !== $product->category_id) {
                        $validator->errors()->add(
                            'attribute_id',
                            'The selected attribute does not belong to the product category.'
                        );
                    }
                }
            }

            // Validate that the attribute value belongs to the attribute
            if ($this->has('attribute_id') && $this->has('attribute_value_id')) {
                $attributeId = $this->input('attribute_id');
                $attributeValueId = $this->input('attribute_value_id');

                $attributeValue = \App\Models\AttributeValue::find($attributeValueId);
                if (!$attributeValue || $attributeValue->attribute_id !== $attributeId) {
                    $validator->errors()->add(
                        'attribute_value_id',
                        'The selected attribute value does not belong to the specified attribute.'
                    );
                }
            }

            // Validate bulk assignment attributes
            if ($this->has('attributes') && is_array($this->input('attributes'))) {
                $productId = $this->input('product_id');
                $attributes = $this->input('attributes');

                if ($productId) {
                    $product = \App\Models\Product::with('category')->find($productId);
                    if ($product && $product->category) {
                        foreach ($attributes as $index => $attributeData) {
                            if (isset($attributeData['attribute_id'])) {
                                $attributeId = $attributeData['attribute_id'];
                                $attribute = \App\Models\Attribute::find($attributeId);
                                
                                if (!$attribute || $attribute->category_id !== $product->category_id) {
                                    $validator->errors()->add(
                                        "attributes.{$index}.attribute_id",
                                        'The selected attribute does not belong to the product category.'
                                    );
                                }

                                // Validate attribute value belongs to attribute
                                if (isset($attributeData['attribute_value_id'])) {
                                    $attributeValueId = $attributeData['attribute_value_id'];
                                    $attributeValue = \App\Models\AttributeValue::find($attributeValueId);
                                    
                                    if (!$attributeValue || $attributeValue->attribute_id !== $attributeId) {
                                        $validator->errors()->add(
                                            "attributes.{$index}.attribute_value_id",
                                            'The selected attribute value does not belong to the specified attribute.'
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });
    }
}
