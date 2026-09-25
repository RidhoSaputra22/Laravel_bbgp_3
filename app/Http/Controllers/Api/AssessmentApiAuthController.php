<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AssessmentApiAuthController extends Controller
{
    public function token(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()
            ->where(function ($query) use ($credentials) {
                $query->where('username', $credentials['username'])
                    ->orWhere('no_ktp', $credentials['username']);
            })
            ->whereIn('role', ['tenaga pendidik', 'tenaga kependidikan', 'stakeholder'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Kredensial tidak valid.',
            ], 401);
        }

        $token = $user->createToken(
            $credentials['device_name'] ?? 'assessment-api',
            ['assessment:read']
        );

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at?->toISOString(),
            'abilities' => ['assessment:read'],
            'user' => $user->only(['id', 'name', 'username', 'role']),
        ]);
    }

    public function revoke(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Token berhasil dicabut.']);
    }
}
