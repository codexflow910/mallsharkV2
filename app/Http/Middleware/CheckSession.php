<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CheckSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            //1719306376
            // Check if the user session has expired
            $user = auth()->user();
            if ($request->session()->has('last_activity')) {
                $lastActivity = $request->session()->get('last_activity');
                $sessionLifetime = config('session.lifetime') * 60; // Convert to seconds
                if (time() - $lastActivity < $sessionLifetime) {
                    
                    $user->token()->revoke();
                    return response()->json(['error' => 'Session expired. Please login again.'], 401);
                }
            }
            $request->session()->put('last_activity', time());
        } else {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        return $next($request);
    }
}
