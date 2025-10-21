<?php

namespace App\Http\Controllers\Api\App\Client\Product;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Client\Product\ProductService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class ProductController extends Controller implements HasMiddleware
{

    use ApiResponseTrait;

    public static function middleware(): array
    {
        return [
            'client'
        ];
    }


    public function __construct(private ProductService $productService) {}

    // public function recommendations(Request $request)
    // {
    //     return $this->productService->recommendations($request);
    // }
}
