<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Unauthorized - Authentication required.');
        }

        foreach ($roles as $role) {
            if (Auth::guard('admin')->user()->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized - Required role: ' . implode(' or ', $roles));
    }
}
