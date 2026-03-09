<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Auth\Contracts\V2\AuthHelpers;

class UserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $permissions)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        $userId = $user->user_id;

        $store = AuthHelpers::getAccessibleStore($userId);
        if (!$store) {
            return redirect('/access-denied');
        }

        if ($store->user_id == $userId) {
            // Store owner has all permissions
            return $next($request);
        }

        // Split pipe-delimited permissions: e.g. "view users|edit user"
        $permissionList = explode('|', $permissions);

        $userpermissions = AuthHelpers::getModelPermissions($userId);

        $hasAny = collect($permissionList)->contains(function ($perm) use ($userpermissions) {
            return $userpermissions->contains($perm);
        });
        
        if (!$hasAny) {
            return redirect('/access-denied');
        }

        return $next($request);
    }
}
