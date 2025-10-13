<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\App\Guest\Product\ProductController;

Route::controller(ProductController::class)->group(function () {
    Route::get('products',  'index');
    Route::get('products/recommendations',  'recommendations');
    Route::get('products/{product}',  'show');
});
