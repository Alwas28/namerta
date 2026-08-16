<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Cek role dan redirect ke dashboard yang sesuai
        if ($user->is_admin === 'Y') {
            return view('dashboard'); // atau view('admin.dashboard')
        }
        
        // Get profile untuk cek role non-admin
        $profile = $user->profile;
        
        if ($profile) {
            switch ($profile->id_status) {
                case 'siswa':
                    return view('dashboard.siswa');
                    break;
                case 'guru':
                    return view('dashboard.guru');
                    break;
                case 'tendik':
                    return view('dashboard.tendik');
                    break;
            }
        }
        
        // Default dashboard jika role tidak ditemukan
        return view('dashboard');
    }
}