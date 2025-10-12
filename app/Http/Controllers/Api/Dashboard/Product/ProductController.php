<?php

namespace App\Http\Controllers\Api\Dashboard\Product;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Product\ProductRequest;
use App\Models\Product;
use App\Services\Api\Dashboard\Product\ProductService;
use Illuminate\Routing\Controllers\HasMiddleware;

class ProductController extends GenericCrudController implements HasMiddleware
{
	protected static $permissionsList = [
		'index' => 'products.view',
		'show' => 'products.view',
		'store' => 'products.create',
		'update' => 'products.update',
		'destroy' => 'products.delete',
		'toggleActive' => 'products.toggle_active',
		// 'global' => [
		// 	'admin'
		// ]
	];
    protected static $middleware = ['admin'];


	public function __construct(ProductService $productService)
	{
		parent::__construct(
			$productService,
			ProductRequest::class,
			Product::class
		);
	}
}


