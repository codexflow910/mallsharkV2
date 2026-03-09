<?php

namespace App\Http\Middleware;

use Closure;
use Session;
class SupportAccess {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
//        for Support role  is 5
        $roleId = 5;
        $userRole = getUserRolesId($roleId);
        if (Session::has('userData')) {
            $userid = Session::get('userData')['user_id'];
            $userData = getUserById($userid);
            if ($userData->status == '2') {
                return redirect('/login');
            }
        }

        if (is_array($userRole)) {
            if ($userRole['role_status'] == '1') {
                return $next($request);
            } else {
                return redirect('/admin/deactivate');
            }
        } else if ($userRole == 0) {
            //for store has no right is 0
            return redirect('/access-denied');
        } else if ($userRole == 2) {
//            for non login 2
            return redirect('/login');
        }
    }

}
