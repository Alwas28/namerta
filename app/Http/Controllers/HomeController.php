<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Ambil statistik data
        $stats = $this->getStatistics();
        
        // Ambil mata pelajaran dengan statistik
        $mataPelajaran = $this->getMataPelajaran();
        
        // Ambil data sekolah
        $sekolah = $this->getSekolahInfo();
        
        return view('home', compact('stats', 'mataPelajaran', 'sekolah'));
    }
    
    private function getStatistics()
    {
        // Hitung total siswa
        $totalSiswa = DB::table('profile')
            ->where('status', 'siswa')
            ->count();
        
        // Hitung total guru
        $totalGuru = DB::table('profile')
            ->where('status', 'guru')
            ->count();
        
        // Hitung total kursus/modul
        $totalKursus = DB::table('modul')->count();
        
        // Hitung total kelas
        $totalKelas = DB::table('kelas')
            ->where('aktif', 'Y')
            ->count();
        
        // Hitung total mata pelajaran
        $totalMataPelajaran = DB::table('mata_pelajaran')
            ->where('aktif', 'Y')
            ->count();
        
        return [
            'total_siswa' => $totalSiswa,
            'total_guru' => $totalGuru,
            'total_kursus' => $totalKursus,
            'total_kelas' => $totalKelas,
            'total_mata_pelajaran' => $totalMataPelajaran
        ];
    }
    
    private function getMataPelajaran()
    {
        // Ambil mata pelajaran dengan statistik
        $mataPelajaran = DB::table('mata_pelajaran')
            ->where('aktif', 'Y')
            ->select(
                'mata_pelajaran.id_mata_pelajaran',
                'mata_pelajaran.nama_mata_pelajaran',
                'mata_pelajaran.deskripsi'
            )
            ->limit(6)
            ->get();
        
        // Tambahkan statistik untuk setiap mata pelajaran
        foreach ($mataPelajaran as $mp) {
            // Hitung total modul
            $mp->total_modul = DB::table('modul')
                ->where('id_mata_pelajaran', $mp->id_mata_pelajaran)
                ->count();
            
            // Hitung total guru yang mengajar mata pelajaran ini
            $mp->total_guru = DB::table('kelas_mp')
                ->where('id_mata_pelajaran', $mp->id_mata_pelajaran)
                ->where('aktif', 'Y')
                ->distinct('id_user')
                ->count('id_user');
            
            // Hitung total siswa yang terdaftar
            $mp->total_siswa = DB::table('siswa_kelas')
                ->join('kelas_mp', 'siswa_kelas.id_kelas_ta', '=', 'kelas_mp.id_kelas_ta')
                ->where('kelas_mp.id_mata_pelajaran', $mp->id_mata_pelajaran)
                ->where('siswa_kelas.aktif', 'Y')
                ->distinct('siswa_kelas.id_user')
                ->count('siswa_kelas.id_user');
        }
        
        return $mataPelajaran;
    }
    
    private function getSekolahInfo()
    {
        // Ambil data sekolah pertama (asumsi hanya ada 1 sekolah)
        $sekolah = DB::table('sekolah')->first();
        
        if (!$sekolah) {
            // Jika tidak ada data sekolah, buat object kosong
            $sekolah = (object) [
                'nama_sekolah' => 'Sekolah Kami',
                'alamat' => 'Jl. Pendidikan No. 123',
                'kota' => 'Jakarta',
                'provinsi' => 'DKI Jakarta',
                'kelurahan' => '-'
            ];
        }
        
        return $sekolah;
    }
}