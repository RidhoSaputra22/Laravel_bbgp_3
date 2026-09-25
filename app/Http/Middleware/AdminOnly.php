<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    private const ROLES = [
        'admin',
        'superadmin',
        'kepala',
        'database',
        'kepegawaian',
        'kegiatan',
        'keuangan',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $role = strtolower(trim((string) ($user?->role ?? session('role'))));

        $authenticated = $user !== null || session('cek') === true;

        abort_unless($authenticated && in_array($role, self::ROLES, true), 403);

        return $next($request);
    }
}
