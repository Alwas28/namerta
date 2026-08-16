<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Display list of subjects for current student
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id_user;
        
        // Get current academic year
        $currentTA = DB::table('tahun_pelajaran')
            ->where('aktif', 'Y')
            ->first();
            
        if (!$currentTA) {
            return view('student.subjects.index', [
                'subjects' => [],
                'currentTA' => null,
                'studentClass' => null,
                'message' => 'Tidak ada tahun ajaran aktif'
            ]);
        }
        
        // Get student's class
        $studentClass = $this->getStudentClass($userId, $currentTA);
        
        if (!$studentClass) {
            return view('student.subjects.index', [
                'subjects' => [],
                'currentTA' => $currentTA,
                'studentClass' => null,
                'message' => 'Anda belum terdaftar di kelas manapun'
            ]);
        }
        
        // Get subjects for this class
        $subjects = $this->getSubjectsForClass($studentClass->id_kelas_ta, $userId);
        
        return view('mata_pelajaran.index', compact(
            'subjects',
            'currentTA', 
            'studentClass'
        ));
    }
    
    /**
     * Display specific subject with its modules
     */
    public function show($id)
    {
        $user = Auth::user();
        $userId = $user->id_user;
        
        // Get subject details
        $subject = DB::table('mata_pelajaran')
            ->where('id_mata_pelajaran', $id)
            ->where('aktif', 'Y')
            ->first();
            
        if (!$subject) {
            abort(404, 'Mata pelajaran tidak ditemukan');
        }
        
        // Check if student has access to this subject
        $hasAccess = $this->checkSubjectAccess($userId, $id);
        
        if (!$hasAccess) {
            abort(403, 'Anda tidak memiliki akses ke mata pelajaran ini');
        }
        
        // Get modules for this subject
        $modules = $this->getModulesForSubject($id, $userId);
        
        // Get teacher info
        $teacher = $this->getSubjectTeacher($id, $userId);
        
        // Get subject statistics
        $stats = $this->getSubjectStatistics($id, $userId);
        
        return view('mata_pelajaran.show', compact(
            'subject',
            'modules',
            'teacher',
            'stats'
        ));
    }
    
    /**
     * Get student's class information
     */
    private function getStudentClass($userId, $currentTA)
    {
        return DB::table('siswa_kelas')
            ->join('kelas_ta', 'siswa_kelas.id_kelas_ta', '=', 'kelas_ta.id_kelas_ta')
            ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
            ->where('siswa_kelas.id_user', $userId)
            ->where('kelas_ta.id_ta', $currentTA->id_ta)
            ->where('siswa_kelas.aktif', 'Y')
            ->select('kelas.nama_kelas', 'kelas_ta.id_kelas_ta')
            ->first();
    }
    
    /**
     * Get subjects for student's class
     */
    private function getSubjectsForClass($kelasTA, $userId)
    {
        $subjects = DB::table('kelas_mp')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->join('users', 'kelas_mp.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where('kelas_mp.id_kelas_ta', $kelasTA)
            ->where('kelas_mp.aktif', 'Y')
            ->where('mata_pelajaran.aktif', 'Y')
            ->select(
                'mata_pelajaran.id_mata_pelajaran',
                'mata_pelajaran.nama_mata_pelajaran',
                'mata_pelajaran.deskripsi',
                'profile.nama as teacher_name',
                'users.id_user as teacher_id'
            )
            ->get();
            
        // Add progress and module count for each subject
        foreach ($subjects as $subject) {
            $subject->module_count = $this->getModuleCount($subject->id_mata_pelajaran);
            $subject->progress = $this->calculateSubjectProgress($subject->id_mata_pelajaran, $userId);
            $subject->last_activity = $this->getLastActivity($subject->id_mata_pelajaran, $userId);
        }
        
        return $subjects;
    }
    
    /**
     * Check if student has access to subject
     */
    private function checkSubjectAccess($userId, $subjectId)
    {
        // Get current TA
        $currentTA = DB::table('tahun_pelajaran')->where('aktif', 'Y')->first();
        if (!$currentTA) return false;
        
        // Get student class
        $studentClass = $this->getStudentClass($userId, $currentTA);
        if (!$studentClass) return false;
        
        // Check if subject is assigned to student's class
        return DB::table('kelas_mp')
            ->where('id_kelas_ta', $studentClass->id_kelas_ta)
            ->where('id_mata_pelajaran', $subjectId)
            ->where('aktif', 'Y')
            ->exists();
    }
    
    /**
     * Get modules for specific subject
     */
    private function getModulesForSubject($subjectId, $userId, $userRole = 'siswa')
    {
        $modules = DB::table('modul')
            ->where('id_mata_pelajaran', $subjectId)
            ->orderBy('created_at', 'asc')
            ->get();
            
        // Add different data based on role
        foreach ($modules as $module) {
            if ($userRole === 'guru') {
                // For teachers: show teaching statistics
                $module->student_count = $this->getModuleStudentCount($module->id_modul, $userId);
                $module->avg_progress = $this->getModuleAverageProgress($module->id_modul, $userId);
                $module->completed_students = $this->getModuleCompletedStudents($module->id_modul, $userId);
                $module->pending_assignments = $this->getModulePendingAssignments($module->id_modul, $userId);
            } else {
                // For students: show personal progress
                $module->progress = $this->calculateModuleProgress($module->id_modul, $userId);
                $module->status = $this->getModuleStatus($module->id_modul, $userId);
                $module->last_accessed = $this->getModuleLastAccessed($module->id_modul, $userId);
            }
            
            $module->materi_count = $this->getMateriCount($module->id_modul);
        }
        
        return $modules;
    }
    
    /**
     * Get subject statistics based on user role
     */
    private function getSubjectStatistics($subjectId, $userId, $userRole = 'siswa')
    {
        $totalModules = $this->getModuleCount($subjectId);
        $totalMateri = $this->getTotalMateriCount($subjectId);
        
        if ($userRole === 'guru') {
            // Teacher statistics
            $studentCount = $this->getStudentCountForTeacherSubject($subjectId, $userId);
            $avgProgress = $this->getAverageProgressForTeacher($subjectId, $userId);
            $pendingAssignments = $this->getTotalPendingAssignments($subjectId, $userId);
            
            return [
                'total_modules' => $totalModules,
                'total_materi' => $totalMateri,
                'student_count' => $studentCount,
                'avg_progress' => $avgProgress,
                'pending_assignments' => $pendingAssignments,
                'active_students' => $this->getActiveStudentsCount($subjectId, $userId)
            ];
        } else {
            // Student statistics
            $completedModules = $this->getCompletedModuleCount($subjectId, $userId);
            $completedMateri = $this->getCompletedMateriCount($subjectId, $userId);
            
            return [
                'total_modules' => $totalModules,
                'completed_modules' => $completedModules,
                'total_materi' => $totalMateri,
                'completed_materi' => $completedMateri,
                'overall_progress' => $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0
            ];
        }
    }
    
    /**
     * Get module student count for teacher
     */
    private function getModuleStudentCount($moduleId, $teacherId)
    {
        // Mock data - count students who have access to this module
        return rand(25, 35);
    }
    
    /**
     * Get module average progress for teacher
     */
    private function getModuleAverageProgress($moduleId, $teacherId)
    {
        // Mock data - average progress of all students
        return rand(60, 90);
    }
    
    /**
     * Get module completed students count
     */
    private function getModuleCompletedStudents($moduleId, $teacherId)
    {
        // Mock data - students who completed the module
        return rand(15, 25);
    }
    
    /**
     * Get module pending assignments
     */
    private function getModulePendingAssignments($moduleId, $teacherId)
    {
        // Mock data - assignments waiting for grading
        return rand(0, 8);
    }
    
    /**
     * Get total pending assignments for subject
     */
    private function getTotalPendingAssignments($subjectId, $teacherId)
    {
        // Mock data - total assignments to be graded
        return rand(5, 15);
    }
    
    /**
     * Get active students count
     */
    private function getActiveStudentsCount($subjectId, $teacherId)
    {
        // Mock data - students active in last week
        return rand(20, 30);
    }
    
    /**
     * Get subject teacher info
     */
    private function getSubjectTeacher($subjectId, $userId)
    {
        // Get current TA and student class
        $currentTA = DB::table('tahun_pelajaran')->where('aktif', 'Y')->first();
        if (!$currentTA) return null;
        
        $studentClass = $this->getStudentClass($userId, $currentTA);
        if (!$studentClass) return null;
        
        return DB::table('kelas_mp')
            ->join('users', 'kelas_mp.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where('kelas_mp.id_kelas_ta', $studentClass->id_kelas_ta)
            ->where('kelas_mp.id_mata_pelajaran', $subjectId)
            ->where('kelas_mp.aktif', 'Y')
            ->select('profile.nama', 'profile.nip_nis', 'users.id_user')
            ->first();
    }
    
    /**
     * Get subject statistics
     */
    private function getSubjectStatistics($subjectId, $userId)
    {
        $totalModules = $this->getModuleCount($subjectId);
        $completedModules = $this->getCompletedModuleCount($subjectId, $userId);
        $totalMateri = $this->getTotalMateriCount($subjectId);
        $completedMateri = $this->getCompletedMateriCount($subjectId, $userId);
        
        return [
            'total_modules' => $totalModules,
            'completed_modules' => $completedModules,
            'total_materi' => $totalMateri,
            'completed_materi' => $completedMateri,
            'overall_progress' => $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0
        ];
    }
    
    /**
     * Calculate subject progress
     */
    private function calculateSubjectProgress($subjectId, $userId)
    {
        $totalModules = $this->getModuleCount($subjectId);
        if ($totalModules == 0) return 0;
        
        $completedModules = $this->getCompletedModuleCount($subjectId, $userId);
        return round(($completedModules / $totalModules) * 100);
    }
    
    /**
     * Calculate module progress
     */
    private function calculateModuleProgress($moduleId, $userId)
    {
        // Get materi for this module
        $totalMateri = DB::table('materi')->where('id_modul', $moduleId)->count();
        if ($totalMateri == 0) return 0;
        
        // Check completed materi based on checklist
        // This is simplified - in real implementation you'd check each materi's progress
        $checklist = DB::table('checklist')->where('id_user', $userId)->first();
        if (!$checklist) return 0;
        
        $completed = 0;
        if ($checklist->mulai_dari_diri == 'Y') $completed++;
        if ($checklist->eksplorasi_konsep == 'Y') $completed++;
        if ($checklist->ruang_kolaborasi == 'Y') $completed++;
        if ($checklist->refleksi_terbimbing == 'Y') $completed++;
        if ($checklist->demonstrasi_konseptual == 'Y') $completed++;
        if ($checklist->elaborasi_pemahaman == 'Y') $completed++;
        
        return round(($completed / 6) * 100);
    }
    
    /**
     * Get module status
     */
    private function getModuleStatus($moduleId, $userId)
    {
        $progress = $this->calculateModuleProgress($moduleId, $userId);
        
        if ($progress == 0) return 'not_started';
        if ($progress == 100) return 'completed';
        return 'in_progress';
    }
    
    /**
     * Get module count for subject
     */
    private function getModuleCount($subjectId)
    {
        return DB::table('modul')->where('id_mata_pelajaran', $subjectId)->count();
    }
    
    /**
     * Get completed module count
     */
    private function getCompletedModuleCount($subjectId, $userId)
    {
        // Simplified - count modules where student has completed all components
        $checklist = DB::table('checklist')->where('id_user', $userId)->first();
        if (!$checklist || $checklist->uji_kompetensi != 'Y') return 0;
        
        // In real implementation, this would check each module individually
        return 1; // Mock data
    }
    
    /**
     * Get materi count for module
     */
    private function getMateriCount($moduleId)
    {
        return DB::table('materi')->where('id_modul', $moduleId)->count();
    }
    
    /**
     * Get total materi count for subject
     */
    private function getTotalMateriCount($subjectId)
    {
        return DB::table('materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->where('modul.id_mata_pelajaran', $subjectId)
            ->count();
    }
    
    /**
     * Get completed materi count
     */
    private function getCompletedMateriCount($subjectId, $userId)
    {
        // Mock implementation
        return 5;
    }
    
    /**
     * Get last activity
     */
    private function getLastActivity($subjectId, $userId)
    {
        // Mock data - in real implementation, track user activity
        return 'Kemarin, 14:30';
    }
    
    /**
     * Get module last accessed
     */
    private function getModuleLastAccessed($moduleId, $userId)
    {
        // Mock data - in real implementation, track module access
        return now()->subDays(rand(1, 7));
    }
}