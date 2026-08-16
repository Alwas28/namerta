<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CoursesController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->id_user;
        
        // Query untuk mendapatkan mata pelajaran yang di-enroll user
        // Baik sebagai siswa maupun guru
        $courses = DB::table('kelas_mp')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->join('kelas_ta', 'kelas_mp.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
            ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
            ->join('tahun_pelajaran', 'kelas_ta.id_ta', '=', 'tahun_pelajaran.id_ta')
            ->leftJoin('users as guru', 'kelas_mp.id_user', '=', 'guru.id_user')
            ->leftJoin('profile as guru_profile', 'guru.id_user', '=', 'guru_profile.id_user')
            ->where('tahun_pelajaran.aktif', 'Y')
            ->where('kelas_mp.aktif', 'Y')
            ->where(function($query) use ($userId) {
                // User sebagai guru
                $query->where('kelas_mp.id_user', $userId)
                // Atau user sebagai siswa
                ->orWhereExists(function($q) use ($userId) {
                    $q->select(DB::raw(1))
                      ->from('siswa_kelas')
                      ->whereRaw('siswa_kelas.id_kelas_ta = kelas_mp.id_kelas_ta')
                      ->where('siswa_kelas.id_user', $userId)
                      ->where('siswa_kelas.aktif', 'Y');
                });
            })
            ->select(
                'kelas_mp.id_kelas_mp',
                'mata_pelajaran.nama_mata_pelajaran',
                'mata_pelajaran.deskripsi',
                'kelas.nama_kelas',
                'tahun_pelajaran.nama_ta',
                'guru_profile.nama as nama_guru',
                DB::raw('(SELECT COUNT(*) FROM modul WHERE modul.id_mata_pelajaran = mata_pelajaran.id_mata_pelajaran) as total_modul')
            )
            ->get();

        return view('mata_pelajaran.index', compact('courses'));
    }

    public function show($id)
    {
        $userId = Auth::user()->id_user;
        
        // Verify user has access to this course
        $hasAccess = DB::table('kelas_mp')
            ->where('id_kelas_mp', $id)
            ->where('aktif', 'Y')
            ->where(function($query) use ($userId) {
                $query->where('id_user', $userId)
                ->orWhereExists(function($q) use ($userId) {
                    $q->select(DB::raw(1))
                      ->from('siswa_kelas')
                      ->whereRaw('siswa_kelas.id_kelas_ta = kelas_mp.id_kelas_ta')
                      ->where('siswa_kelas.id_user', $userId)
                      ->where('siswa_kelas.aktif', 'Y');
                });
            })
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->route('mata_pelajaran.index')->with('error', 'Anda tidak memiliki akses ke mata pelajaran ini');
        }
        
        // Get course detail
        $course = DB::table('kelas_mp')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->join('kelas_ta', 'kelas_mp.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
            ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
            ->join('tahun_pelajaran', 'kelas_ta.id_ta', '=', 'tahun_pelajaran.id_ta')
            ->leftJoin('users as guru', 'kelas_mp.id_user', '=', 'guru.id_user')
            ->leftJoin('profile as guru_profile', 'guru.id_user', '=', 'guru_profile.id_user')
            ->where('kelas_mp.id_kelas_mp', $id)
            ->select(
                'kelas_mp.*',
                'mata_pelajaran.nama_mata_pelajaran',
                'mata_pelajaran.deskripsi',
                'kelas.nama_kelas',
                'tahun_pelajaran.nama_ta',
                'guru_profile.nama as nama_guru'
            )
            ->first();
            
        // Get modules for this course
        $modules = DB::table('modul')
            ->where('id_mata_pelajaran', $course->id_mata_pelajaran)
            ->orderBy('created_at', 'asc')
            ->get();
            
        return view('mata_pelajaran.show', compact('course', 'modules'));
    }
}