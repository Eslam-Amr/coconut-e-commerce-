<?php

namespace App\Http\Requests\Api\Dashboard\Product;

use App\Http\Requests\Api\MasterRequest;
use Illuminate\Support\Facades\Log;

class ProductRequest extends MasterRequest
{
	public function rules(): array
	{
		$requireOrSometimes = $this->getRequireOrSometimes();
		Log::info('ProductRequest');
		Log::info($requireOrSometimes);
		Log::info($this->isMethod('post'));
		Log::info($this->isMethod('put'));
		Log::info($this->isMethod('patch'));
		Log::info($this->method());
		$product = $this->route('product');

		$productId = null;
		if (is_object($product) && isset($product->id)) {
			$productId = $product->id;
		} elseif (is_string($product) || is_numeric($product)) {
			$productId = $product;
		}

		return [
			'ar.name' => [$requireOrSometimes, 'string', 'max:255', 'unique:product_translations,name,' . $productId],
			'en.name' => [$requireOrSometimes, 'string', 'max:255', 'unique:product_translations,name,' . $productId],
			'ar.description' => [$requireOrSometimes, 'string', 'max:1000'],
			'en.description' => [$requireOrSometimes, 'string', 'max:1000'],
			'category_id' => [$requireOrSometimes, 'integer', 'exists:categories,id'],
			'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id'],
			'total_quantity' => ['sometimes', 'integer', 'min:0'],
			'base_price' => [$requireOrSometimes, 'numeric', 'min:0'],
			'active' => ['sometimes', 'boolean'],
			'images' => ['sometimes', 'array', 'max:10'],
			'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
			// 'image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
			// 'x'=>['required', 'boolean'],
			// 'y'=>['required', 'boolean'],
		];
	}

	public function messages(): array
	{
		return [
			'name.required' => 'The product name is required.',
			'category_id.required' => 'The category is required.',
			'category_id.exists' => 'The selected category is invalid.',
			'brand_id.exists' => 'The selected brand is invalid.',
			'base_price.required' => 'The base price is required.',
			'image.image' => 'The product image must be an image file.',
			'image.mimes' => 'The product image must be a file of type: jpeg, png, jpg, gif, webp.',
			'image.max' => 'The product image may not be greater than 5MB.',
		];
	}
}


// -F "ar[name]=منتج تجريبي" ^
