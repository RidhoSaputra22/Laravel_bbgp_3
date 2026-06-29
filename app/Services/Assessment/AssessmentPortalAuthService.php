<?php

namespace App\Services\Assessment;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AssessmentPortalAuthService
{
    public const SESSION_KEY = 'assessment_portal_auth';

    private const LOGOUT_FLAG = 'assessment_portal_logged_out';

    private const ALLOWED_ROLES = [
        'tenaga pendidik',
        'tenaga kependidikan',
        'stakeholder',
    ];

    public function roleOptions(): array
    {
        return [
            'tenaga pendidik' => 'Tenaga Pendidik',
            'tenaga kependidikan' => 'Tenaga Kependidikan',
            'stakeholder' => 'Stakeholder',
        ];
    }

    public function allowedRoleValues(): array
    {
        return array_keys($this->roleOptions());
    }

    public function authenticate(string $nik, string $password, string $role): ?array
    {
        $normalizedNik = trim($nik);

        $user = User::query()
            ->where('no_ktp', $normalizedNik)
            ->where('role', $role)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        $guru = Guru::query()
            ->where('no_ktp', $user->no_ktp)
            ->first();

        if (! $guru) {
            return null;
        }

        $this->storeSession($user, $guru);

        return [
            'user' => $user,
            'guru' => $guru,
        ];
    }

    public function storeSession(User $user, Guru $guru): void
    {
        session()->forget(self::LOGOUT_FLAG);

        session()->put(self::SESSION_KEY, [
            'user_id' => $user->id,
            'guru_id' => $guru->id,
            'name' => $user->name,
            'no_ktp' => $user->no_ktp,
            'role' => $user->role,
            'logged_in_at' => now()->toIso8601String(),
        ]);
    }

    public function logout(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->put(self::LOGOUT_FLAG, true);
    }

    public function currentGuru(): ?Guru
    {
        $guruId = $this->currentGuruId();

        return $guruId ? Guru::find($guruId) : null;
    }

    public function currentUser(): ?User
    {
        $portalUserId = session(self::SESSION_KEY.'.user_id');

        if ($portalUserId) {
            return User::find((int) $portalUserId);
        }

        $globalUserId = session('user_id');

        if ($globalUserId && $this->currentGuruId()) {
            return User::find((int) $globalUserId);
        }

        return null;
    }

    public function currentGuruId(): ?int
    {
        $portalGuruId = session(self::SESSION_KEY.'.guru_id');

        if ($portalGuruId) {
            return (int) $portalGuruId;
        }

        if (session(self::LOGOUT_FLAG) === true) {
            return null;
        }

        $globalRole = strtolower((string) session('role'));
        $globalGuruId = session('guru_id');

        if ($globalGuruId && in_array($globalRole, self::ALLOWED_ROLES, true)) {
            return (int) $globalGuruId;
        }

        return null;
    }

    public function isAuthenticated(): bool
    {
        return $this->currentGuruId() !== null;
    }
}
