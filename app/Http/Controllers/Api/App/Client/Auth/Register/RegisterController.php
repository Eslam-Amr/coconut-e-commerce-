<?php

namespace App\Http\Controllers\Api\App\Client\Auth\Register;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\Client\Auth\Register\RegisterRequest;
use App\Http\Requests\Api\App\Client\Auth\Register\ResendOtpRequest;
use App\Http\Requests\Api\App\Client\Auth\Register\VerifyOtpRequest;
use App\Services\Api\App\Client\Auth\Register\RegisterService;
use Illuminate\Routing\Controllers\HasMiddleware;

class RegisterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['guest'];
    }
    public function __construct(public RegisterService $registerService) {}
  

    public function register(RegisterRequest $request)
    {
        return $this->registerService->register($request->validated());
    }
    public function resendOtp(ResendOtpRequest $request)
    {
        return $this->registerService->resendOtp($request->validated());
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        return $this->registerService->verifyOtp($request->validated());
    }
}
