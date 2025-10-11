<?php

namespace App\Services\Api\Dashboard\Auth\Login;

use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;

class LoginService
{
    use ApiResponseTrait;
    /**
     * Handle the incoming request.
     */
    public function login($credentials)
    {
        if (!$token = auth('admin')->attempt($credentials))
            return response()->json(['error' => 'Unauthorized'], 401);
        return $this->createNewToken($token);
    }

    private function createNewToken($token)
    {
        // $user = Auth::user('admin');
        $user = auth('admin')->user();

        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => $user
        ], __('messages.login.successful'));
    }
}
