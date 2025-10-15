<?php

use App\Http\Controllers\Api\App\Client\Auth\Login\LoginController;
use App\Http\Controllers\Api\App\Client\Auth\Logout\LogoutController;
use App\Http\Controllers\Api\App\Client\Auth\Register\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\App\Client\Product\ProductController as ClientProductController;
use App\Http\Controllers\Api\App\client\wallet\WalletController;
use App\Http\Controllers\Api\App\Client\Wishlist\WishlistController;
use App\Http\Controllers\Api\App\Client\Cart\CartController;
use App\Http\Controllers\Api\App\Client\Recommendation\InteractionRecommendationController;




Route::post('/register', [RegisterController::class, 'register']);

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/resend-otp', [RegisterController::class, 'resendOtp']);
Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);

Route::post('/login', [LoginController::class, 'login']);


Route::post('/forget-password', [LoginController::class, 'forgetPassword']);
Route::post('/verify-password-otp', [LoginController::class, 'verifyOtpForgetPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);

Route::post('/logout', [LogoutController::class, 'logout']);

// Authenticated client routes
Route::get('/products/recommendations', [ClientProductController::class, 'recommendations']);

// Wishlist
Route::get('/wishlist', [WishlistController::class, 'index']);
Route::post('/wishlist/{productId}', [WishlistController::class, 'toggleWishlist']);

Route::post('/wallet', [WalletController::class, 'createWallet']);

// Cart
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::post('/cart/increment', [CartController::class, 'increment']);
Route::post('/cart/decrement', [CartController::class, 'decrement']);
Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity']);
Route::delete('/cart/remove', [CartController::class, 'remove']);

// Interaction-based Recommendations
Route::get('/recommendations/interaction', [InteractionRecommendationController::class, 'getRecommendations']);
Route::get('/recommendations/collaborative', [InteractionRecommendationController::class, 'getCollaborativeRecommendations']);
Route::get('/recommendations/trending', [InteractionRecommendationController::class, 'getTrendingProducts']);
Route::get('/recommendations/personalized', [InteractionRecommendationController::class, 'getPersonalizedRecommendations']);
Route::get('/recommendations/category/{categoryId}', [InteractionRecommendationController::class, 'getCategoryRecommendations']);
Route::get('/recommendations/related/{productId}', [InteractionRecommendationController::class, 'getRelatedProducts']);
Route::get('/recommendations/stats', [InteractionRecommendationController::class, 'getUserStats']);
Route::post('/recommendations/record-view', [InteractionRecommendationController::class, 'recordView']);