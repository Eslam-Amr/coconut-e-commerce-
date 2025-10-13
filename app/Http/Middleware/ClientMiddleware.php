<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login first.',
                'status' => false
            ], 401);
        }

        $user = Auth::user();

        // Check if user is a client (not admin)
        if (!$user || $user->user_type === 'admin') {
            return response()->json([
                'message' => 'Access denied. Client access only.',
                'status' => false
            ], 403);
        }
        if (!$user->active) {
            Auth::guard()->logout();

            return response()->json([
                'message' => __("messages.login.inactive_account"),
                'status' => false
            ], 403);
        }

        return $next($request);
    }
}
