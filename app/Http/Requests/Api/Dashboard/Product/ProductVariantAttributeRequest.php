<?php

namespace App\Http\Requests\Api\Dashboard\Product;

use App\Http\Requests\Api\MasterRequest;

class ProductVariantAttributeRequest extends MasterRequest
{
    public function rules(): array
    {
        return [
            'attribute_value_id' => ['required', 'integer', 'exists:attribute_values,id']
        ];
    }

    public function messages(): array
    {
        return [
            'attribute_value_id.required' => 'Attribute value is required.',
            'attribute_value_id.exists' => 'Selected attribute value does not exist.'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $routeParam = $this->route('product_variant');
            if (!$routeParam) {
                $validator->errors()->add('product_variant_id', 'Product variant is required.');
                return;
            }

            // Resolve model from route parameter (can be ID or model instance)
            $variant = $routeParam instanceof \App\Models\ProductVariant
                ? $routeParam
                : \App\Models\ProductVariant::find($routeParam);

            if (!$variant) {
                $validator->errors()->add('product_variant_id', 'Selected product variant does not exist.');
                return;
            }

            // Ensure necessary relations are loaded
            $variant->loadMissing(['product', 'attributeValues']);

            $attributeValueId = (int) $this->input('attribute_value_id');
            $attributeValue = \App\Models\AttributeValue::find($attributeValueId);
            if (!$attributeValue) {
                $validator->errors()->add('attribute_value_id', 'Selected attribute value does not exist.');
                return;
            }

            // Ensure the attribute for this value is allowed for the product (exists in product_attributes)
            $isAllowed = \App\Models\ProductAttribute::where('product_id', $variant->product_id)
                ->where('attribute_id', $attributeValue->attribute_id)
                ->exists();
            if (!$isAllowed) {
                $validator->errors()->add('attribute_value_id', 'This attribute is not configured for the product.');
                return;
            }

            // Prevent conflicting assignment: variant cannot have multiple values of the same attribute
            $existingConflicting = $variant->attributeValues()
                ->where('attribute_values.attribute_id', $attributeValue->attribute_id)
                ->where('attribute_values.id', '!=', $attributeValueId)
                ->exists();
            if ($existingConflicting) {
                // Not an error; we will replace in service, but validation can allow
            }
        });
    }
}


