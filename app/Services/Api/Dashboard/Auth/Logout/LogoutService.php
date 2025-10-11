<?php

namespace App\Services\Api\Dashboard\Auth\Logout;

use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class LogoutService
{
    use ApiResponseTrait;

    /**
     * Handle admin logout
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            // Logout the admin
            Auth::guard('admin')->logout();

            return $this->successNotDataResponse(
                __('messages.auth.logout.success')
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                __('messages.auth.logout.failed'),
                500
            );
        }
    }
}
