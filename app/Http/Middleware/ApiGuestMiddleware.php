<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiGuestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via any guard
        if (auth()->check() || auth('admin')->check()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.already_authenticated'),
                'details' => [
                    'error_type' => 'already_authenticated',
                    'message' => 'You are already logged in. Please logout first to register a new account.'
                ]
            ], 403);
        }

        return $next($request);
    }
}
