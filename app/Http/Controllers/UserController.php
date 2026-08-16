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
    public function index()
    {
        $users = User::with('profile.sekolah')->paginate(10);
        return view('users.index', compact('users'));
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