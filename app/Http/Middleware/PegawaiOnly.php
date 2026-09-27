<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PegawaiOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = strtolower(trim((string) (Auth::user()?->role ?? session('role'))));

        abort_unless($role === 'pegawai' && session()->has('no_ktp'), 403);

        return $next($request);
    }
}
