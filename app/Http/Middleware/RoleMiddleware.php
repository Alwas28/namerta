<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Silakan login terlebih dahulu');
        }

        $user = Auth::user();
        
        // Check if user is active
        if ($user->aktif !== 'Y') {
            Auth::logout();
            return redirect('/')->with('error', 'Akun Anda telah dinonaktifkan');
        }

        // Check role
        $userRole = $user->getRole();
        
        // Admin can access everything
        if ($userRole === 'admin') {
            return $next($request);
        }
        
        // Check specific role
        if ($userRole !== $role) {
            // Redirect to appropriate dashboard based on actual role
            switch ($userRole) {
                case 'siswa':
                    return redirect()->route('siswa.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut');
                case 'guru':
                    return redirect()->route('guru.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut');
                case 'tendik':
                    return redirect()->route('tendik.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut');
                default:
                    return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut');
            }
        }

        return $next($request);
    }
}