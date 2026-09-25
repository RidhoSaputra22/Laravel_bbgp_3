<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AkunController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAccountAdmin();

        // $data = Admin::orderByDesc('id')->get();
        $data = Admin::select('id', 'name', 'username', 'role')
            ->orderByDesc('id')
            ->get();


        return view('pages.admin.akun.index', [
            'menu' => 'akun',
            'datas' => $data
        ]);
    }

    public function getAkunData(Request $request)
    {
        $this->authorizeAccountAdmin();

        $query = Admin::select('id', 'name', 'username', 'role');

        // DataTables server-side processing
        if ($request->has('draw')) {
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            }

            $total = Admin::count();
            $filtered = $query->count();
            $data = $query->orderByDesc('id')
                ->skip($start)
                ->take($length)
                ->get();

            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data
            ]);
        }

        // Simple AJAX request
        return response()->json($query->orderByDesc('id')->get());
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        $this->authorizeAccountAdmin();

        $validated = $r->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100',
            'no_ktp' => 'nullable|string|max:50',
            'role' => ['required', Rule::in($this->managedRoles())],
            'password' => 'required|string|min:8|max:255',
        ]);
        $this->guardRoleManagement(null, $validated['role']);

        $usernameExists = Admin::where('username', $validated['username'])->exists()
            || User::where('username', $validated['username'])->exists();
        if (! $usernameExists) {
            $validated['password'] = Hash::make($validated['password']);
            Admin::create($validated);
            User::create($validated);

            return redirect()->route('akun.index')->with('message', 'store');
        } else {
            return redirect()->route('akun.index')->with('message', 'username sudah ada');
        }
    }

    /**
     * Display the specified resource.
     */

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorizeAccountAdmin();

        $data = Admin::find($id);

        return view('pages.admin.akun.edit', ['menu' => 'akun', 'datas' => $data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $this->authorizeAccountAdmin();

        $r = $request->validate([
            'id' => 'required|integer|exists:admins,id',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100',
            'no_ktp' => 'nullable|string|max:50',
            'role' => ['required', Rule::in($this->managedRoles())],
            'password' => 'nullable|string|min:8|max:255',
        ]);
        $admin = Admin::find($r['id']);

        if (!$admin) {
            return redirect()->route('akun.index')->with('message', 'Data tidak ditemukan');
        }
        $this->guardRoleManagement($admin->role, $r['role']);

        // Simpan username lama untuk mencari User yang terkait
        $oldUsername = $admin->username;

        // Hanya update password jika diisi
        if ($request->filled('password')) {
            $r['password'] = Hash::make($r['password']);
        } else {
            // Jika password kosong, hapus dari array agar tidak ikut diupdate
            unset($r['password']);
        }

        // Update Admin
        $admin->update($r);

        // Update User yang memiliki username yang sama
        $user = User::where('username', $oldUsername)->first();
        if ($user) {
            $user->update($r);
        }

        return redirect()->route('akun.index')->with('message', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorizeAccountAdmin();

        $admin = Admin::find($id);
        if ($admin) {
            $this->guardRoleManagement($admin->role);
            abort_if((string) $admin->username === (string) Auth::user()?->username, 422, 'Akun yang sedang digunakan tidak dapat dihapus.');

            $user = User::where('username', $admin->username)->first();
            if ($user) {
                $user->delete();
            }
            $admin->delete();
        }
        return response()->json($admin);
    }



    public function regis(Request $r)
    {
        $this->authorizeAccountAdmin();

        $validated = $r->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100',
            'no_ktp' => 'nullable|string|max:50',
            'role' => 'required|string|max:100',
            'password' => 'nullable|string|max:255',
        ]);

        $role = match (strtolower(trim($validated['role']))) {
            'tenaga pendidik' => 'tenaga pendidik',
            'tenaga kependidikan' => 'tenaga kependidikan',
            'stakeholder' => 'stakeholder',
            'pegawai' => 'pegawai',
            default => null,
        };

        abort_unless($role !== null, 422, 'Role akun otomatis tidak valid.');

        $reg = [
            'name' => $validated['name'],
            'username' => strtolower(str_replace(' ', '', $validated['username'])),
            'no_ktp' => (string) ($validated['no_ktp'] ?? ''),
            'role' => $role,
        ];
        $passwordPlain = Str::random(16);
        $reg['password'] = Hash::make($passwordPlain);
        Admin::create($reg);
        User::create($reg);

        return response()->json([
            'status' => true,
            'data' => collect($reg)->except('password')->all(),
            'credentials' => [
                'username' => $reg['username'],
                'password' => $passwordPlain,
            ],
        ]);
        // return redirect()->route('akun.index')->with('message', 'store');
    }

    private function managedRoles(): array
    {
        return [
            'admin',
            'pegawai',
            'tenaga pendidik',
            'kepala',
            'superadmin',
            'tenaga kependidikan',
            'stakeholder',
            'kepegawaian',
            'keuangan',
            'kegiatan',
            'database',
        ];
    }

    private function authorizeAccountAdmin(): void
    {
        abort_unless(in_array(strtolower(trim((string) Auth::user()?->role)), [
            'admin',
            'superadmin',
            'kepala',
            'database',
        ], true), 403);
    }

    private function guardRoleManagement(?string $currentRole, ?string $newRole = null): void
    {
        $isSuperAdmin = strtolower(trim((string) Auth::user()?->role)) === 'superadmin';
        $requiresSuperAdmin = in_array($currentRole, ['superadmin'], true)
            || in_array($newRole, ['superadmin'], true);

        abort_unless(!$requiresSuperAdmin || $isSuperAdmin, 403);
    }
}
