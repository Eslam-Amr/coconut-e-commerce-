<?php

namespace App\Http\Controllers\Api\Dashboard\Auth\Logout;

use App\Http\Controllers\Controller;
use App\Services\Api\Dashboard\Auth\Logout\LogoutService;
use Illuminate\Routing\Controllers\HasMiddleware;

class LogoutController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return ['admin'];
    }

    public function __construct(public LogoutService $logoutService) {}

    /**
     * Handle admin logout
     */
    public function logout()
    {
        return $this->logoutService->logout();
    }
}
