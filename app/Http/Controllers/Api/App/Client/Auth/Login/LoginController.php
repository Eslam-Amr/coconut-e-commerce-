<?php

namespace App\Http\Controllers\Api\App\Client\Auth\Login;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\Client\Auth\Login\ForgetPasswordRequest;
use App\Http\Requests\Api\App\Client\Auth\Login\LoginRequest;
use App\Http\Requests\Api\App\Client\Auth\Login\ResetPasswordRequest;
use App\Http\Requests\Api\App\Client\Auth\Login\VerifyOtpRequest;
use App\Services\Api\App\Client\Auth\Login\LoginService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public static function middleware(): array
    {
        return ['guest'];
    }
    public function __construct(public LoginService $loginService) {}

    public function login(LoginRequest $request)
    {
        return $this->loginService->login($request->validated());
    }
    public function forgetPassword(ForgetPasswordRequest $request)
    {
        return $this->loginService->forgetPassword($request->validated());
    }
    
    public function verifyOtpForgetPassword(VerifyOtpRequest $request)
    {
        return $this->loginService->verifyOtpForgetPassword($request->validated());
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->loginService->resetPassword($request->validated());
    }
}
