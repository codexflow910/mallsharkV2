<?php

namespace App\Http\Middleware;

use Closure;

class LoggedIn {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $userId = "") {
        $roleId = 4;
        // dd($roleId);
        $userRole = getUserRolesId($roleId);
        if (!empty($userId)) {   
          $userRole = getUserRolesId($roleId,$userId);  
          if ($userRole['role_status'] == '1') {
               return $next($request);
          }
        } else if (getCurrentUserId() && $userRole['role_status'] == '1') {
            return $next($request);
        }
        return redirect('/login');
    }

}
