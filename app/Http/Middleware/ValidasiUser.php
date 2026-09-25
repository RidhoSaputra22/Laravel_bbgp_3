<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidasiUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // return $next($request);
        // Legacy assessment/admin flows use the server-side cek marker;
        // critical admin routes still require AdminOnly/Auth.
        if (Auth::check() || session('cek') === true) {
            return $next($request);
        } else {
            return redirect()->route('login')->with('message', 'need login');
        }
    }
}
