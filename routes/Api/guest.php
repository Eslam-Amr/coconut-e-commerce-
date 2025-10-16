<?php

use App\Http\Controllers\Api\App\Guest\Recommendation\GuestRecommendationController;
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




// Guest recommendation routes (no authentication required)
Route::get('/recommendations', [GuestRecommendationController::class, 'getRecommendations']);
Route::get('/recommendations/trending', [GuestRecommendationController::class, 'getTrendingProducts']);
Route::get('/recommendations/points', [GuestRecommendationController::class, 'getPointBasedRecommendations']);
Route::get('/recommendations/top-rated', [GuestRecommendationController::class, 'getTopRatedProducts']);
Route::get('/recommendations/most-reviewed', [GuestRecommendationController::class, 'getMostReviewedProducts']);
Route::get('/recommendations/category/{categoryId}', [GuestRecommendationController::class, 'getCategoryRecommendations']);
Route::get('/recommendations/related/{productId}', [GuestRecommendationController::class, 'getRelatedProducts']);