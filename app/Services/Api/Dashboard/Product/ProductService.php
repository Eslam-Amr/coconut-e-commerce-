<?php

namespace App\Services\Api\Dashboard\Product;

use App\Models\Product;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ProductService
{
	use ApiResponseTrait;

	public function index(Request $request)
	{
		try {
			$query = Product::with([
				'category.translations', 
				'brand.translations', 
				'media',
				'translations',
				'variants.attributeValues.attribute.translations',
				'variants.attributeValues.translations',
				'productAttributes.attribute.translations',
				'productAttributes.attributeValue.translations'
			]);

			if ($request->filled('search')) {
				$search = $request->get('search');
				$query->whereHas('translations', function ($q) use ($search) {
					$q->where('name', 'like', "%{$search}%");
				});
			}

			if ($request->filled('category_id')) {
				$query->where('category_id', $request->integer('category_id'));
			}

			if ($request->filled('brand_id')) {
				$query->where('brand_id', $request->integer('brand_id'));
			}

			if ($request->filled('active')) {
				$query->where('active', $request->boolean('active'));
			}

			if ($request->filled('has_variants')) {
				if ($request->boolean('has_variants')) {
					$query->has('variants');
				} else {
					$query->doesntHave('variants');
				}
			}

			if ($request->filled('has_attributes')) {
				if ($request->boolean('has_attributes')) {
					$query->has('productAttributes');
				} else {
					$query->doesntHave('productAttributes');
				}
			}

			$perPage = $request->integer('per_page', 15);
			$products = $query->paginate($perPage);

			return $this->successResponse($products, 'Products retrieved successfully');
		} catch (\Exception $e) {
			return $this->serverErrorResponse('Failed to retrieve products', ['error' => $e->getMessage()]);
		}
	}

	public function store(array $data)
	{
		try {
			$product = Product::create($data);
			$product->load([
				'category.translations', 
				'brand.translations', 
				'media',
				'translations',
				'variants.attributeValues.attribute.translations',
				'variants.attributeValues.translations',
				'productAttributes.attribute.translations',
				'productAttributes.attributeValue.translations'
			]);
			return $this->successResponse($product, 'Product created successfully', 201);
		} catch (\Exception $e) {
			return $this->serverErrorResponse('Failed to create product', ['error' => $e->getMessage()]);
		}
	}

	public function show(Product $product)
	{
		try {
			$product->load([
				'category.translations', 
				'brand.translations', 
				'media',
				'translations',
				'variants.attributeValues.attribute.translations',
				'variants.attributeValues.translations',
				'productAttributes.attribute.translations',
				'productAttributes.attributeValue.translations'
			]);
			return $this->successResponse($product, 'Product retrieved successfully');
		} catch (\Exception $e) {
			return $this->serverErrorResponse('Failed to retrieve product', ['error' => $e->getMessage()]);
		}
	}

	public function update(Product $product, array $data)
	{
		try {
			$product->update($data);
			$product->load([
				'category.translations', 
				'brand.translations', 
				'media',
				'translations',
				'variants.attributeValues.attribute.translations',
				'variants.attributeValues.translations',
				'productAttributes.attribute.translations',
				'productAttributes.attributeValue.translations'
			]);
			return $this->successResponse($product, 'Product updated successfully');
		} catch (\Exception $e) {
			return $this->serverErrorResponse('Failed to update product', ['error' => $e->getMessage()]);
		}
	}

	public function destroy(Product $product)
	{
		try {
			// Add business rule checks here if needed
			$product->delete();
			return $this->successResponse(null, 'Product deleted successfully');
		} catch (\Exception $e) {
			return $this->serverErrorResponse('Failed to delete product', ['error' => $e->getMessage()]);
		}
	}
}


