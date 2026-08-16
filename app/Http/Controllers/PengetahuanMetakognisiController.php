<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengetahuanMetakognisiController extends Controller
{
    /**
     * Check if user has permission to manage (CRUD)
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
     * Upload Soal Pengetahuan Metakognisi (Teacher only)
     */
    public function uploadSoal(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'soal_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'type' => 'required|in:deklaratif,prosedural,kondisional'
        ]);
        
        try {
            // Get eksplorasi_konsep record
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$eksplorasiKonsep) {
                return redirect()->back()->with('error', 'Eksplorasi konsep tidak ditemukan');
            }
            
            $userId = Auth::user()->id_user;
            $type = $request->type;
            
            // Upload file
            $path = $request->file('soal_file')->store('soal_metakognisi', 'public');
            
            // Check if soal already exists
            $existingSoal = DB::table('soal_pengetahuan_metakognisi')
                ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                ->where('id_user', $userId)
                ->first();
                
            if ($existingSoal) {
                // Delete old file if exists
                if ($existingSoal->{$type}) {
                    Storage::disk('public')->delete($existingSoal->{$type});
                }
                
                // Update existing record
                DB::table('soal_pengetahuan_metakognisi')
                    ->where('id_soal_pengetahuan_metakognisi', $existingSoal->id_soal_pengetahuan_metakognisi)
                    ->update([
                        $type => $path,
                        'updated_at' => now()
                    ]);
            } else {
                // Insert new record
                DB::table('soal_pengetahuan_metakognisi')->insert([
                    'id_eksplorasi_konsep' => $eksplorasiKonsep->id_eksplorasi_konsep,
                    'id_user' => $userId,
                    $type => $path,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            return redirect()->back()->with('success', 'Soal ' . ucfirst($type) . ' berhasil diupload');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload Jawaban Pengetahuan Metakognisi (Student only)
     */
    public function uploadJawaban(Request $request, $courseId, $materialId)
    {
        if ($this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Only students can submit answers');
        }
        
        $request->validate([
            'jawaban_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'type' => 'required|in:deklaratif,prosedural,kondisional'
        ]);
        
        try {
            // Get eksplorasi_konsep record
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$eksplorasiKonsep) {
                return redirect()->back()->with('error', 'Eksplorasi konsep tidak ditemukan');
            }
            
            $userId = Auth::user()->id_user;
            $type = $request->type;
            
            // Upload file
            $path = $request->file('jawaban_file')->store('jawaban_metakognisi', 'public');
            
            // Check if jawaban already exists
            $existingJawaban = DB::table('jawaban_pengetahuan_metakognisi')
                ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                ->where('id_user', $userId)
                ->first();
                
            if ($existingJawaban) {
                // Delete old file if exists
                if ($existingJawaban->{$type}) {
                    Storage::disk('public')->delete($existingJawaban->{$type});
                }
                
                // Update existing record and reset grade
                $nilaiField = 'nilai_' . $type;
                DB::table('jawaban_pengetahuan_metakognisi')
                    ->where('id_jawaban_pengetahuan_metakognisi', $existingJawaban->id_jawaban_pengetahuan_metakognisi)
                    ->update([
                        $type => $path,
                        $nilaiField => null, // Reset grade when answer is updated
                        'updated_at' => now()
                    ]);
            } else {
                // Insert new record
                DB::table('jawaban_pengetahuan_metakognisi')->insert([
                    'id_eksplorasi_konsep' => $eksplorasiKonsep->id_eksplorasi_konsep,
                    'id_user' => $userId,
                    $type => $path,
                    'nilai_deklaratif' => null,
                    'nilai_prosedural' => null,
                    'nilai_kondisional' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            return redirect()->back()->with('success', 'Jawaban ' . ucfirst($type) . ' berhasil diupload');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete Soal Pengetahuan Metakognisi (Teacher only)
     */
    public function deleteSoal(Request $request, $courseId, $materialId, $type)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if (!in_array($type, ['deklaratif', 'prosedural', 'kondisional'])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }
        
        try {
            // Get eksplorasi_konsep record
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$eksplorasiKonsep) {
                return response()->json(['error' => 'Eksplorasi konsep tidak ditemukan'], 404);
            }
            
            $userId = Auth::user()->id_user;
            
            // Get soal record
            $soal = DB::table('soal_pengetahuan_metakognisi')
                ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                ->where('id_user', $userId)
                ->first();
                
            if (!$soal || !$soal->{$type}) {
                return response()->json(['error' => 'Soal tidak ditemukan'], 404);
            }
            
            // Delete file
            Storage::disk('public')->delete($soal->{$type});
            
            // Update record to set field to null
            DB::table('soal_pengetahuan_metakognisi')
                ->where('id_soal_pengetahuan_metakognisi', $soal->id_soal_pengetahuan_metakognisi)
                ->update([
                    $type => null,
                    'updated_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Soal ' . ucfirst($type) . ' berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Student Answers for Pengetahuan Metakognisi (Teacher Only)
     */
    public function getStudentAnswers($courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            // Get eksplorasi_konsep record
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$eksplorasiKonsep) {
                return response()->json(['error' => 'Eksplorasi konsep tidak ditemukan'], 404);
            }
            
            // Get student answers with profile info
            $answers = DB::table('jawaban_pengetahuan_metakognisi')
                ->join('users', 'jawaban_pengetahuan_metakognisi.id_user', '=', 'users.id_user')
                ->join('profile', 'users.id_user', '=', 'profile.id_user')
                ->where('jawaban_pengetahuan_metakognisi.id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                ->select(
                    'jawaban_pengetahuan_metakognisi.*',
                    'profile.nama as nama_siswa'
                )
                ->orderBy('profile.nama', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'answers' => $answers
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grade Student Answer for Pengetahuan Metakognisi (Teacher Only)
     */
    public function gradeAnswer(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'answer_id' => 'required|integer',
            'component' => 'required|string',
            'nilai' => 'required|integer|min:0|max:100'
        ]);
        
        try {
            // Extract type from component (e.g., 'metakognisi_deklaratif' -> 'deklaratif')
            $component = $request->component;
            if (!str_starts_with($component, 'metakognisi_')) {
                return response()->json(['error' => 'Invalid component'], 400);
            }
            
            $type = str_replace('metakognisi_', '', $component);
            if (!in_array($type, ['deklaratif', 'prosedural', 'kondisional'])) {
                return response()->json(['error' => 'Invalid type'], 400);
            }
            
            $nilaiField = 'nilai_' . $type;
            
            // Update grade
            $updated = DB::table('jawaban_pengetahuan_metakognisi')
                ->where('id_jawaban_pengetahuan_metakognisi', $request->answer_id)
                ->update([
                    $nilaiField => $request->nilai,
                    'updated_at' => now()
                ]);
            
            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penilaian ' . ucfirst($type) . ' berhasil disimpan'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Jawaban tidak ditemukan'
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}