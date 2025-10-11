<?php

namespace App\Http\Controllers\Api\App\Client\Auth\Logout;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Client\Auth\Logout\LogoutService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LogoutController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['client'];
    }

    public function __construct(public LogoutService $logoutService) {}

    public function logout()
    {
        return $this->logoutService->logout();
    }
}
