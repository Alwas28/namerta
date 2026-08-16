<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Get current active tahun ajaran
        $currentTA = DB::table('tahun_pelajaran')
            ->where('aktif', 'Y')
            ->first();

        // Basic Statistics
        $stats = $this->getBasicStats($currentTA);
        
        // User Statistics
        $userStats = $this->getUserStats();
        
        // Academic Year Statistics
        $academicStats = $this->getAcademicStats($currentTA);
        
        // Recent Activities
        $recentActivities = $this->getRecentActivities();
        
        // System Status
        $systemStatus = $this->getSystemStatus();
        
        // Quick Actions Data
        $quickActionsData = $this->getQuickActionsData();

        return view('admin.dashboard', compact(
            'currentTA',
            'stats',
            'userStats', 
            'academicStats',
            'recentActivities',
            'systemStatus',
            'quickActionsData'
        ));
    }

    /**
     * Get basic statistics
     */
    private function getBasicStats($currentTA)
    {
        $stats = [];
        
        // Total Users
        $stats['total_users'] = DB::table('users')->count();
        $stats['active_users'] = DB::table('users')->where('aktif', 'Y')->count();
        
        // Total Students
        $stats['total_siswa'] = DB::table('profile')->where('status', 'siswa')->count();
        
        // Total Teachers
        $stats['total_guru'] = DB::table('profile')->where('status', 'guru')->count();
        
        // Total Classes
        $stats['total_kelas'] = DB::table('kelas')->where('aktif', 'Y')->count();
        
        // Total Subjects
        $stats['total_mapel'] = DB::table('mata_pelajaran')->where('aktif', 'Y')->count();
        
        // Current Year Classes
        if ($currentTA) {
            $stats['current_year_classes'] = DB::table('kelas_ta')
                ->where('id_ta', $currentTA->id_ta)
                ->count();
                
            // Students enrolled in current year
            if (DB::getSchemaBuilder()->hasTable('siswa_kelas')) {
                $stats['enrolled_students'] = DB::table('siswa_kelas')
                    ->join('kelas_ta', 'siswa_kelas.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
                    ->where('kelas_ta.id_ta', $currentTA->id_ta)
                    ->where('siswa_kelas.aktif', 'Y')
                    ->distinct('siswa_kelas.id_user')
                    ->count();
            } else {
                $stats['enrolled_students'] = 0;
            }
        } else {
            $stats['current_year_classes'] = 0;
            $stats['enrolled_students'] = 0;
        }

        return $stats;
    }

    /**
     * Get user statistics
     */
    private function getUserStats()
    {
        $userStats = [];
        
        // Users by status
        $userStats['by_status'] = DB::table('profile')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
            
        // Recent registrations (last 30 days)
        $userStats['recent_registrations'] = DB::table('users')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->count();
            
        // Active vs Inactive users
        $userStats['active_count'] = DB::table('users')->where('aktif', 'Y')->count();
        $userStats['inactive_count'] = DB::table('users')->where('aktif', 'N')->count();
        
        return $userStats;
    }

    /**
     * Get academic statistics
     */
    private function getAcademicStats($currentTA)
    {
        $academicStats = [];
        
        if (!$currentTA) {
            return [
                'kelas_with_mapel' => 0,
                'kelas_without_mapel' => 0,
                'total_enrollments' => 0,
                'completion_rate' => 0
            ];
        }
        
        // Classes with subjects assigned
        $kelasWithMapel = DB::table('kelas_ta')
            ->leftJoin('kelas_mp', 'kelas_ta.id_kelas_ta', '=', 'kelas_mp.id_kelas_ta')
            ->where('kelas_ta.id_ta', $currentTA->id_ta)
            ->whereNotNull('kelas_mp.id_kelas_mp')
            ->distinct('kelas_ta.id_kelas_ta')
            ->count();
            
        $totalKelas = DB::table('kelas_ta')
            ->where('id_ta', $currentTA->id_ta)
            ->count();
            
        $academicStats['kelas_with_mapel'] = $kelasWithMapel;
        $academicStats['kelas_without_mapel'] = $totalKelas - $kelasWithMapel;
        
        // Total subject enrollments
        $academicStats['total_enrollments'] = DB::table('kelas_mp')
            ->join('kelas_ta', 'kelas_mp.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
            ->where('kelas_ta.id_ta', $currentTA->id_ta)
            ->where('kelas_mp.aktif', 'Y')
            ->count();
            
        // Calculate completion rate (classes with subjects / total classes)
        $academicStats['completion_rate'] = $totalKelas > 0 ? 
            round(($kelasWithMapel / $totalKelas) * 100, 1) : 0;
        
        return $academicStats;
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        // Recent user registrations
        $recentUsers = DB::table('users')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->select('profile.nama', 'profile.status', 'users.created_at')
            ->orderBy('users.created_at', 'desc')
            ->limit(5)
            ->get();
            
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user_registration',
                'message' => "User baru terdaftar: {$user->nama} ({$user->status})",
                'time' => Carbon::parse($user->created_at)->diffForHumans(),
                'icon' => 'user-plus',
                'color' => 'blue'
            ];
        }
        
        // Recent class assignments (if table exists)
        if (DB::getSchemaBuilder()->hasTable('kelas_mp')) {
            $recentAssignments = DB::table('kelas_mp')
                ->join('kelas_ta', 'kelas_mp.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
                ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
                ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
                ->select('kelas.nama_kelas', 'mata_pelajaran.nama_mata_pelajaran', 'kelas_mp.created_at')
                ->orderBy('kelas_mp.created_at', 'desc')
                ->limit(3)
                ->get();
                
            foreach ($recentAssignments as $assignment) {
                $activities[] = [
                    'type' => 'subject_assignment',
                    'message' => "Mata pelajaran {$assignment->nama_mata_pelajaran} ditambahkan ke {$assignment->nama_kelas}",
                    'time' => Carbon::parse($assignment->created_at)->diffForHumans(),
                    'icon' => 'book',
                    'color' => 'green'
                ];
            }
        }
        
        // Sort activities by time (most recent first)
        usort($activities, function($a, $b) {
            return strtotime($a['time']) - strtotime($b['time']);
        });
        
        return array_slice($activities, 0, 8);
    }

    /**
     * Get system status
     */
    private function getSystemStatus()
    {
        $status = [];
        
        // Database connectivity
        try {
            DB::connection()->getPdo();
            $status['database'] = ['status' => 'healthy', 'message' => 'Database connected'];
        } catch (\Exception $e) {
            $status['database'] = ['status' => 'error', 'message' => 'Database connection failed'];
        }
        
        // Check required tables
        $requiredTables = ['users', 'profile', 'kelas', 'mata_pelajaran', 'tahun_pelajaran', 'kelas_ta'];
        $missingTables = [];
        
        foreach ($requiredTables as $table) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                $missingTables[] = $table;
            }
        }
        
        if (empty($missingTables)) {
            $status['tables'] = ['status' => 'healthy', 'message' => 'All required tables exist'];
        } else {
            $status['tables'] = ['status' => 'warning', 'message' => 'Missing tables: ' . implode(', ', $missingTables)];
        }
        
        // Check active tahun ajaran
        $activeTACount = DB::table('tahun_pelajaran')->where('aktif', 'Y')->count();
        if ($activeTACount === 1) {
            $status['academic_year'] = ['status' => 'healthy', 'message' => 'Active academic year configured'];
        } elseif ($activeTACount === 0) {
            $status['academic_year'] = ['status' => 'warning', 'message' => 'No active academic year'];
        } else {
            $status['academic_year'] = ['status' => 'error', 'message' => 'Multiple active academic years'];
        }
        
        return $status;
    }

    /**
     * Get data for quick actions
     */
    private function getQuickActionsData()
    {
        $data = [];
        
        // Count pending items that need attention
        $data['inactive_users'] = DB::table('users')->where('aktif', 'N')->count();
        
        $currentTA = DB::table('tahun_pelajaran')->where('aktif', 'Y')->first();
        if ($currentTA) {
            // Classes without subjects
            $totalKelas = DB::table('kelas_ta')->where('id_ta', $currentTA->id_ta)->count();
            $kelasWithMapel = 0;
            
            if (DB::getSchemaBuilder()->hasTable('kelas_mp')) {
                $kelasWithMapel = DB::table('kelas_ta')
                    ->leftJoin('kelas_mp', 'kelas_ta.id_kelas_ta', '=', 'kelas_mp.id_kelas_ta')
                    ->where('kelas_ta.id_ta', $currentTA->id_ta)
                    ->whereNotNull('kelas_mp.id_kelas_mp')
                    ->distinct('kelas_ta.id_kelas_ta')
                    ->count();
            }
            
            $data['kelas_without_subjects'] = $totalKelas - $kelasWithMapel;
        } else {
            $data['kelas_without_subjects'] = 0;
        }
        
        return $data;
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'users');
        
        switch ($type) {
            case 'users':
                return $this->getUserChartData();
            case 'enrollment':
                return $this->getEnrollmentChartData();
            case 'activity':
                return $this->getActivityChartData();
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Get user chart data
     */
    private function getUserChartData()
    {
        $data = DB::table('profile')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
            
        return response()->json([
            'labels' => $data->pluck('status')->map(function($status) {
                return ucfirst($status);
            }),
            'data' => $data->pluck('count'),
            'backgroundColor' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444']
        ]);
    }

    /**
     * Get enrollment chart data
     */
    private function getEnrollmentChartData()
    {
        $currentTA = DB::table('tahun_pelajaran')->where('aktif', 'Y')->first();
        
        if (!$currentTA || !DB::getSchemaBuilder()->hasTable('siswa_kelas')) {
            return response()->json([
                'labels' => [],
                'data' => [],
                'backgroundColor' => []
            ]);
        }
        
        $data = DB::table('kelas_ta')
            ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
            ->leftJoin('siswa_kelas', 'kelas_ta.id_kelas_ta', '=', 'siswa_kelas.id_kelas_ta')
            ->where('kelas_ta.id_ta', $currentTA->id_ta)
            ->where(function($query) {
                $query->whereNull('siswa_kelas.aktif')
                      ->orWhere('siswa_kelas.aktif', 'Y');
            })
            ->select('kelas.nama_kelas', DB::raw('COUNT(siswa_kelas.id) as student_count'))
            ->groupBy('kelas.id_kelas', 'kelas.nama_kelas')
            ->get();
            
        return response()->json([
            'labels' => $data->pluck('nama_kelas'),
            'data' => $data->pluck('student_count'),
            'backgroundColor' => '#3B82F6'
        ]);
    }

    /**
     * Get activity chart data (last 7 days)
     */
    private function getActivityChartData()
    {
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $userCount = DB::table('users')
                ->whereDate('created_at', $date->toDateString())
                ->count();
                
            $last7Days->push([
                'date' => $date->format('M d'),
                'users' => $userCount
            ]);
        }
        
        return response()->json([
            'labels' => $last7Days->pluck('date'),
            'data' => $last7Days->pluck('users'),
            'backgroundColor' => '#10B981'
        ]);
    }
    

    public function guru()
    {
        $userId = Auth::user()->id_user;
        
        // Get teacher profile info
        $profile = DB::table('profile')
            ->where('id_user', $userId)
            ->first();
        
        // Get total classes taught by this teacher (active classes)
        $totalKelas = DB::table('kelas_mp')
            ->where('id_user', $userId)
            ->where('aktif', 'Y')
            ->count();
        
        // Get total students across all classes
        $totalSiswa = DB::table('kelas_mp')
            ->join('siswa_kelas', 'kelas_mp.id_kelas_ta', '=', 'siswa_kelas.id_kelas_ta')
            ->where('kelas_mp.id_user', $userId)
            ->where('kelas_mp.aktif', 'Y')
            ->where('siswa_kelas.aktif', 'Y')
            ->distinct('siswa_kelas.id_user')
            ->count('siswa_kelas.id_user');
        
        // Get total active modules
        $totalModul = DB::table('modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->where('kelas_mp.aktif', 'Y')
            ->distinct('modul.id_modul')
            ->count('modul.id_modul');
        
        // Get ungraded assignments count (all components that need grading)
        $tugasBelumDinilai = 0;
        
        // Mulai Dari Diri
        $tugasBelumDinilai += DB::table('jawaban_mulai_dari_diri')
            ->join('materi', 'jawaban_mulai_dari_diri.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->whereNull('jawaban_mulai_dari_diri.nilai')
            ->count();
        
        // Ruang Kolaborasi
        $tugasBelumDinilai += DB::table('jawaban_ruang_kolaborasi')
            ->join('materi', 'jawaban_ruang_kolaborasi.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->whereNull('jawaban_ruang_kolaborasi.nilai')
            ->count();
        
        // Demonstrasi Konseptual
        $tugasBelumDinilai += DB::table('jawaban_demonstrasi_konseptual')
            ->join('materi', 'jawaban_demonstrasi_konseptual.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->whereNull('jawaban_demonstrasi_konseptual.nilai')
            ->count();
        
        // Elaborasi Pemahaman
        $tugasBelumDinilai += DB::table('jawaban_elaborasi_pemahaman')
            ->join('materi', 'jawaban_elaborasi_pemahaman.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->whereNull('jawaban_elaborasi_pemahaman.nilai')
            ->count();
        
        // Get active students today (students who have submitted work today)
        $siswaAktifHariIni = DB::table('checklist_materi')
            ->join('users', 'checklist_materi.id_user', '=', 'users.id_user')
            ->join('materi', 'checklist_materi.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->whereDate('checklist_materi.updated_at', today())
            ->distinct('checklist_materi.id_user')
            ->count('checklist_materi.id_user');
        
        // Calculate average progress across all students
        $avgProgress = DB::table('checklist_materi')
            ->join('materi', 'checklist_materi.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->selectRaw('AVG(
                (CASE WHEN checklist_materi.mulai_dari_diri = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.eksplorasi_konsep = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.ruang_kolaborasi = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.demonstrasi_konseptual = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.elaborasi_pemahaman = "Y" THEN 1 ELSE 0 END) * 20
            ) as avg_progress')
            ->value('avg_progress');
        
        $avgProgress = round($avgProgress ?? 0);
        
        // Get classes with details
        $kelasDetail = DB::table('kelas_mp')
            ->join('kelas_ta', 'kelas_mp.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
            ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->where('kelas_mp.aktif', 'Y')
            ->select(
                'kelas_mp.id_kelas_mp',
                'kelas.nama_kelas',
                'mata_pelajaran.nama_mata_pelajaran',
                'kelas_mp.id_kelas_ta',
                'kelas_mp.id_mata_pelajaran'
            )
            ->get();
        
        // Get detailed class info with student count and progress
        $kelasList = [];
        $colors = ['blue', 'green', 'purple', 'teal', 'orange', 'red', 'indigo', 'pink'];
        
        foreach ($kelasDetail as $index => $kelas) {
            // Count students in this class
            $jumlahSiswa = DB::table('siswa_kelas')
                ->where('id_kelas_ta', $kelas->id_kelas_ta)
                ->where('aktif', 'Y')
                ->count();
            
            // Get modules for this class
            $modulAktif = DB::table('modul')
                ->where('id_mata_pelajaran', $kelas->id_mata_pelajaran)
                ->count();
            
            // Get ungraded assignments for this class
            $tugasPending = 0;
            
            // Mulai Dari Diri
            $tugasPending += DB::table('jawaban_mulai_dari_diri')
                ->join('materi', 'jawaban_mulai_dari_diri.id_materi', '=', 'materi.id_materi')
                ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
                ->where('modul.id_mata_pelajaran', $kelas->id_mata_pelajaran)
                ->whereNull('jawaban_mulai_dari_diri.nilai')
                ->count();
            
            // Calculate average progress for this class
            $classProgress = DB::table('checklist_materi')
                ->join('materi', 'checklist_materi.id_materi', '=', 'materi.id_materi')
                ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
                ->join('siswa_kelas', 'checklist_materi.id_user', '=', 'siswa_kelas.id_user')
                ->where('modul.id_mata_pelajaran', $kelas->id_mata_pelajaran)
                ->where('siswa_kelas.id_kelas_ta', $kelas->id_kelas_ta)
                ->where('siswa_kelas.aktif', 'Y')
                ->selectRaw('AVG(
                    (CASE WHEN checklist_materi.mulai_dari_diri = "Y" THEN 1 ELSE 0 END +
                     CASE WHEN checklist_materi.eksplorasi_konsep = "Y" THEN 1 ELSE 0 END +
                     CASE WHEN checklist_materi.ruang_kolaborasi = "Y" THEN 1 ELSE 0 END +
                     CASE WHEN checklist_materi.demonstrasi_konseptual = "Y" THEN 1 ELSE 0 END +
                     CASE WHEN checklist_materi.elaborasi_pemahaman = "Y" THEN 1 ELSE 0 END) * 20
                ) as avg_progress')
                ->value('avg_progress');
            
            $kelasList[] = [
                'id' => $kelas->id_kelas_mp,
                'nama' => $kelas->nama_kelas,
                'mata_pelajaran' => $kelas->nama_mata_pelajaran,
                'jumlah_siswa' => $jumlahSiswa,
                'modul_aktif' => $modulAktif,
                'tugas_pending' => $tugasPending,
                'progress' => round($classProgress ?? 0),
                'color' => $colors[$index % count($colors)]
            ];
        }
        
        // Get module progress overview
        $modulProgress = DB::table('modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->where('kelas_mp.aktif', 'Y')
            ->select('modul.id_modul', 'modul.nama_modul')
            ->distinct()
            ->limit(5)
            ->get();
        
        $modulList = [];
        foreach ($modulProgress as $modul) {
            // Get all materials in this module
            $materials = DB::table('materi')
                ->where('id_modul', $modul->id_modul)
                ->pluck('id_materi');
            
            if ($materials->isEmpty()) continue;
            
            // Calculate progress for each component
            $components = [
                'mulai_dari_diri',
                'eksplorasi_konsep',
                'ruang_kolaborasi',
                'demonstrasi_konseptual',
                'elaborasi_pemahaman'
            ];
            
            $componentProgress = [];
            foreach ($components as $component) {
                $completed = DB::table('checklist_materi')
                    ->whereIn('id_materi', $materials)
                    ->where('checklist_materi.' . $component, 'Y')
                    ->count();
                
                $total = DB::table('checklist_materi')
                    ->whereIn('id_materi', $materials)
                    ->count();
                
                $componentProgress[$component] = $total > 0 ? round(($completed / $total) * 100) : 0;
            }
            
            // Count students working on this module
            $siswaAktif = DB::table('checklist_materi')
                ->whereIn('id_materi', $materials)
                ->distinct('id_user')
                ->count('id_user');
            
            $modulList[] = [
                'nama' => $modul->nama_modul,
                'progress' => $componentProgress,
                'siswa_aktif' => $siswaAktif,
                'status' => $siswaAktif > 0 ? 'Aktif' : 'Belum Dimulai'
            ];
        }
        
        // Get assignments that need grading (with priority)
        $tugasPerluDinilai = [];
        
        // Get from all answer tables
        $answerTables = [
            'jawaban_mulai_dari_diri' => 'Mulai Dari Diri',
            'jawaban_ruang_kolaborasi' => 'Ruang Kolaborasi',
            'jawaban_demonstrasi_konseptual' => 'Demonstrasi Konseptual',
            'jawaban_elaborasi_pemahaman' => 'Elaborasi Pemahaman'
        ];
        
        foreach ($answerTables as $table => $componentName) {
            $idField = 'id_' . ($table === 'jawaban_demonstrasi_konseptual' ? 'demonstrasi_konseptual' : str_replace('jawaban_', '', $table));
            
            $assignments = DB::table($table)
                ->join('materi', $table . '.id_materi', '=', 'materi.id_materi')
                ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
                ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
                ->join('kelas_ta', 'kelas_mp.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
                ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
                ->where('kelas_mp.id_user', $userId)
                ->whereNull($table . '.nilai')
                ->select(
                    'materi.nama_materi',
                    'kelas.nama_kelas',
                    $table . '.created_at',
                    DB::raw('COUNT(*) as jumlah_siswa')
                )
                ->groupBy('materi.id_materi', 'materi.nama_materi', 'kelas.nama_kelas', $table . '.created_at')
                ->limit(5)
                ->get();
            
            foreach ($assignments as $assignment) {
                $daysOld = now()->diffInDays($assignment->created_at);
                $priority = $daysOld > 3 ? 'high' : ($daysOld > 1 ? 'medium' : 'low');
                $deadline = $daysOld > 3 ? $daysOld . ' hari lalu' : 
                           ($daysOld > 1 ? $daysOld . ' hari' : 'Baru');
                
                $tugasPerluDinilai[] = [
                    'nama' => $componentName . ' - ' . $assignment->nama_materi,
                    'kelas' => $assignment->nama_kelas,
                    'jumlah_siswa' => $assignment->jumlah_siswa,
                    'priority' => $priority,
                    'deadline' => $deadline
                ];
            }
        }
        
        // Sort by priority
        usort($tugasPerluDinilai, function($a, $b) {
            $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            return $priorityOrder[$a['priority']] - $priorityOrder[$b['priority']];
        });
        
        $tugasPerluDinilai = array_slice($tugasPerluDinilai, 0, 10);
        
        // Get recent activities
        $aktivitasTerbaru = DB::table('checklist_materi')
            ->join('users', 'checklist_materi.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->join('materi', 'checklist_materi.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('kelas_mp.id_user', $userId)
            ->select(
                'profile.nama',
                'materi.nama_materi',
                'checklist_materi.updated_at',
                DB::raw('CASE 
                    WHEN checklist_materi.elaborasi_pemahaman = "Y" THEN "Menyelesaikan Elaborasi Pemahaman"
                    WHEN checklist_materi.demonstrasi_konseptual = "Y" THEN "Menyelesaikan Demonstrasi Konseptual"
                    WHEN checklist_materi.ruang_kolaborasi = "Y" THEN "Menyelesaikan Ruang Kolaborasi"
                    WHEN checklist_materi.eksplorasi_konsep = "Y" THEN "Menyelesaikan Eksplorasi Konsep"
                    WHEN checklist_materi.mulai_dari_diri = "Y" THEN "Menyelesaikan Mulai Dari Diri"
                    ELSE "Memulai Materi"
                END as aktivitas')
            )
            ->orderBy('checklist_materi.updated_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboard.guru', compact(
            'profile',
            'totalKelas',
            'totalSiswa',
            'totalModul',
            'tugasBelumDinilai',
            'siswaAktifHariIni',
            'avgProgress',
            'kelasList',
            'modulList',
            'tugasPerluDinilai',
            'aktivitasTerbaru'
        ));
    }

    public function siswa()
    {
        $userId = Auth::user()->id_user;
        
        // Get student profile
        $profile = DB::table('profile')
            ->where('id_user', $userId)
            ->first();
        
        // Get student's class info
        $kelasInfo = DB::table('siswa_kelas')
            ->join('kelas_ta', 'siswa_kelas.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
            ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
            ->join('tahun_pelajaran', 'kelas_ta.id_ta', '=', 'tahun_pelajaran.id_ta')
            ->where('siswa_kelas.id_user', $userId)
            ->where('siswa_kelas.aktif', 'Y')
            ->where('tahun_pelajaran.aktif', 'Y')
            ->select('kelas.nama_kelas', 'tahun_pelajaran.nama_ta', 'siswa_kelas.id_kelas_ta')
            ->first();
        
        // Get total subjects (mata pelajaran) for this student
        $totalMataPelajaran = DB::table('kelas_mp')
            ->where('id_kelas_ta', $kelasInfo->id_kelas_ta ?? 0)
            ->where('aktif', 'Y')
            ->count();
        
        // Get completed modules count
        $modulSelesai = DB::table('checklist_modul')
            ->join('modul', 'checklist_modul.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('checklist_modul.id_user', $userId)
            ->where('kelas_mp.id_kelas_ta', $kelasInfo->id_kelas_ta ?? 0)
            ->where('checklist_modul.materi', 'Y')
            ->where('checklist_modul.aksi_nyata', 'Y')
            ->count();
        
        // Get pending tasks count (materi yang belum selesai)
        $tugasPending = DB::table('checklist_materi')
            ->join('materi', 'checklist_materi.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('checklist_materi.id_user', $userId)
            ->where('kelas_mp.id_kelas_ta', $kelasInfo->id_kelas_ta ?? 0)
            ->where(function($query) {
                $query->where('checklist_materi.elaborasi_pemahaman', 'N');
            })
            ->count();
        
        // Calculate overall progress
        $overallProgress = DB::table('checklist_materi')
            ->join('materi', 'checklist_materi.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->where('checklist_materi.id_user', $userId)
            ->where('kelas_mp.id_kelas_ta', $kelasInfo->id_kelas_ta ?? 0)
            ->selectRaw('AVG(
                (CASE WHEN checklist_materi.mulai_dari_diri = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.eksplorasi_konsep = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.ruang_kolaborasi = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.demonstrasi_konseptual = "Y" THEN 1 ELSE 0 END +
                 CASE WHEN checklist_materi.elaborasi_pemahaman = "Y" THEN 1 ELSE 0 END) * 20
            ) as avg_progress')
            ->value('avg_progress');
        
        $overallProgress = round($overallProgress ?? 0);
        
        // Get subjects with progress
        $mataPelajaranList = DB::table('kelas_mp')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->join('users', 'kelas_mp.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where('kelas_mp.id_kelas_ta', $kelasInfo->id_kelas_ta ?? 0)
            ->where('kelas_mp.aktif', 'Y')
            ->select(
                'kelas_mp.id_kelas_mp',
                'mata_pelajaran.nama_mata_pelajaran',
                'mata_pelajaran.id_mata_pelajaran',
                'profile.nama as nama_guru'
            )
            ->get();
        
        $colors = ['blue', 'green', 'purple', 'teal', 'orange', 'red', 'indigo', 'pink'];
        $mataPelajaran = [];
        
        foreach ($mataPelajaranList as $index => $mp) {
            // Count total and completed modules
            $totalModul = DB::table('modul')
                ->where('id_mata_pelajaran', $mp->id_mata_pelajaran)
                ->count();
            
            $modulDone = DB::table('checklist_modul')
                ->join('modul', 'checklist_modul.id_modul', '=', 'modul.id_modul')
                ->where('checklist_modul.id_user', $userId)
                ->where('modul.id_mata_pelajaran', $mp->id_mata_pelajaran)
                ->where('checklist_modul.materi', 'Y')
                ->where('checklist_modul.aksi_nyata', 'Y')
                ->count();
            
            // Calculate progress for this subject
            $progress = $totalModul > 0 ? round(($modulDone / $totalModul) * 100) : 0;
            
            $mataPelajaran[] = [
                'id' => $mp->id_kelas_mp,
                'nama' => $mp->nama_mata_pelajaran,
                'guru' => $mp->nama_guru,
                'progress' => $progress,
                'modul_selesai' => $modulDone,
                'total_modul' => $totalModul,
                'color' => $colors[$index % count($colors)]
            ];
        }
        
        // Get current learning module (module in progress)
        $currentModule = DB::table('checklist_materi')
            ->join('materi', 'checklist_materi.id_materi', '=', 'materi.id_materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->where('checklist_materi.id_user', $userId)
            ->where('kelas_mp.id_kelas_ta', $kelasInfo->id_kelas_ta ?? 0)
            ->where('checklist_materi.elaborasi_pemahaman', 'N')
            ->orderBy('checklist_materi.updated_at', 'desc')
            ->select(
                'modul.nama_modul',
                'materi.nama_materi',
                'materi.id_materi',
                'mata_pelajaran.nama_mata_pelajaran',
                'checklist_materi.*'
            )
            ->first();
        
        // Get learning path steps for current material
        $learningPath = [];
        if ($currentModule) {
            $steps = [
                ['name' => 'Mulai dari Diri', 'field' => 'mulai_dari_diri', 'num' => 1],
                ['name' => 'Eksplorasi Konsep', 'field' => 'eksplorasi_konsep', 'num' => 2],
                ['name' => 'Ruang Kolaborasi', 'field' => 'ruang_kolaborasi', 'num' => 3],
                ['name' => 'Demonstrasi Konseptual', 'field' => 'demonstrasi_konseptual', 'num' => 4],
                ['name' => 'Elaborasi Pemahaman', 'field' => 'elaborasi_pemahaman', 'num' => 5],
            ];
            
            foreach ($steps as $step) {
                $isCompleted = $currentModule->{$step['field']} == 'Y';
                $learningPath[] = [
                    'name' => $step['name'],
                    'number' => $step['num'],
                    'completed' => $isCompleted,
                    'current' => false
                ];
            }
            
            // Mark current step
            for ($i = 0; $i < count($learningPath); $i++) {
                if (!$learningPath[$i]['completed']) {
                    $learningPath[$i]['current'] = true;
                    break;
                }
            }
        }
        
        // Get upcoming tasks/assignments with deadlines
        $upcomingTasks = DB::table('materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->join('kelas_mp', 'modul.id_mata_pelajaran', '=', 'kelas_mp.id_mata_pelajaran')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->join('users', 'kelas_mp.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->leftJoin('checklist_materi', function($join) use ($userId) {
                $join->on('materi.id_materi', '=', 'checklist_materi.id_materi')
                     ->where('checklist_materi.id_user', '=', $userId);
            })
            ->where('kelas_mp.id_kelas_ta', $kelasInfo->id_kelas_ta ?? 0)
            ->where(function($query) {
                $query->whereNull('checklist_materi.id_materi')
                      ->orWhere('checklist_materi.elaborasi_pemahaman', 'N');
            })
            ->select(
                'materi.nama_materi',
                'mata_pelajaran.nama_mata_pelajaran',
                'profile.nama as nama_guru',
                'materi.created_at',
                'materi.id_materi',
                'modul.id_modul'  // TAMBAHKAN INI
            )
            ->orderBy('materi.created_at', 'asc')
            ->limit(5)
            ->get();
        
        $tugasMendatang = [];
        foreach ($upcomingTasks as $task) {
            $daysAgo = Carbon::now()->diffInDays($task->created_at);
            
            if ($daysAgo <= 2) {
                $priority = 'urgent';
                $deadline = $daysAgo == 0 ? 'Hari ini' : ($daysAgo == 1 ? 'Besok' : '2 hari lagi');
            } elseif ($daysAgo <= 5) {
                $priority = 'soon';
                $deadline = $daysAgo . ' hari lagi';
            } else {
                $priority = 'normal';
                $deadline = $daysAgo > 7 ? ($daysAgo / 7) . ' minggu lagi' : $daysAgo . ' hari lagi';
            }
            
            $tugasMendatang[] = [
                'id' => $task->id_materi,
                'nama' => $task->nama_materi,
                'id_modul' => $task->id_modul,  // TAMBAHKAN INI
                'mata_pelajaran' => $task->nama_mata_pelajaran,
                'guru' => $task->nama_guru,
                'priority' => $priority,
                'deadline' => $deadline
            ];
        }
        
        // Calculate badge count (achievements)
        $badgeCount = 0;
        
        // Badge for completing modules
        if ($modulSelesai >= 10) $badgeCount++;
        if ($modulSelesai >= 25) $badgeCount++;
        if ($modulSelesai >= 50) $badgeCount++;
        
        // Badge for high progress
        if ($overallProgress >= 70) $badgeCount++;
        if ($overallProgress >= 85) $badgeCount++;
        if ($overallProgress >= 95) $badgeCount++;
        
        return view('dashboard.siswa', compact(
            'profile',
            'kelasInfo',
            'totalMataPelajaran',
            'modulSelesai',
            'tugasPending',
            'overallProgress',
            'mataPelajaran',
            'currentModule',
            'learningPath',
            'tugasMendatang',
            'badgeCount'
        ));
    }

    function tendik()
    {
        return view('dashboard.tendik');
    }
}