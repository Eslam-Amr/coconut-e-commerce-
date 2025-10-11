<?php

namespace App\Http\Controllers\Api\Dashboard\Product;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Product\ProductRequest;
use App\Models\Product;
use App\Services\Api\Dashboard\Product\ProductService;

class ProductController extends GenericCrudController
{
	public function __construct(ProductService $productService)
	{
		parent::__construct(
			$productService,
			ProductRequest::class,
			Product::class
		);
	}
}


