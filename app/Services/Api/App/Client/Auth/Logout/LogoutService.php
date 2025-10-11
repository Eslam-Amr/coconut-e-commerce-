<?php

namespace App\Services\Api\App\Client\Auth\Logout;

use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class LogoutService
{
    use ApiResponseTrait;

    /**
     * Handle user logout
     *
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            // Logout the user
            Auth::logout();

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
