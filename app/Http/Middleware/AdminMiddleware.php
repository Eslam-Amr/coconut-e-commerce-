<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd("");
        if (!Auth::guard('admin')->check()) {
            return response()->json([
                'message' => 'Unauthorized. Please login first.',
                'status' => false
            ], 401);
        }

        $user =  Auth::guard('admin')->user();
        
        // Check if user is an admin
        if (!$user || $user->user_type !== 'admin') {
            return response()->json([
                'message' => 'Access denied. Admin privileges required.',
                'status' => false
            ], 403);
        }

        return $next($request);
    }
}
