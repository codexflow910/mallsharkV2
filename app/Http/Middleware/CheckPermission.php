<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle($request, Closure $next, $permissions)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Split pipe-delimited permissions: e.g. "view users|edit user"
        $permissionList = explode('|', $permissions);

        if (isset($user->all_permissions)) {
            // If CheckRole middleware already attached all_permissions
            $hasAny = collect($permissionList)->contains(function ($perm) use ($user) {
                return $user->all_permissions->contains('name', $perm);
            });
        } else {
            // Fallback: check each permission individually
            $hasAny = collect($permissionList)->contains(function ($perm) use ($user) {
                return $user->hasPermission($perm);
            });
        }

        if (!$hasAny) {
            return redirect('/access-denied');
        }

        return $next($request);
    }
}