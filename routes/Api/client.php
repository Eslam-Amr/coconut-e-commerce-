<?php

use App\Http\Controllers\Api\App\Client\Auth\Login\LoginController;
use App\Http\Controllers\Api\App\Client\Auth\Logout\LogoutController;
use App\Http\Controllers\Api\App\Client\Auth\Register\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\App\Client\Product\ProductController as ClientProductController;
use App\Http\Controllers\Api\App\client\wallet\WalletController;
use App\Http\Controllers\Api\App\Client\Wishlist\WishlistController;
use App\Http\Controllers\Api\App\Client\Cart\CartController;
use App\Http\Controllers\Api\App\Client\Order\OrderController;
use App\Http\Controllers\Api\App\Client\Payment\PaymentController;
use App\Http\Controllers\Api\App\Client\Product\ProductReviewController;
use App\Http\Controllers\Api\App\Client\Recommendation\OptimizedRecommendationController;
use App\Http\Controllers\Api\App\Client\Recommendation\InteractionRecommendationController;
use App\Http\Controllers\Api\App\Client\SearchHistory\SearchHistoryController;




// Route::post('/register', [RegisterController::class, 'register']);

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

// Wallet routes
Route::post('/wallet', [WalletController::class, 'createWallet']);
Route::post('/wallet/charge', [WalletController::class, 'chargeWallet']);
Route::get('/wallet/info', [WalletController::class, 'getWalletInfo']);
Route::get('/wallet/transaction/{transactionId}', [WalletController::class, 'getTransactionDetails']);

// Cart
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/add', [CartController::class, 'add']);
Route::post('/cart/increment', [CartController::class, 'increment']);
Route::post('/cart/decrement', [CartController::class, 'decrement']);
Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity']);
Route::delete('/cart/remove', [CartController::class, 'remove']);

// Cart calculation route
Route::get('/cart/calculate-total', [CartController::class, 'calculateTotal']);

// Orders
Route::post('/orders/confirm', [OrderController::class, 'confirmOrder']);
// Route::post('/orders/{orderId}/cancel', [OrderController::class, 'cancelOrder']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{orderId}', [OrderController::class, 'show']);

// Product Reviews
Route::post('/reviews', [ProductReviewController::class, 'store']);
Route::get('/products/{productId}/reviews', [ProductReviewController::class, 'getProductReviews']);
Route::get('/reviews/my-reviews', [ProductReviewController::class, 'getUserReviews']);
Route::put('/reviews/{reviewId}', [ProductReviewController::class, 'update']);
Route::delete('/reviews/{reviewId}', [ProductReviewController::class, 'destroy']);
Route::get('/products/{productId}/can-review', [ProductReviewController::class, 'canReview']);




// Interaction-based Recommendations
// Route::get('/recommendations/interaction', [InteractionRecommendationController::class, 'getRecommendations']);
// Route::get('/recommendations/collaborative', [InteractionRecommendationController::class, 'getCollaborativeRecommendations']);
// Route::get('/recommendations/trending', [InteractionRecommendationController::class, 'getTrendingProducts']);
// Route::get('/recommendations/personalized', [InteractionRecommendationController::class, 'getPersonalizedRecommendations']);
// Route::get('/recommendations/points', [InteractionRecommendationController::class, 'getPointBasedRecommendations']);
// Route::get('/recommendations/top-rated', [InteractionRecommendationController::class, 'getTopRatedProducts']);
// Route::get('/recommendations/most-reviewed', [InteractionRecommendationController::class, 'getMostReviewedProducts']);
// Route::get('/recommendations/category/{categoryId}', [InteractionRecommendationController::class, 'getCategoryRecommendations']);
// Route::get('/recommendations/related/{productId}', [InteractionRecommendationController::class, 'getRelatedProducts']);
// Route::get('/recommendations/stats', [InteractionRecommendationController::class, 'getUserStats']);
// Route::post('/recommendations/record-view', [InteractionRecommendationController::class, 'recordView']);

// Optimized Recommendations
// Route::get('/recommendations', [OptimizedRecommendationController::class, 'getRecommendations']);
// Route::get('/recommendations/top-rated', [OptimizedRecommendationController::class, 'getTopRatedProducts']);
// Route::get('/recommendations/most-ordered', [OptimizedRecommendationController::class, 'getMostOrderedProducts']);





Route::post('/payment/process', [PaymentController::class, 'paymentProcess']);
Route::match(['GET','POST'],'/payment/callback', [PaymentController::class, 'callBack']);

// Search History routes
Route::get('/search-history', [SearchHistoryController::class, 'index']);
Route::delete('/search-history', [SearchHistoryController::class, 'delete']); // Delete all
Route::delete('/search-history/{id}', [SearchHistoryController::class, 'delete']); // Delete specific
