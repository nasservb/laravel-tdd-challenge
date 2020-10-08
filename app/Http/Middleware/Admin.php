<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // @TODO implement

        if(!Auth::check() || Auth::user()->is_admin == false )
            return response()->json([], 403);


        return $next($request);
    }
}
