<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExternalOnly
{
    private const ROLES = [
        'tenaga pendidik',
        'tenaga kependidikan',
        'stakeholder',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $role = strtolower(trim((string) (Auth::user()?->role ?? session('role'))));

        abort_unless(
            session()->has('guru_id') && in_array($role, self::ROLES, true),
            403
        );

        return $next($request);
    }
}
