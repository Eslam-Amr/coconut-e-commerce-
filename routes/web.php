<?php

use App\Http\Controllers\Api\App\Client\Payment\PaymentController;
use App\Http\Controllers\Api\App\Client\wallet\WalletPaymentController;
use App\Http\Controllers\Api\App\Client\Order\OrderPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});





Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');

// Wallet payment routes
Route::get('/wallet/payment/success', [WalletPaymentController::class, 'success'])->name('wallet.payment.success');
Route::get('/wallet/payment/failed', [WalletPaymentController::class, 'failed'])->name('wallet.payment.failed');

// Order payment routes
Route::get('/order/payment/success', [OrderPaymentController::class, 'success'])->name('order.payment.success');
Route::get('/order/payment/failed', [OrderPaymentController::class, 'failed'])->name('order.payment.failed');