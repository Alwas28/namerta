<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModulController extends Controller
{
    /**
     * Check if user has permission to manage modules (CRUD)
     */
    private function hasManageAccess($courseId)
    {
        $userId = Auth::user()->id_user;
        
        // Check if user is the teacher of this course
        $isTeacher = DB::table('kelas_mp')
            ->where('id_kelas_mp', $courseId)
            ->where('id_user', $userId)
            ->where('aktif', 'Y')
            ->exists();
            
        // Check if user is admin
        $isAdmin = Auth::user()->is_admin == 'Y';
        
        return $isTeacher || $isAdmin;
    }
    
    /**
     * Check if user has permission to view modules
     */
    private function hasViewAccess($courseId)
    {
        $userId = Auth::user()->id_user;
        
        // Teacher or admin
        if ($this->hasManageAccess($courseId)) {
            return true;
        }
        
        // Student enrolled in this course
        $isStudent = DB::table('kelas_mp')
            ->join('siswa_kelas', 'kelas_mp.id_kelas_ta', '=', 'siswa_kelas.id_kelas_ta')
            ->where('kelas_mp.id_kelas_mp', $courseId)
            ->where('siswa_kelas.id_user', $userId)
            ->where('siswa_kelas.aktif', 'Y')
            ->exists();
            
        return $isStudent;
    }
    

    /**
     * Display listing of modules for a course
     */
    public function index($courseId)
    {
        if (!$this->hasViewAccess($courseId)) {
            return redirect()->route('courses.index')
                ->with('error', 'Anda tidak memiliki akses ke modul ini');
        }
        
        // Get course info
        $course = DB::table('kelas_mp')
            ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
            ->where('kelas_mp.id_kelas_mp', $courseId)
            ->select('kelas_mp.*', 'mata_pelajaran.nama_mata_pelajaran')
            ->first();
            
        if (!$course) {
            return redirect()->route('courses.index')
                ->with('error', 'Course tidak ditemukan');
        }
            
        // Get modules
        $modules = DB::table('modul')
            ->where('id_mata_pelajaran', $course->id_mata_pelajaran)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Check if user can manage modules
        $canManage = $this->hasManageAccess($courseId);
            
        return view('mata_pelajaran.modul', compact('course', 'modules', 'courseId', 'canManage'));
    }

    /**
     * Show module detail
     */
    public function show($courseId, $modulId)
    {
        if (!$this->hasViewAccess($courseId)) {
            return redirect()->route('courses.index')
                ->with('error', 'Anda tidak memiliki akses ke modul ini');
        }
        
        $userId = Auth::user()->id_user;
        
        // Get module
        $modul = DB::table('modul')
            ->where('id_modul', $modulId)
            ->first();
            
        if (!$modul) {
            return redirect()->back()->with('error', 'Modul tidak ditemukan');
        }
        
        // Get materials
        $materials = DB::table('materi')
            ->where('id_modul', $modulId)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Get or create checklist for student
        $checklist = null;
        if (!$this->hasManageAccess($courseId)) {
            $checklist = DB::table('checklist_modul')
                ->where('id_user', $userId)
                ->where('id_modul', $modulId)
                ->first();
                
            if (!$checklist) {
                DB::table('checklist_modul')->insert([
                    'id_user' => $userId,
                    'id_modul' => $modulId,
                    'pengalaman_belajar' => 'N',
                    'materi' => 'N',
                    'koneksi_materi' => 'N',
                    'aksi_nyata' => 'N',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $checklist = DB::table('checklist_modul')
                    ->where('id_user', $userId)
                    ->where('id_modul', $modulId)
                    ->first();
            }
        }
        
        // Get student answers
        $koneksiMateriFile = null;
        $aksiNyataAnswer = null;
        
        if (!$this->hasManageAccess($courseId)) {
            $koneksiMateriFile = DB::table('jawaban_koneksi_materi')
                ->where('id_user', $userId)
                ->where('id_modul', $modulId)
                ->first();
                
            $aksiNyataAnswer = DB::table('jawaban_aksi_nyata')
                ->where('id_user', $userId)
                ->where('id_modul', $modulId)
                ->first();
        }
        
        $canManage = $this->hasManageAccess($courseId);
        
        return view('mata_pelajaran.modul_show', compact(
            'modul', 
            'materials', 
            'courseId', 
            'canManage', 
            'checklist',
            'koneksiMateriFile',
            'aksiNyataAnswer'
        ));
    }
    

    /**
     * Store new material (Teacher only)
     */
    public function storeMaterial(Request $request, $courseId, $modulId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'nama_materi' => 'required|string|max:255'
        ]);
        
        DB::table('materi')->insert([
            'id_modul' => $modulId,
            'nama_materi' => $request->nama_materi,
            'mulai_dari_diri' => null,
            'eksplorasi_konsep' => null,
            'ruang_kolaborasi' => null,
            'refleksi_terbimbing' => null,
            'demonstrasi_konseptual' => null,
            'elaborasi_pemahaman' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Materi baru berhasil ditambahkan'
        ]);
    }

    /**
     * Update material name (Teacher only)
     */
    public function updateMaterial(Request $request, $courseId, $materiId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'nama_materi' => 'required|string|max:255'
        ]);
        
        $updated = DB::table('materi')
            ->where('id_materi', $materiId)
            ->update([
                'nama_materi' => $request->nama_materi,
                'updated_at' => now()
            ]);
        
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Nama materi berhasil diperbarui'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Materi tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Delete material (Teacher only)
     */
    public function destroyMaterial(Request $request, $courseId, $materiId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            // Log the delete attempt
            \Log::info('Attempting to delete material', ['material_id' => $materiId]);
            
            // Disable foreign key checks to avoid constraint issues
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            
            DB::beginTransaction();
            
            // First, get all eksplorasi_konsep IDs that belong to this material
            $eksplorasiKonsepIds = DB::select('SELECT id_eksplorasi_konsep FROM eksplorasi_konsep WHERE id_materi = ?', [$materiId]);
            $eksplorasiKonsepIdArray = array_column($eksplorasiKonsepIds, 'id_eksplorasi_konsep');
            
            \Log::info('Found eksplorasi_konsep IDs', ['ids' => $eksplorasiKonsepIdArray]);
            
            // Delete records that reference id_eksplorasi_konsep
            $deletedRows = [];
            
            if (!empty($eksplorasiKonsepIdArray)) {
                $placeholders = implode(',', array_fill(0, count($eksplorasiKonsepIdArray), '?'));
                
                $deletedRows['jawaban_pengetahuan_metakognisi'] = DB::delete(
                    "DELETE FROM jawaban_pengetahuan_metakognisi WHERE id_eksplorasi_konsep IN ($placeholders)", 
                    $eksplorasiKonsepIdArray
                );
                
                $deletedRows['soal_pengetahuan_metakognisi'] = DB::delete(
                    "DELETE FROM soal_pengetahuan_metakognisi WHERE id_eksplorasi_konsep IN ($placeholders)", 
                    $eksplorasiKonsepIdArray
                );
            }
            
            // Delete records that still reference id_materi directly
            $deletedRows['jawaban_mulai_dari_diri'] = DB::delete('DELETE FROM jawaban_mulai_dari_diri WHERE id_materi = ?', [$materiId]);
            $deletedRows['jawaban_demonstrasi_konseptual'] = DB::delete('DELETE FROM jawaban_demonstrasi_konseptual WHERE id_materi = ?', [$materiId]);
            $deletedRows['jawaban_elaborasi_pemahaman'] = DB::delete('DELETE FROM jawaban_elaborasi_pemahaman WHERE id_materi = ?', [$materiId]);
            $deletedRows['jawaban_refleksi_terbimbing'] = DB::delete('DELETE FROM jawaban_refleksi_terbimbing WHERE id_materi = ?', [$materiId]);
            $deletedRows['jawaban_ruang_kolaborasi'] = DB::delete('DELETE FROM jawaban_ruang_kolaborasi WHERE id_materi = ?', [$materiId]);
            $deletedRows['jawaban_perencanaan_refleksi_evaluasi'] = DB::delete('DELETE FROM jawaban_perencanaan_refleksi_evaluasi WHERE id_materi = ?', [$materiId]);
            $deletedRows['soal_perencanaan_refleksi_evaluasi'] = DB::delete('DELETE FROM soal_perencanaan_refleksi_evaluasi WHERE id_materi = ?', [$materiId]);
            $deletedRows['checklist_materi'] = DB::delete('DELETE FROM checklist_materi WHERE id_materi = ?', [$materiId]);
            
            // Delete eksplorasi_konsep records
            $deletedRows['eksplorasi_konsep'] = DB::delete('DELETE FROM eksplorasi_konsep WHERE id_materi = ?', [$materiId]);
            
            // Log what was deleted
            \Log::info('Deleted related records', $deletedRows);
            
            // Finally delete the material itself
            $deleted = DB::delete('DELETE FROM materi WHERE id_materi = ?', [$materiId]);
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            
            if ($deleted) {
                DB::commit();
                \Log::info('Material deleted successfully', ['material_id' => $materiId]);
                return response()->json([
                    'success' => true,
                    'message' => 'Materi berhasil dihapus'
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Materi tidak ditemukan'
                ], 404);
            }
            
        } catch (\Exception $e) {
            // Re-enable foreign key checks on error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                DB::rollback();
            } catch (\Exception $rollbackException) {
                \Log::error('Error during rollback', ['error' => $rollbackException->getMessage()]);
            }
            
            \Log::error('Error deleting material', [
                'material_id' => $materiId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus materi. Silakan cek log untuk detail.'
            ], 500);
        }
    }

    /**
     * Update Pengalaman Belajar (Teacher only)
     */
    public function updatePengalamanBelajar(Request $request, $courseId, $modulId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'pengalaman_belajar' => 'required|string'
        ]);
        
        DB::table('modul')
            ->where('id_modul', $modulId)
            ->update([
                'pengalaman_belajar' => $request->pengalaman_belajar,
                'updated_at' => now()
            ]);
            
        return redirect()->back()->with('success', 'Pengalaman Belajar berhasil diperbarui');
    }
    
    /**
     * Complete Pengalaman Belajar (Student only)
     */
    public function completePengalamanBelajar($courseId, $modulId)
    {
        if ($this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Only students can mark completion');
        }
        
        $userId = Auth::user()->id_user;
        
        DB::table('checklist_modul')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->update([
                'pengalaman_belajar' => 'Y',
                'updated_at' => now()
            ]);
            
        return redirect()->back()->with('success', 'Pengalaman Belajar telah selesai');
    }
    
    /**
     * Complete Material (Student only)
     */
    public function completeMaterial($courseId, $modulId)
    {
        if ($this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Only students can mark completion');
        }
        
        $userId = Auth::user()->id_user;
        
        // Check if pengalaman_belajar is completed
        $checklist = DB::table('checklist_modul')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->first();
            
        if (!$checklist || $checklist->pengalaman_belajar != 'Y') {
            return redirect()->back()->with('error', 'Selesaikan pengalaman belajar terlebih dahulu');
        }
        
        DB::table('checklist_modul')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->update([
                'materi' => 'Y',
                'updated_at' => now()
            ]);
            
        return redirect()->back()->with('success', 'Materi telah selesai');
    }
    
    /**
     * Upload Koneksi Materi Soal (Teacher only)
     */
    public function uploadKoneksiMateriSoal(Request $request, $courseId, $modulId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'file_soal' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);
        
        // Delete old file if exists
        $oldModule = DB::table('modul')->where('id_modul', $modulId)->first();
        if ($oldModule && $oldModule->koneksi_materi) {
            Storage::disk('public')->delete($oldModule->koneksi_materi);
        }
        
        // Upload new file
        $path = $request->file('file_soal')->store('koneksi_materi/soal', 'public');
        
        DB::table('modul')
            ->where('id_modul', $modulId)
            ->update([
                'koneksi_materi' => $path,
                'updated_at' => now()
            ]);
            
        return redirect()->back()->with('success', 'File soal berhasil diupload');
    }
    
    /**
     * Upload Koneksi Materi Jawaban (Student only)
     */
    public function uploadKoneksiMateriJawaban(Request $request, $courseId, $modulId)
    {
        if ($this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Only students can submit answers');
        }
        
        $userId = Auth::user()->id_user;
        
        // Check if materi is completed
        $checklist = DB::table('checklist_modul')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->first();
            
        if (!$checklist || $checklist->materi != 'Y') {
            return redirect()->back()->with('error', 'Selesaikan materi terlebih dahulu');
        }
        
        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);
        
        // Upload file
        $path = $request->file('file_jawaban')->store('koneksi_materi/jawaban', 'public');
        
        // Check if answer already exists
        $existing = DB::table('jawaban_koneksi_materi')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->first();
            
        if ($existing) {
            // Delete old file and update
            if ($existing->jawaban) {
                Storage::disk('public')->delete($existing->jawaban);
            }
            
            DB::table('jawaban_koneksi_materi')
                ->where('id_jawaban_koneksi_materi', $existing->id_jawaban_koneksi_materi)
                ->update([
                    'jawaban' => $path,
                    'benar' => null, // Reset grade when updated
                    'updated_at' => now()
                ]);
        } else {
            // Insert new record
            DB::table('jawaban_koneksi_materi')->insert([
                'id_user' => $userId,
                'id_modul' => $modulId,
                'jawaban' => $path,
                'benar' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        // Update checklist
        DB::table('checklist_modul')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->update(['koneksi_materi' => 'Y']);
            
        return redirect()->back()->with('success', 'Jawaban berhasil diupload');
    }
    
    /**
     * Update Aksi Nyata Soal (Teacher only)
     */
    public function updateAksiNyataSoal(Request $request, $courseId, $modulId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'aksi_nyata' => 'required|string'
        ]);
        
        DB::table('modul')
            ->where('id_modul', $modulId)
            ->update([
                'aksi_nyata' => $request->aksi_nyata,
                'updated_at' => now()
            ]);
            
        return redirect()->back()->with('success', 'Soal Aksi Nyata berhasil diperbarui');
    }
    
    /**
     * Submit Aksi Nyata Jawaban (Student only)
     */
    public function submitAksiNyataJawaban(Request $request, $courseId, $modulId)
    {
        if ($this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Only students can submit answers');
        }
        
        $userId = Auth::user()->id_user;
        
        // Check if koneksi_materi is completed
        $checklist = DB::table('checklist_modul')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->first();
            
        if (!$checklist || $checklist->koneksi_materi != 'Y') {
            return redirect()->back()->with('error', 'Selesaikan koneksi materi terlebih dahulu');
        }
        
        $request->validate([
            'jawaban' => 'required|string'
        ]);
        
        // Check if answer already exists
        $existing = DB::table('jawaban_aksi_nyata')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->first();
            
        if ($existing) {
            // Update record
            DB::table('jawaban_aksi_nyata')
                ->where('id_jawaban_aksi_nyata', $existing->id_jawaban_aksi_nyata)
                ->update([
                    'jawaban' => $request->jawaban,
                    'benar' => null, // Reset grade when updated
                    'updated_at' => now()
                ]);
        } else {
            // Insert new record
            DB::table('jawaban_aksi_nyata')->insert([
                'id_user' => $userId,
                'id_modul' => $modulId,
                'jawaban' => $request->jawaban,
                'benar' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        // Update checklist
        DB::table('checklist_modul')
            ->where('id_user', $userId)
            ->where('id_modul', $modulId)
            ->update(['aksi_nyata' => 'Y']);
            
        return redirect()->back()->with('success', 'Jawaban berhasil disimpan');
    }
    
    /**
     * Get Student Answers for Grading (Teacher Only)
     */
    public function getStudentAnswers($courseId, $modulId, $type)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if (!in_array($type, ['koneksi_materi', 'aksi_nyata'])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }
        
        $table = $type === 'koneksi_materi' ? 'jawaban_koneksi_materi' : 'jawaban_aksi_nyata';
        $idField = $type === 'koneksi_materi' ? 'id_jawaban_koneksi_materi' : 'id_jawaban_aksi_nyata';
        
        $answers = DB::table($table)
            ->join('users', $table . '.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where($table . '.id_modul', $modulId)
            ->select(
                $table . '.' . $idField . ' as id',
                $table . '.jawaban',
                $table . '.benar',
                $table . '.created_at',
                'profile.nama as nama_siswa'
            )
            ->orderBy($table . '.created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'answers' => $answers
        ]);
    }
    
    /**
     * Grade Student Answer (Teacher Only)
     */
    public function gradeAnswer(Request $request, $courseId, $modulId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'answer_id' => 'required|integer',
            'grade' => 'required|in:Y,N',
            'type' => 'required|in:koneksi_materi,aksi_nyata'
        ]);
        
        $table = $request->type === 'koneksi_materi' ? 'jawaban_koneksi_materi' : 'jawaban_aksi_nyata';
        $idField = $request->type === 'koneksi_materi' ? 'id_jawaban_koneksi_materi' : 'id_jawaban_aksi_nyata';
        
        // Update grade
        $updated = DB::table($table)
            ->where($idField, $request->answer_id)
            ->where('id_modul', $modulId)
            ->update([
                'benar' => $request->grade,
                'updated_at' => now()
            ]);
        
        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Jawaban tidak ditemukan'
            ], 404);
        }
    }
}