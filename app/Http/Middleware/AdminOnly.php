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

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();
        $role = strtolower(trim((string) ($user?->role ?? session('role'))));
        $allowedRoles = $roles === []
            ? self::ROLES
            : array_map(static fn (string $value): string => strtolower(trim($value)), $roles);

        $authenticated = $user !== null || session('cek') === true;

        abort_unless($authenticated && in_array($role, $allowedRoles, true), 403);

        return $next($request);
    }
}
