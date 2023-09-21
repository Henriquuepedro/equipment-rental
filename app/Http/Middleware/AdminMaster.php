<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AdminMaster
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

        if ($user && !Gate::allows('admin-master')) {
            return redirect(RouteServiceProvider::HOME);
        } elseif (!$user) {
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
	}
}
