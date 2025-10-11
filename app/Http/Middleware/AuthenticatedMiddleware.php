<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user =  Auth::guard('admin')->user() ?? Auth::guard()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Please login first.',
                'status' => false
            ], 401);
        }

        
        return $next($request);
    }
}
