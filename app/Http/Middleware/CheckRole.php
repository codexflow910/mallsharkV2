<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // Get permissions from roles (with eager loading)
        $rolePermissions = $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten();
        
        // Get directly assigned permissions
        $directPermissions = $user->permissions;

        // Merge both and make them unique by name or id
        $allPermissions = $rolePermissions
            ->merge($directPermissions)
            ->unique('permission_id')
            ->values();
        
        // Attach for current request lifecycle
        $user->setRelation('all_permissions', $allPermissions);

        return $next($request);
    }
}
