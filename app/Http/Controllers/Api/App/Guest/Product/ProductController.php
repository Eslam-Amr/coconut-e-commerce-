<?php

namespace App\Http\Controllers\Api\App\Guest\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Api\App\Guest\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        return $this->productService->index($request);
    }

    public function show(Product $product)
    {
        return $this->productService->show($product);
    }

    public function recommendations(Request $request)
    {
        return $this->productService->recommendations($request);
    }

    public function trending(Request $request)
    {
        return $this->productService->trending($request);
    }

    public function featured(Request $request)
    {
        return $this->productService->featured($request);
    }

    public function mostOrdered(Request $request)
    {
        return $this->productService->mostOrdered($request);
    }

    public function topRated(Request $request)
    {
        return $this->productService->topRated($request);
    }

    public function related(Product $product, Request $request)
    {
        return $this->productService->related($product, $request);
    }
}



