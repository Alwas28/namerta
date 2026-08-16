<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
   
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('username', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }
            return back()->withErrors(['email' => 'Email atau password salah']);
        }

        if ($user->aktif !== 'Y') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan'
                ], 401);
            }
            return back()->withErrors(['email' => 'Akun telah dinonaktifkan']);
        }

        /// Login berhasil
        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();

        // Tentukan redirect URL
        if ($user->is_admin === 'Y') {
            $redirect = '/dashboard';
        } else {
            $profile = $user->profile;
            switch ($profile->status) {
                case 'siswa':
                    $redirect = '/dashboard_siswa';
                    break;
                case 'guru':
                    $redirect = '/dashboard_guru';
                    break;
                case 'tendik':
                    $redirect = '/dashboard_tendik';
                    break;
                default:
                    $redirect = '/'; // fallback jika role tidak dikenali
                    break;
            }
        }

        // Jika request dari AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => $redirect
            ]);
        }

        // Jika bukan AJAX, redirect normal
        return redirect($redirect)->with('success', 'Berhasil login');

    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Check if request is AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Berhasil logout',
                // 'redirect' => '/'
            ]);
        }
        
        // Normal request - redirect
        return redirect('/')->with('success', 'Berhasil logout');
    }
}