<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            // save last_access
            $user->last_access_at = date('Y-m-d H:i:s');
            $user->save();

            // user logout
            if ($user->logout || !$user->active) {
                // Not for the next time!
                // Maybe a `unmarkForLogout()` method is appropriate here.
                $user->logout = false;
                $user->save();
                // Log her out
                Auth::logout();
                return redirect()->route('login');
            }
        }

		return $next($request);
	}
}
