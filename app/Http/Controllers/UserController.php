<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Pemetaan status profil -> id_role pada tabel `roles`.
     */
    private const ROLE_MAP = [
        'guru'   => 3,
        'siswa'  => 4,
        'tendik' => 5,
    ];

    private const ROLE_SUPER_ADMIN = 1;

    /**
     * Samakan isi tabel user_roles dengan status profil + flag is_admin user.
     * Dipakai saat membuat / mengubah user supaya menu & pengecekan role
     * (Auth::user()->hasRole(...)) tidak pernah kosong untuk user baru.
     */
    private function syncUserRole($userId, string $status, string $isAdmin): void
    {
        $roleIds = [];

        if (isset(self::ROLE_MAP[$status])) {
            $roleIds[] = self::ROLE_MAP[$status];
        }

        if ($isAdmin === 'Y') {
            $roleIds[] = self::ROLE_SUPER_ADMIN;
        }

        // Delete-then-insert supaya selalu sinkron dan tidak ada baris ganda.
        DB::table('user_roles')->where('id_user', $userId)->delete();

        foreach (array_unique($roleIds) as $roleId) {
            DB::table('user_roles')->insert([
                'id_user'    => $userId,
                'id_role'    => $roleId,
                'created_at' => now(),
            ]);
        }
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $users = User::with('profile.sekolah')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($p) use ($search) {
                            $p->where('nama', 'like', "%{$search}%")
                                ->orWhere('id_status', 'like', "%{$search}%")
                                ->orWhereHas('sekolah', function ($s) use ($search) {
                                    $s->where('nama_sekolah', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        $sekolah = Sekolah::all();
        return view('users.create', compact('sekolah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|email|unique:users,username',
            'password' => 'required|min:6',
            'nama' => 'required|string|max:255',
            'id_sekolah' => 'required|exists:sekolah,id_sekolah',
            'id_status' => 'required|in:siswa,guru,tendik',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'aktif' => $request->aktif ?? 'Y',
                'is_admin' => $request->is_admin ?? 'N'
            ]);

            // Create profile
            Profile::create([
                'nama' => $request->nama,
                'id_user' => $user->id_user,
                'id_sekolah' => $request->id_sekolah,
                'alamat' => $request->alamat,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'status' => $request->id_status,
                'id_status' => $request->id_status
            ]);

            // Assign role sesuai status supaya user langsung dikenali sistem
            $this->syncUserRole($user->id_user, $request->id_status, $request->is_admin ?? 'N');

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menambahkan user')->withInput();
        }
    }

    public function edit($id)
    {
        $user = User::with('profile')->findOrFail($id);
        $sekolah = Sekolah::all();
        return view('users.edit', compact('user', 'sekolah'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'username' => 'required|email|unique:users,username,'.$id.',id_user',
            'nama' => 'required|string|max:255',
            'id_sekolah' => 'required|exists:sekolah,id_sekolah',
            'id_status' => 'required|in:siswa,guru,tendik',
            'alamat' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update user
            $user->update([
                'username' => $request->username,
                'aktif' => $request->aktif ?? 'Y',
                'is_admin' => $request->is_admin ?? 'N'
            ]);

            // Update password if provided
            if ($request->filled('password')) {
                $user->update(['password' => Hash::make($request->password)]);
            }

            // Update profile
            $user->profile->update([
                'nama' => $request->nama,
                'id_sekolah' => $request->id_sekolah,
                'alamat' => $request->alamat,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'status' => $request->id_status,
                'id_status' => $request->id_status
            ]);

            // Jaga tabel user_roles tetap sinkron dengan status & is_admin terbaru
            $this->syncUserRole($user->id_user, $request->id_status, $request->is_admin ?? 'N');

            DB::commit();
            return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal mengupdate user')->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // Delete profile first
            if ($user->profile) {
                $user->profile->delete();
            }

            // Bersihkan role user
            DB::table('user_roles')->where('id_user', $id)->delete();

            // Then delete user
            $user->delete();
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user'
            ], 500);
        }
    }
}