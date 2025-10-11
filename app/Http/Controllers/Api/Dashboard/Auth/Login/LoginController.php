<?php

namespace App\Http\Controllers\Api\Dashboard\Auth\Login;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Dashboard\Auth\Login\LoginRequest;
use App\Services\Api\Dashboard\Auth\Login\LoginService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class LoginController extends  Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['guest'];
    }

    public function __construct(private LoginService $loginService)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function login(LoginRequest $request)
    {
        return $this->loginService->login($request->validated());
    }
}
