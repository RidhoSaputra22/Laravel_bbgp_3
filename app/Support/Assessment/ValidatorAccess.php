<?php

namespace App\Support\Assessment;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ValidatorAccess
{
    public const ADMIN_ROLES = ['admin', 'superadmin', 'kepala', 'database'];

    public static function isAdminSession(): bool
    {
        return in_array(strtolower(trim((string) session('role'))), self::ADMIN_ROLES, true);
    }

    public static function eligibleUsersQuery(): Builder
    {
        return User::query()
            ->whereRaw('LOWER(TRIM(role)) = ?', ['stakeholder'])
            ->whereHas('guru', function (Builder $query) {
                $query
                    ->whereRaw('LOWER(TRIM(eksternal_jabatan)) = ?', ['stakeholder'])
                    ->whereRaw('LOWER(TRIM(jenis_jabatan)) = ?', ['validator']);
            });
    }

    public static function isEligibleUser(?User $user): bool
    {
        if (! $user || strtolower(trim((string) $user->role)) !== 'stakeholder') {
            return false;
        }

        $guru = $user->relationLoaded('guru') ? $user->guru : $user->guru()->first();

        return $guru
            && strtolower(trim((string) $guru->eksternal_jabatan)) === 'stakeholder'
            && strtolower(trim((string) $guru->jenis_jabatan)) === 'validator';
    }

    public static function currentValidator(): ?User
    {
        $userId = session('user_id');

        if (! $userId) {
            return null;
        }

        $user = User::with('guru')->find((int) $userId);

        return self::isEligibleUser($user) ? $user : null;
    }

    public static function authorizeAdmin(): void
    {
        abort_unless(self::isAdminSession(), 403);
    }

    public static function authorizeValidator(): User
    {
        $user = self::currentValidator();
        abort_unless($user, 403);

        return $user;
    }
}
