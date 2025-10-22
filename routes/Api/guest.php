<?php

use App\Http\Controllers\Api\App\Guest\Recommendation\OptimizedRecommendationController;
use App\Http\Controllers\Api\App\Guest\Banner\BannerController;
use App\Http\Controllers\Api\App\Guest\Brand\BrandController;
use App\Http\Controllers\Api\App\Guest\Category\CategoryController;
use App\Http\Controllers\Api\App\Guest\Recommendation\GuestRecommendationController;
use App\Http\Controllers\Api\App\Guest\Slider\SliderController;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Api\App\Guest\Product\ProductController;
use App\Http\Controllers\Api\App\Guest\StaticPage\StaticPageController;

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




// // Guest recommendation routes (no authentication required)
// Route::get('/recommendations', [GuestRecommendationController::class, 'getRecommendations']);
// Route::get('/recommendations/trending', [GuestRecommendationController::class, 'getTrendingProducts']);
// Route::get('/recommendations/points', [GuestRecommendationController::class, 'getPointBasedRecommendations']);
// Route::get('/recommendations/top-rated', [GuestRecommendationController::class, 'getTopRatedProducts']);
// Route::get('/recommendations/most-reviewed', [GuestRecommendationController::class, 'getMostReviewedProducts']);
// Route::get('/recommendations/category/{categoryId}', [GuestRecommendationController::class, 'getCategoryRecommendations']);
// Route::get('/recommendations/related/{productId}', [GuestRecommendationController::class, 'getRelatedProducts']);



Route::get('/recommendations', [OptimizedRecommendationController::class, 'getRecommendations']);
Route::get('/recommendations/top-rated', [OptimizedRecommendationController::class, 'getTopRatedProducts']);
Route::get('/recommendations/most-ordered', [OptimizedRecommendationController::class, 'getMostOrderedProducts']);

Route::get('/brands', [BrandController::class, 'index'])->name('guest.brands');
Route::get('/categories', [CategoryController::class, 'index'])->name('guest.categories');
Route::get('/static-pages', [StaticPageController::class, 'index'])->name('guest.static-pages');
Route::get('/static-pages/{title}', [StaticPageController::class, 'show'])->name('guest.static-pages.show');
Route::get('/banners', [BannerController::class, 'index'])->name('guest.banners');
Route::get('/banners/{id}', [BannerController::class, 'show'])->name('guest.banners.show');
Route::get('/sliders', [SliderController::class, 'index'])->name('guest.sliders');
Route::get('/sliders/{id}', [SliderController::class, 'show'])->name('guest.sliders.show');
