<?php

namespace App\Http\Requests\Api\Dashboard\Product;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $requireOrSometimes = $this->getRequireOrSometimes();
        $variantId = $this->route('product_variant') ? $this->route('product_variant') : null;
        $productId = $this->input('product_id');

        return [
            'product_id' => [
                $requireOrSometimes,
                'integer',
                'exists:products,id'
            ],
            'sku' => [
                $requireOrSometimes,
                'string',
                'max:255',
                Rule::unique('product_variants', 'sku')->ignore($variantId)
            ],
            'price' => [
                $requireOrSometimes,
                'numeric',
                'min:0',
                'max:999999.99'
            ],
            'minimum_stock' => [
                $requireOrSometimes,
                'integer',
                'min:0'
            ],
            'stock' => [
                $requireOrSometimes,
                'integer',
                'min:0'
            ],
            'active' => [
                'boolean'
            ],
            'attribute_value_ids' => [
                'sometimes',
                'array'
            ],
            'attribute_value_ids.*' => [
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
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU is already taken.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price must be at least 0.',
            'minimum_stock.required' => 'Minimum stock is required.',
            'minimum_stock.integer' => 'Minimum stock must be an integer.',
            'minimum_stock.min' => 'Minimum stock must be at least 0.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be an integer.',
            'stock.min' => 'Stock must be at least 0.',
            'active.boolean' => 'Active status must be true or false.',
            'attribute_value_ids.array' => 'Attribute values must be an array.',
            'attribute_value_ids.*.integer' => 'Each attribute value ID must be an integer.',
            'attribute_value_ids.*.exists' => 'One or more attribute values do not exist.'
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
            'attribute_value_ids' => 'attribute values',
            'attribute_value_ids.*' => 'attribute value'
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
            // Validate that attribute values belong to the same product's category attributes
            if ($this->has('attribute_value_ids') && $this->has('product_id')) {
                $productId = $this->input('product_id');
                $attributeValueIds = $this->input('attribute_value_ids', []);

                if (!empty($attributeValueIds)) {
                    // Get the product's category
                    $product = \App\Models\Product::with('category')->find($productId);
                    
                    if ($product && $product->category) {
                        // Get valid attribute IDs for this category
                        $validAttributeIds = \App\Models\Attribute::where('category_id', $product->category->id)
                            ->pluck('id')
                            ->toArray();

                        // Check if all attribute values belong to valid attributes
                        $invalidAttributeValues = \App\Models\AttributeValue::whereIn('id', $attributeValueIds)
                            ->whereNotIn('attribute_id', $validAttributeIds)
                            ->pluck('id')
                            ->toArray();

                        if (!empty($invalidAttributeValues)) {
                            $validator->errors()->add(
                                'attribute_value_ids',
                                'Some attribute values do not belong to the product category.'
                            );
                        }

                        // Check for duplicate attributes (same attribute with multiple values)
                        $attributeIds = \App\Models\AttributeValue::whereIn('id', $attributeValueIds)
                            ->pluck('attribute_id')
                            ->toArray();

                        if (count($attributeIds) !== count(array_unique($attributeIds))) {
                            $validator->errors()->add(
                                'attribute_value_ids',
                                'Cannot assign multiple values for the same attribute.'
                            );
                        }
                    }
                }
            }
        });
    }
}
