<?php

use App\Http\Controllers\Api\App\Client\Auth\Login\LoginController;
use App\Http\Controllers\Api\App\Client\Auth\Logout\LogoutController;
use App\Http\Controllers\Api\App\Client\Auth\Register\RegisterController;
use Illuminate\Support\Facades\Route;




Route::post('/register', [RegisterController::class, 'register']);

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/resend-otp', [RegisterController::class, 'resendOtp']);
Route::post('/verify-otp', [RegisterController::class, 'verifyOtp']);

Route::post('/login', [LoginController::class, 'login']);


Route::post('/forget-password', [LoginController::class, 'forgetPassword']);
Route::post('/verify-password-otp', [LoginController::class, 'verifyOtpForgetPassword']);
Route::post('/reset-password', [LoginController::class, 'resetPassword']);

Route::post('/logout', [LogoutController::class, 'logout']);
