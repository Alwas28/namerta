<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $profile = DB::table('profile')
            ->leftJoin('sekolah', 'profile.id_sekolah', '=', 'sekolah.id_sekolah')
            ->where('profile.id_user', $user->id_user)
            ->select(
                'profile.*',
                'sekolah.nama_sekolah',
                'sekolah.alamat as alamat_sekolah'
            )
            ->first();
        
        return view('profile.index', compact('user', 'profile'));
    }
    
    public function edit()
    {
        $user = Auth::user();
        
        $profile = DB::table('profile')
            ->where('id_user', $user->id_user)
            ->first();
        
        $sekolahList = DB::table('sekolah')
            ->orderBy('nama_sekolah')
            ->get();
        
        return view('profile.edit', compact('user', 'profile', 'sekolahList'));
    }
    
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip_nis' => 'nullable|string|max:30',
            'id_sekolah' => 'nullable|exists:sekolah,id_sekolah',
            'alamat' => 'nullable|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'agama' => 'nullable|in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')
            ]
        ]);
        
        try {
            DB::beginTransaction();
            
            // Update username di tabel users
            DB::table('users')
                ->where('id_user', $user->id_user)
                ->update([
                    'username' => $request->username,
                    'updated_at' => now()
                ]);
            
            // Update profile
            $profileData = [
                'nama' => $request->nama,
                'nip_nis' => $request->nip_nis,
                'id_sekolah' => $request->id_sekolah,
                'alamat' => $request->alamat,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'agama' => $request->agama,
                'updated_at' => now()
            ];
            
            $profileExists = DB::table('profile')
                ->where('id_user', $user->id_user)
                ->exists();
            
            if ($profileExists) {
                DB::table('profile')
                    ->where('id_user', $user->id_user)
                    ->update($profileData);
            } else {
                $profileData['id_user'] = $user->id_user;
                $profileData['created_at'] = now();
                DB::table('profile')->insert($profileData);
            }
            
            DB::commit();
            
            return redirect()->route('profile.index')
                ->with('success', 'Profile berhasil diperbarui');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui profile: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        
        $user = Auth::user();
        
        // Cek password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Password lama tidak sesuai');
        }
        
        // Update password
        DB::table('users')
            ->where('id_user', $user->id_user)
            ->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now()
            ]);
        
        return redirect()->back()
            ->with('success', 'Password berhasil diubah');
    }
    
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        
        $user = Auth::user();
        
        try {
            if ($request->hasFile('photo')) {
                // Get old photo
                $profile = DB::table('profile')
                    ->where('id_user', $user->id_user)
                    ->first();
                
                // Delete old photo if exists
                if ($profile && $profile->foto && Storage::exists('public/' . $profile->foto)) {
                    Storage::delete('public/' . $profile->foto);
                }
                
                // Upload new photo
                $file = $request->file('photo');
                $filename = 'profile_' . $user->id_user . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('public/photos', $filename);
                $photoPath = str_replace('public/', '', $path);
                
                // Update database
                $profileExists = DB::table('profile')
                    ->where('id_user', $user->id_user)
                    ->exists();
                
                if ($profileExists) {
                    DB::table('profile')
                        ->where('id_user', $user->id_user)
                        ->update([
                            'foto' => $photoPath,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('profile')->insert([
                        'id_user' => $user->id_user,
                        'foto' => $photoPath,
                        'nama' => $user->username,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                return redirect()->back()
                    ->with('success', 'Foto profile berhasil diupload');
            }
            
            return redirect()->back()
                ->with('error', 'Tidak ada file yang diupload');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal upload foto: ' . $e->getMessage());
        }
    }
}