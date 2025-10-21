<?php

namespace App\Http\Requests\Api\Dashboard\Product;

use App\Http\Requests\Api\MasterRequest;
use App\Traits\BilingualValidationTrait;
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
    

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    

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
                    
                    if ($product) {
                        // Get valid attribute IDs configured for this product via product_attributes
                        $validAttributeIds = \App\Models\ProductAttribute::where('product_id', $product->id)
                            ->pluck('attribute_id')
                            ->unique()
                            ->toArray();

                        // Check if all attribute values belong to valid attributes for this product
                        $invalidAttributeValues = [];
                        if (!empty($validAttributeIds)) {
                            $invalidAttributeValues = \App\Models\AttributeValue::whereIn('id', $attributeValueIds)
                                ->whereNotIn('attribute_id', $validAttributeIds)
                                ->pluck('id')
                                ->toArray();
                        }

                        // dd($invalidAttributeValues);
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
