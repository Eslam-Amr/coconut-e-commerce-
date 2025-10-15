<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\App\Guest\Product\ProductController;

Route::controller(ProductController::class)->group(function () {
    
    Route::get('products',  'index');

    Route::get('products/recommendations',  'recommendations');

    Route::get('products/trending',  'trending');

    Route::get('products/featured',  'featured');

    Route::get('products/most-ordered',  'mostOrdered');

    Route::get('products/top-rated',  'topRated');

    Route::get('products/{product}',  'show');

    Route::get('products/{product}/related',  'related');

});
