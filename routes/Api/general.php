<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\General\Profile\ProfileController;
use App\Http\Controllers\Api\General\Settings\SettingsController;
use App\Http\Controllers\Api\General\Notification\NotificationController;


Route::post('/change-password', [ProfileController::class, 'changePassword']);
Route::post('/request-phone-change', [ProfileController::class, 'requestPhoneChange']);
Route::post('/verify-phone-otp', [ProfileController::class, 'verifyPhoneOtp']);
Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
Route::post('/delete-account', [ProfileController::class, 'deleteAccount']);

Route::post('/change-language', [SettingsController::class, 'changeLanguage']);
Route::post('/profile-data', [ProfileController::class, 'profileData']);
Route::post('/toggle-notification', [SettingsController::class, 'toggleNotification']);
Route::post('/toggle-dark-mode', [SettingsController::class, 'toggleDarkMode']);

Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'list']);
    Route::post('/{id}/read', [NotificationController::class, 'read']); 
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']); 
    Route::delete('/{id}', [NotificationController::class, 'delete']); 
});
