<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class StoreAccess {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $userId="") {
        // checking if logged in user is super admin or not
        $roleId = 2;
        if (!empty($userId)) {
           
            $userRole = getUserRolesId($roleId, $userId);
        } else {
            $userRole = getUserRolesId($roleId);
          //  echo '$userRole: <pre>' .print_r($userRole,true). '</pre>'; 
            // die($userRole);
        }
        if (is_array($userRole)) {
            if ($userRole['role_status'] == '1') {
                return $next($request);
            } else {
                return redirect('/admin/deactivate');
            }
        } else if ($userRole == 0) {
            // for store role is 2
            $roleId = 2;
            if (!empty($userId)) {
                $userRole = getUserRolesId($roleId, $userId);
            } else {
                $userRole = getUserRolesId($roleId);
            }
            if (is_array($userRole)) {
                if ($userRole['role_status'] == '1') {
                    return $next($request);
                } else {
                    return redirect('/store/deactivate');
                }
            } else if ($userRole == 0) {
                //for store has no right is 0
                return redirect('/access-denied');
            } else if ($userRole == 2) {
//            for non login 2
                return redirect('/login');
            }
        } else if ($userRole == 2) {
//            for non login 2
            return redirect('/login');
        }
              
        return redirect('/login');
    }

}
