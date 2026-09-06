<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MateriLihatController extends Controller
{
    /**
     * Check if user is teacher/admin
     */
    private function isTeacher($courseId)
    {
        $userId = Auth::user()->id_user;
        
        // Check if admin
        if (Auth::user()->is_admin == 'Y') {
            return true;
        }
        
        // Check if teacher of this course
        return DB::table('kelas_mp')
            ->where('id_kelas_mp', $courseId)
            ->where('id_user', $userId)
            ->where('aktif', 'Y')
            ->exists();
    }
    
    /**
     * Check if user is student
     */
    private function isStudent($courseId)
    {
        $userId = Auth::user()->id_user;

        return DB::table('kelas_mp')
            ->join('siswa_kelas', 'kelas_mp.id_kelas_ta', '=', 'siswa_kelas.id_kelas_ta')
            ->where('kelas_mp.id_kelas_mp', $courseId)
            ->where('siswa_kelas.id_user', $userId)
            ->where('siswa_kelas.aktif', 'Y')
            ->exists();
    }

    /**
     * Daftar id_user siswa yang terdaftar aktif di kelas (course) ini.
     * Dipakai untuk membatasi daftar penilaian hanya ke siswa kelas ini,
     * karena satu materi bisa dipakai beberapa kelas (mata pelajaran sama).
     */
    private function studentIdsForCourse($courseId)
    {
        return DB::table('siswa_kelas')
            ->join('kelas_mp', 'kelas_mp.id_kelas_ta', '=', 'siswa_kelas.id_kelas_ta')
            ->where('kelas_mp.id_kelas_mp', $courseId)
            ->where('siswa_kelas.aktif', 'Y')
            ->pluck('siswa_kelas.id_user')
            ->all();
    }
    
    /**
     * Display materi page
     */
    public function index($courseId, $materialId)
    {
        // Check access
        $isTeacher = $this->isTeacher($courseId);
        $isStudent = $this->isStudent($courseId);
        
        if (!$isTeacher && !$isStudent) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses');
        }
        
        $userId = Auth::user()->id_user;
        
        // Get material data
        $material = DB::table('materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->where('materi.id_materi', $materialId)
            ->select('materi.*', 'modul.nama_modul', 'modul.id_modul')
            ->first();
            
        if (!$material) {
            return redirect()->back()->with('error', 'Materi tidak ditemukan');
        }
        
        // Data untuk view
        $data = [
            'material' => $material,
            'courseId' => $courseId,
            'isTeacher' => $isTeacher,
            'isStudent' => $isStudent,
        ];
        
        // Jika siswa, ambil data checklist dan jawaban
        if ($isStudent) {
            $data['checklist'] = $this->getChecklist($userId, $materialId);
            $data['studentAnswers'] = $this->getStudentAnswers($userId, $materialId);
        }
        
        // Ambil data eksplorasi konsep
        $data['eksplorasiKonsep'] = DB::table('eksplorasi_konsep')
            ->where('id_materi', $materialId)
            ->first();
            
        // Ambil soal metakognisi jika ada eksplorasi konsep
        if ($data['eksplorasiKonsep']) {
            $data['soalMetakognisi'] = DB::table('soal_pengetahuan_metakognisi')
                ->where('id_eksplorasi_konsep', $data['eksplorasiKonsep']->id_eksplorasi_konsep)
                ->first();
                
            if ($isStudent) {
                $data['jawabanMetakognisi'] = DB::table('jawaban_pengetahuan_metakognisi')
                    ->where('id_eksplorasi_konsep', $data['eksplorasiKonsep']->id_eksplorasi_konsep)
                    ->where('id_user', $userId)
                    ->first();
            }
        }
        
        // Ambil data soal dan jawaban PRE
        $data['soalPRE'] = $this->getSoalPRE($materialId);
        if ($isStudent) {
            $data['jawabanPRE'] = $this->getJawabanPRE($materialId, $userId);
        }
        
        return view('mata_pelajaran.materi_lihat', $data);
    }
    
    /**
     * Get student checklist
     */
    private function getChecklist($userId, $materialId)
    {
        $checklist = DB::table('checklist_materi')
            ->where('id_user', $userId)
            ->where('id_materi', $materialId)
            ->first();
            
        if (!$checklist) {
            // Create new checklist
            DB::table('checklist_materi')->insert([
                'id_user' => $userId,
                'id_materi' => $materialId,
                'mulai_dari_diri' => 'N',
                'eksplorasi_konsep' => 'N',
                'ruang_kolaborasi' => 'N',
                'refleksi_terbimbing' => 'N',
                'demonstrasi_konseptual' => 'N',
                'elaborasi_pemahaman' => 'N',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return DB::table('checklist_materi')
                ->where('id_user', $userId)
                ->where('id_materi', $materialId)
                ->first();
        }
        
        return $checklist;
    }
    
    /**
     * Get student answers for all components
     */
    private function getStudentAnswers($userId, $materialId)
    {
        $components = [
            'mulai_dari_diri',
            'ruang_kolaborasi',
            'demonstrasi_konseptual',
            'elaborasi_pemahaman'
        ];
        
        $answers = [];
        foreach ($components as $component) {
            $table = 'jawaban_' . $component;
            $answers[$component] = DB::table($table)
                ->where('id_user', $userId)
                ->where('id_materi', $materialId)
                ->first();
        }
        
        return $answers;
    }
    
    /**
     * Get soal PRE
     */
    private function getSoalPRE($materialId)
    {
        $soals = DB::table('soal_perencanaan_refleksi_evaluasi')
            ->where('id_materi', $materialId)
            ->get();
            
        $formatted = [];
        foreach ($soals as $soal) {
            if ($soal->perencanaan) {
                $formatted[$soal->jenis . '_perencanaan'] = $soal->perencanaan;
            }
            if ($soal->refleksi) {
                $formatted[$soal->jenis . '_refleksi'] = $soal->refleksi;
            }
            if ($soal->evaluasi) {
                $formatted[$soal->jenis . '_evaluasi'] = $soal->evaluasi;
            }
        }
        
        return $formatted;
    }
    
    /**
     * Get jawaban PRE
     */
    private function getJawabanPRE($materialId, $userId)
    {
        $jawabans = DB::table('jawaban_perencanaan_refleksi_evaluasi')
            ->where('id_materi', $materialId)
            ->where('id_user', $userId)
            ->get();
            
        $formatted = [];
        foreach ($jawabans as $jawaban) {
            if ($jawaban->perencanaan) {
                $formatted[$jawaban->jenis . '_perencanaan'] = [
                    'jawaban' => $jawaban->perencanaan,
                    'nilai' => $jawaban->nilai_perencanaan
                ];
            }
            if ($jawaban->refleksi) {
                $formatted[$jawaban->jenis . '_refleksi'] = [
                    'jawaban' => $jawaban->refleksi,
                    'nilai' => $jawaban->nilai_refleksi
                ];
            }
            if ($jawaban->evaluasi) {
                $formatted[$jawaban->jenis . '_evaluasi'] = [
                    'jawaban' => $jawaban->evaluasi,
                    'nilai' => $jawaban->nilai_evaluasi
                ];
            }
        }
        
        return $formatted;
    }
    
    /**
     * AJAX: Get list of student answers for grading
     */
    public function getAnswersForGrading(Request $request, $courseId, $materialId)
    {
        if (!$this->isTeacher($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $component = $request->get('component');
        $type = $request->get('type', null);

        // Batasi hanya ke siswa kelas (course) ini
        $studentIds = $this->studentIdsForCourse($courseId);

        try {
            if ($type) {
                // Get PRE answers
                $answers = $this->getPREAnswersForGrading($materialId, $component, $type, $studentIds);
            } else if ($component === 'metakognisi') {
                // Get metakognisi answers
                $answers = $this->getMetakognisiAnswersForGrading($materialId, $studentIds);
            } else {
                // Get regular component answers
                $answers = $this->getRegularAnswersForGrading($materialId, $component, $studentIds);
            }
            
            return response()->json([
                'success' => true,
                'answers' => $answers
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting answers for grading', [
                'error' => $e->getMessage(),
                'component' => $component,
                'type' => $type
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan'
            ], 500);
        }
    }
    
    /**
     * Get regular component answers for grading
     */
    private function getRegularAnswersForGrading($materialId, $component, array $studentIds = null)
    {
        $componentMapping = [
            'mulai_dari_diri' => [
                'table' => 'jawaban_mulai_dari_diri',
                'pk' => 'id_jawaban_mulai_dari_diri'
            ],
            'ruang_kolaborasi' => [
                'table' => 'jawaban_ruang_kolaborasi',
                'pk' => 'id_jawaban_ruang_kolaborasi'
            ],
            'demonstrasi_konseptual' => [
                'table' => 'jawaban_demonstrasi_konseptual',
                'pk' => 'id_jawaban_demonstrasi_konseptual'
            ],
            'elaborasi_pemahaman' => [
                'table' => 'jawaban_elaborasi_pemahaman',
                'pk' => 'id_jawaban_elaborasi_pemahaman'
            ]
        ];
        
        if (!isset($componentMapping[$component])) {
            throw new \Exception('Invalid component');
        }
        
        $table = $componentMapping[$component]['table'];
        $pk = $componentMapping[$component]['pk'];
        
        return DB::table($table)
            ->join('users', $table . '.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where($table . '.id_materi', $materialId)
            ->when(is_array($studentIds), function ($q) use ($table, $studentIds) {
                $q->whereIn($table . '.id_user', $studentIds);
            })
            ->select(
                $table . '.' . $pk . ' as id',
                $table . '.jawaban',
                $table . '.nilai',
                $table . '.created_at',
                'profile.nama as nama_siswa',
                'users.id_user'
            )
            ->orderBy($table . '.created_at', 'desc')
            ->get();
    }

    /**
     * Get PRE answers for grading
     */
    private function getPREAnswersForGrading($materialId, $component, $type, array $studentIds = null)
    {
        return DB::table('jawaban_perencanaan_refleksi_evaluasi')
            ->join('users', 'jawaban_perencanaan_refleksi_evaluasi.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where('jawaban_perencanaan_refleksi_evaluasi.id_materi', $materialId)
            ->where('jawaban_perencanaan_refleksi_evaluasi.jenis', $component)
            ->whereNotNull('jawaban_perencanaan_refleksi_evaluasi.' . $type)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('jawaban_perencanaan_refleksi_evaluasi.id_user', $studentIds);
            })
            ->select(
                'jawaban_perencanaan_refleksi_evaluasi.id_jawaban_perencanaan_refleksi_evaluasi as id',
                'jawaban_perencanaan_refleksi_evaluasi.' . $type . ' as jawaban',
                'jawaban_perencanaan_refleksi_evaluasi.nilai_' . $type . ' as nilai',
                'jawaban_perencanaan_refleksi_evaluasi.created_at',
                'profile.nama as nama_siswa',
                'users.id_user'
            )
            ->orderBy('jawaban_perencanaan_refleksi_evaluasi.created_at', 'desc')
            ->get();
    }
    
    /**
     * Get metakognisi answers for grading
     */
    private function getMetakognisiAnswersForGrading($materialId, array $studentIds = null)
    {
        $eksplorasiKonsep = DB::table('eksplorasi_konsep')
            ->where('id_materi', $materialId)
            ->first();

        if (!$eksplorasiKonsep) {
            return [];
        }

        return DB::table('jawaban_pengetahuan_metakognisi')
            ->join('users', 'jawaban_pengetahuan_metakognisi.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where('jawaban_pengetahuan_metakognisi.id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('jawaban_pengetahuan_metakognisi.id_user', $studentIds);
            })
            ->select(
                'jawaban_pengetahuan_metakognisi.id_jawaban_pengetahuan_metakognisi as id',
                'jawaban_pengetahuan_metakognisi.deklaratif',
                'jawaban_pengetahuan_metakognisi.nilai_deklaratif',
                'jawaban_pengetahuan_metakognisi.prosedural',
                'jawaban_pengetahuan_metakognisi.nilai_prosedural',
                'jawaban_pengetahuan_metakognisi.kondisional',
                'jawaban_pengetahuan_metakognisi.nilai_kondisional',
                'jawaban_pengetahuan_metakognisi.created_at',
                'profile.nama as nama_siswa',
                'users.id_user'
            )
            ->orderBy('jawaban_pengetahuan_metakognisi.created_at', 'desc')
            ->get();
    }
    
    /**
     * AJAX: Submit grade
     */
    public function submitGrade(Request $request, $courseId, $materialId)
    {
        if (!$this->isTeacher($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'answer_id' => 'required|integer',
            'nilai' => 'required|integer|min:0|max:100',
            'component' => 'required|string',
            'type' => 'nullable|string'
        ]);
        
        try {
            $component = $request->component;
            $type = $request->type;
            $answerId = $request->answer_id;
            $nilai = $request->nilai;

            // Hanya boleh menilai jawaban milik siswa kelas ini
            $studentIds = $this->studentIdsForCourse($courseId);

            if ($type && in_array($type, ['perencanaan', 'refleksi', 'evaluasi'])) {
                // Update PRE grade
                $this->updatePREGrade($answerId, $type, $nilai, $studentIds);
            } else if (in_array($type, ['deklaratif', 'prosedural', 'kondisional'])) {
                // Update metakognisi grade
                $this->updateMetakognisiGrade($answerId, $type, $nilai, $studentIds);
            } else {
                // Update regular grade
                $this->updateRegularGrade($component, $answerId, $nilai, $materialId, $studentIds);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error submitting grade', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update regular component grade
     */
    private function updateRegularGrade($component, $answerId, $nilai, $materialId, array $studentIds = null)
    {
        $componentMapping = [
            'mulai_dari_diri' => [
                'table' => 'jawaban_mulai_dari_diri',
                'pk' => 'id_jawaban_mulai_dari_diri'
            ],
            'ruang_kolaborasi' => [
                'table' => 'jawaban_ruang_kolaborasi',
                'pk' => 'id_jawaban_ruang_kolaborasi'
            ],
            'demonstrasi_konseptual' => [
                'table' => 'jawaban_demonstrasi_konseptual',
                'pk' => 'id_jawaban_demonstrasi_konseptual'
            ],
            'elaborasi_pemahaman' => [
                'table' => 'jawaban_elaborasi_pemahaman',
                'pk' => 'id_jawaban_elaborasi_pemahaman'
            ]
        ];
        
        if (!isset($componentMapping[$component])) {
            throw new \Exception('Invalid component');
        }
        
        $table = $componentMapping[$component]['table'];
        $pk = $componentMapping[$component]['pk'];
        
        $updated = DB::table($table)
            ->where($pk, $answerId)
            ->where('id_materi', $materialId)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('id_user', $studentIds);
            })
            ->update([
                'nilai' => $nilai,
                'updated_at' => now()
            ]);

        if (!$updated) {
            throw new \Exception('Jawaban tidak ditemukan');
        }
    }

    /**
     * Update PRE grade
     */
    private function updatePREGrade($answerId, $type, $nilai, array $studentIds = null)
    {
        $nilaiField = 'nilai_' . $type;

        $updated = DB::table('jawaban_perencanaan_refleksi_evaluasi')
            ->where('id_jawaban_perencanaan_refleksi_evaluasi', $answerId)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('id_user', $studentIds);
            })
            ->update([
                $nilaiField => $nilai,
                'updated_at' => now()
            ]);

        if (!$updated) {
            throw new \Exception('Jawaban tidak ditemukan');
        }
    }

    /**
     * Update metakognisi grade
     */
    private function updateMetakognisiGrade($answerId, $type, $nilai, array $studentIds = null)
    {
        $nilaiField = 'nilai_' . $type;

        $updated = DB::table('jawaban_pengetahuan_metakognisi')
            ->where('id_jawaban_pengetahuan_metakognisi', $answerId)
            ->when(is_array($studentIds), function ($q) use ($studentIds) {
                $q->whereIn('id_user', $studentIds);
            })
            ->update([
                $nilaiField => $nilai,
                'updated_at' => now()
            ]);

        if (!$updated) {
            throw new \Exception('Jawaban tidak ditemukan');
        }
    }
}