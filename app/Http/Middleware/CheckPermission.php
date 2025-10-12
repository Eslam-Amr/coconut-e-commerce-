<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403, 'Unauthorized - Authentication required.');
        }

        $user = Auth::guard('admin')->user();
        // dd($permissions,$user);

        foreach ($permissions as $permission) {
            // Check if permission is explicitly revoked
            $directPermission = $user->permissions()->select('granted')
                ->where('name', $permission)
                // ->where('granted', false)
                ->first();
            // dd($directPermission->granted);
            // If explicitly revoked, deny access
            if ($directPermission && !$directPermission->granted) {
                // if ($directPermission && !$directPermission->pivot->granted) {
                abort(403, "Unauthorized - Permission '{$permission}' has been revoked from your account.");
            } else if ($directPermission && $directPermission->granted) {
                // if ($directPermission && !$directPermission->pivot->granted) {
                return $next($request);
            }

            // Check if user has permission via their roles (not direct grant)
            $hasViaRole = $user->roles()->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission);
            })->exists();
// dd($hasViaRole);
            if ($hasViaRole) {
                return $next($request);
            }

            // Optionally: Also allow direct grants
            // Uncomment if you want to allow directly granted permissions too
            // if ($directPermission && $directPermission->pivot->granted) {
            //     return $next($request);
            // }
        }

        abort(403, 'Unauthorized - Required permission: ' . implode(' or ', $permissions));
    }
}
