<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Services\Assessment\AssessmentPortalAuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function __construct(
        private readonly AssessmentPortalAuthService $authService
    ) {}

    public function showLoginForm()
    {
        if ($this->authService->isAuthenticated()) {
            return redirect()->route('assessment.portal.dashboard');
        }

        return view('assessment.auth.login', [
            'menu' => 'assessment-portal',
            'roleOptions' => $this->authService->roleOptions(),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate(
            [
                'nik' => 'required|string|max:50',
                'password' => 'required|string',
                'role' => [
                    'required',
                    'string',
                    Rule::in($this->authService->allowedRoleValues()),
                ],
            ],
            [
                'nik.required' => 'NIK wajib diisi.',
                'password.required' => 'Password wajib diisi.',
                'role.required' => 'Peran peserta wajib dipilih.',
                'role.in' => 'Peran peserta tidak valid.',
            ]
        );

        $authenticated = $this->authService->authenticate(
            $validated['nik'],
            $validated['password'],
            $validated['role']
        );

        if (! $authenticated) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'auth' => 'NIK, password, atau peran peserta assessment tidak sesuai.',
                ]);
        }

        return redirect()
            ->route('assessment.portal.dashboard')
            ->with('assessment_portal_success', 'Login assessment berhasil. Selamat mengerjakan.');
    }

    public function logout()
    {
        $this->authService->logout();

        return redirect()
            ->route('assessment.portal.auth')
            ->with('assessment_portal_success', 'Anda berhasil keluar dari portal assessment.');
    }
}
