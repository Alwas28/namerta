<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    /**
     * Check if user has permission to manage materials (CRUD)
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
     * Check if user has permission to view materials
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
     * Display the specified material with 6 components
     */
    public function show($courseId, $materialId)
    {
        if (!$this->hasViewAccess($courseId)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke materi ini');
        }
        
        $userId = Auth::user()->id_user;
        
        // Get material with module info
        $material = DB::table('materi')
            ->join('modul', 'materi.id_modul', '=', 'modul.id_modul')
            ->where('materi.id_materi', $materialId)
            ->select('materi.*', 'modul.nama_modul', 'modul.id_modul')
            ->first();
            
        if (!$material) {
            return redirect()->back()->with('error', 'Materi tidak ditemukan');
        }
        
        // Get or create checklist for student
        $checklist = null;
        if (!$this->hasManageAccess($courseId)) {
            $checklist = DB::table('checklist_materi')
                ->where('id_user', $userId)
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$checklist) {
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
                
                $checklist = DB::table('checklist_materi')
                    ->where('id_user', $userId)
                    ->where('id_materi', $materialId)
                    ->first();
            }
        }
        
        // Get student answers for all components
        $answers = [];
        if (!$this->hasManageAccess($courseId)) {
            // Untuk tabel jawaban komponen utama, masih menggunakan nama asli
            $components = [
                'mulai_dari_diri', 
                'eksplorasi_konsep', 
                'ruang_kolaborasi',
                'demonstrasi_konseptual', 
                'elaborasi_pemahaman'  // Tetap pakai nama asli untuk tabel jawaban
            ];
            
            foreach ($components as $component) {
                if ($component === 'eksplorasi_konsep') {
                    $answers[$component] = null;
                    continue;
                }
            
                $table = 'jawaban_' . $component;
                $answer = DB::table($table)
                    ->where('id_user', $userId)
                    ->where('id_materi', $materialId)
                    ->first();
                $answers[$component] = $answer;
            }
        }

        $canManage = $this->hasManageAccess($courseId);
        
        // Ambil data eksplorasi konsep
        $eksplorasiKonsep = null;
        if ($canManage) {
            // Untuk guru/admin, ambil eksplorasi konsep yang dibuat oleh guru
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
        } else {
            // Untuk siswa, ambil eksplorasi konsep milik materi ini.
            // Data eksplorasi_konsep selalu 1 baris per id_materi dan hanya bisa
            // dibuat/diubah guru (updateEksplorasiKonsep dijaga hasManageAccess),
            // jadi tidak perlu filter role — filter lama gagal saat guru tidak
            // punya baris di user_roles sehingga materi tidak tampil ke siswa.
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->orderByDesc('id_eksplorasi_konsep')
                ->first();
        }

        // ========== TAMBAHAN UNTUK PRE DATA ==========
        // Ambil data soal dan jawaban PRE
        $soalPRE = [];
        $jawabanPRE = [];

        if ($canManage) {
            // Untuk guru: ambil data soal PRE
            $soalPREData = $this->getSoalPREData($materialId);
            // Langsung assign hasil formatted dari getSoalPREData
            $soalPRE = $soalPREData;
        } else {
            // Untuk siswa: ambil data jawaban PRE milik siswa
            $jawabanPREData = $this->getJawabanPREData($materialId, $userId);
            // Langsung assign hasil formatted dari getJawabanPREData
            $jawabanPRE = $jawabanPREData;
            
            // Juga ambil data soal PRE untuk menampilkan soal
            $soalPREData = $this->getSoalPREData($materialId);
            // Langsung assign hasil formatted dari getSoalPREData
            $soalPRE = $soalPREData;
        }

        // Debug untuk memastikan data terkirim
        \Log::info('Final soalPRE data:', $soalPRE);
        \Log::info('Final jawabanPRE data:', $jawabanPRE);

        // dd($soalPRE, $jawabanPRE);


        // Get soal metakognisi
        $soalMetakognisi = [];
        $jawabanMetakognisi = null;

        if ($eksplorasiKonsep) {
            // Get soal for teacher
            $soal = DB::table('soal_pengetahuan_metakognisi')
                ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                ->first();
                
            if ($soal) {
                $soalMetakognisi = [
                    'deklaratif' => $soal->deklaratif,
                    'prosedural' => $soal->prosedural,
                    'kondisional' => $soal->kondisional
                ];
            }
            
            // Get jawaban for student
            if (!$canManage) {
                $jawabanMetakognisi = DB::table('jawaban_pengetahuan_metakognisi')
                    ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                    ->where('id_user', Auth::user()->id_user)
                    ->first();
            }
        }
        // ========== END TAMBAHAN ==========
        
        return view('mata_pelajaran.materi_show', compact(
            'material', 
            'courseId', 
            'canManage', 
            'checklist',
            'answers',
            'eksplorasiKonsep',
            'soalPRE',      // Data soal PRE
            'jawabanPRE',    // Data jawaban PRE
            'soalMetakognisi',      // Data soal Metakognisi
            'jawabanMetakognisi'    // Data jawaban Metakognisi
        ));
    }
    
    /**
     * Update soal essay metakognisi (Teacher only)
     */
    public function updateSoalMetakognisi(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'component' => 'required|in:metakognisi',
            'type' => 'required|in:deklaratif,prosedural,kondisional',
            'soal_text' => 'required|string'
        ]);
        
        $userId = Auth::id();
        $type = $request->type;
        
        try {
            // Dapatkan eksplorasi konsep
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$eksplorasiKonsep) {
                return redirect()->back()->with('error', 'Eksplorasi konsep belum tersedia');
            }
            
            // Cek apakah sudah ada data soal metakognisi
            $existing = DB::table('soal_pengetahuan_metakognisi')
                ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                ->where('id_user', $userId)
                ->first();
                
            if ($existing) {
                // Update data yang sudah ada
                DB::table('soal_pengetahuan_metakognisi')
                    ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                    ->where('id_user', $userId)
                    ->update([
                        $type => $request->soal_text,
                        'updated_at' => now()
                    ]);
                    
                $message = "Soal {$type} berhasil diperbarui";
            } else {
                // Insert data baru
                DB::table('soal_pengetahuan_metakognisi')->insert([
                    'id_eksplorasi_konsep' => $eksplorasiKonsep->id_eksplorasi_konsep,
                    'id_user' => $userId,
                    $type => $request->soal_text,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = "Soal {$type} berhasil disimpan";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error update soal metakognisi', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'material_id' => $materialId,
                'type' => $type
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Upload jawaban essay metakognisi (Student only)
     */
    public function uploadJawabanMetakognisi(Request $request, $courseId, $materialId)
    {
        // Pastikan siswa memiliki akses view
        if (!$this->hasViewAccess($courseId) || $this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'component' => 'required|in:metakognisi',
            'type' => 'required|in:deklaratif,prosedural,kondisional',
            'jawaban_text' => 'required|string'
        ]);
        
        $userId = Auth::id();
        $type = $request->type;
        
        try {
            // Dapatkan eksplorasi konsep
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$eksplorasiKonsep) {
                return redirect()->back()->with('error', 'Eksplorasi konsep belum tersedia');
            }
            
            // Cek apakah sudah ada data jawaban metakognisi
            $existing = DB::table('jawaban_pengetahuan_metakognisi')
                ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                ->where('id_user', $userId)
                ->first();
                
            if ($existing) {
                // Update data yang sudah ada
                $updateData = [
                    $type => $request->jawaban_text,
                    'updated_at' => now()
                ];
                
                // Reset nilai ketika update jawaban
                $nilaiField = 'nilai_' . $type;
                $updateData[$nilaiField] = null;
                
                DB::table('jawaban_pengetahuan_metakognisi')
                    ->where('id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
                    ->where('id_user', $userId)
                    ->update($updateData);
                    
                $message = "Jawaban {$type} berhasil diperbarui";
            } else {
                // Insert data baru
                DB::table('jawaban_pengetahuan_metakognisi')->insert([
                    'id_eksplorasi_konsep' => $eksplorasiKonsep->id_eksplorasi_konsep,
                    'id_user' => $userId,
                    $type => $request->jawaban_text,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = "Jawaban {$type} berhasil disimpan";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error upload jawaban metakognisi', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'material_id' => $materialId,
                'type' => $type
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Get student metakognisi answers for grading (Teacher only)
     */
    public function getStudentMetakognisiAnswers($courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            // Dapatkan eksplorasi konsep
            $eksplorasiKonsep = DB::table('eksplorasi_konsep')
                ->where('id_materi', $materialId)
                ->first();
                
            if (!$eksplorasiKonsep) {
                return response()->json(['error' => 'Eksplorasi konsep tidak ditemukan'], 404);
            }
            
            $answers = DB::table('jawaban_pengetahuan_metakognisi')
                ->join('users', 'jawaban_pengetahuan_metakognisi.id_user', '=', 'users.id_user')
                ->join('profile', 'users.id_user', '=', 'profile.id_user')
                ->where('jawaban_pengetahuan_metakognisi.id_eksplorasi_konsep', $eksplorasiKonsep->id_eksplorasi_konsep)
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
                    'jawaban_pengetahuan_metakognisi.id_user'
                )
                ->orderBy('jawaban_pengetahuan_metakognisi.created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'answers' => $answers
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error get student metakognisi answers', [
                'error' => $e->getMessage(),
                'material_id' => $materialId
            ]);
            
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    /**
     * Grade student metakognisi answer (Teacher only)
     */
    public function gradeMetakognisiAnswer(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'user_id' => 'required|integer',
            'component' => 'required|string',
            'nilai' => 'required|integer|min:0|max:100'
        ]);
        
        // Extract type from component (format: metakognisi_deklaratif)
        $componentParts = explode('_', $request->component);
        if (count($componentParts) !== 2 || $componentParts[0] !== 'metakognisi') {
            return response()->json(['error' => 'Invalid component format'], 400);
        }
        
        $type = $componentParts[1];
        $validTypes = ['deklaratif', 'prosedural', 'kondisional'];
        
        if (!in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid type'], 400);
        }
        
        try {
            $nilaiField = 'nilai_' . $type;
            
            // Update nilai
            $updated = DB::table('jawaban_pengetahuan_metakognisi')
                ->where('id_jawaban_pengetahuan_metakognisi', $request->user_id)
                ->update([
                    $nilaiField => $request->nilai,
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
            
        } catch (\Exception $e) {
            \Log::error('Error grade metakognisi answer', [
                'error' => $e->getMessage(),
                'user_id' => $request->user_id,
                'type' => $type
            ]);
            
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }
    /**
     * Store a newly created material
     */
    public function store(Request $request, $courseId, $modulId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'nama_materi' => 'required|string|max:255'
        ]);
        
        DB::table('materi')->insert([
            'id_modul' => $modulId,
            'nama_materi' => $request->nama_materi,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->back()->with('success', 'Materi berhasil ditambahkan');
    }
    
    /**
     * Update the specified material
     */
    public function update(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'nama_materi' => 'required|string|max:255'
        ]);
        
        DB::table('materi')
            ->where('id_materi', $materialId)
            ->update([
                'nama_materi' => $request->nama_materi,
                'updated_at' => now()
            ]);
        
        return redirect()->back()->with('success', 'Materi berhasil diperbarui');
    }
    
    /**
     * Remove the specified material
     */
    public function destroy($courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        // Delete related checklist entries
        DB::table('checklist_materi')->where('id_materi', $materialId)->delete();
        
        // Delete related answer files and records - hapus refleksi_terbimbing
        $components = [
            'mulai_dari_diri', 
            'eksplorasi_konsep', 
            'demonstrasi_konseptual', 
            'elaborasi_pemahaman'
        ];
        
        foreach ($components as $component) {
            $table = 'jawaban_' . $component;
            
            // Delete files first
            $answers = DB::table($table)->where('id_materi', $materialId)->get();
            foreach ($answers as $answer) {
                if ($answer->jawaban && Storage::disk('public')->exists($answer->jawaban)) {
                    Storage::disk('public')->delete($answer->jawaban);
                }
            }
            
            // Delete records
            DB::table($table)->where('id_materi', $materialId)->delete();
        }
        
        // Delete the material
        DB::table('materi')->where('id_materi', $materialId)->delete();
        
        return redirect()->back()->with('success', 'Materi berhasil dihapus');
    }
    
    /**
     * Upload soal untuk komponen materi (Teacher only)
     */
    public function uploadSoal(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'component' => 'required|in:mulai_dari_diri,ruang_kolaborasi,demonstrasi_konseptual,elaborasi_pemahaman', // hapus refleksi_terbimbing
            'file_soal' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);
        
        $component = $request->component;
        
        // Delete old file if exists
        $oldMaterial = DB::table('materi')->where('id_materi', $materialId)->first();
        if ($oldMaterial && $oldMaterial->$component) {
            Storage::disk('public')->delete($oldMaterial->$component);
        }
        
        // Upload new file
        $path = $request->file('file_soal')->store("materi/{$component}/soal", 'public');
        
        DB::table('materi')
            ->where('id_materi', $materialId)
            ->update([
                $component => $path,
                'updated_at' => now()
            ]);
            
        return redirect()->back()->with('success', 'File soal berhasil diupload');
    }
    
    /**
     * Upload jawaban untuk komponen materi (Student only)
     */
    function uploadJawaban(Request $request, $courseId, $materialId)
    {
        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'component' => 'required|string'
        ]);

        $component = $request->component;
        $userId = Auth::id();
        
        // Hapus refleksi_terbimbing dari allowedComponents
        $allowedComponents = ['mulai_dari_diri', 'ruang_kolaborasi', 'demonstrasi_konseptual', 'elaborasi_pemahaman'];

        if (!in_array($component, $allowedComponents)) {
            return response()->json(['success' => false, 'message' => 'Komponen tidak valid'], 400);
        }

        if ($request->hasFile('file_jawaban')) {
            $file = $request->file('file_jawaban');
            $path = $file->store("jawaban/{$component}", 'public');

            $table = 'jawaban_' . $component;

            // Cek apakah sudah ada jawaban
            $existingAnswer = DB::table($table)
                ->where('id_user', $userId)
                ->where('id_materi', $materialId)
                ->first();

            if ($existingAnswer) {
                // Update jawaban yang sudah ada
                DB::table($table)
                    ->where('id_user', $userId)
                    ->where('id_materi', $materialId)
                    ->update([
                        'jawaban' => $path,
                        'nilai' => null,
                        'updated_at' => now()
                    ]);
            } else {
                // Insert jawaban baru
                DB::table($table)->insert([
                    'id_user' => $userId,
                    'id_materi' => $materialId,
                    'jawaban' => $path,
                    'nilai' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Update checklist menjadi Y setelah upload jawaban
            DB::table('checklist_materi')
                ->where('id_user', $userId)
                ->where('id_materi', $materialId)
                ->update([
                    $component => 'Y',
                    'updated_at' => now()
                ]);

            return redirect()->back()->with('success', 'Jawaban berhasil diupload');
        }

        return redirect()->back()->with('error', 'Gagal upload file');
    }
    
    /**
     * Complete component (for ruang_kolaborasi - Student only)
     */
    public function completeComponent(Request $request, $courseId, $materialId)
    {
        if ($this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Only students can mark completion');
        }
        
        $userId = Auth::user()->id_user;
        
        $request->validate([
            'component' => 'required|in:ruang_kolaborasi'
        ]);
        
        $component = $request->component;
        
        // Check prerequisites
        $checklist = DB::table('checklist_materi')
            ->where('id_user', $userId)
            ->where('id_materi', $materialId)
            ->first();
            
        if (!$checklist || $checklist->eksplorasi_konsep != 'Y') {
            return redirect()->back()->with('error', 'Selesaikan eksplorasi konsep terlebih dahulu');
        }
        
        // Update checklist
        DB::table('checklist_materi')
            ->where('id_user', $userId)
            ->where('id_materi', $materialId)
            ->update([
                $component => 'Y',
                'updated_at' => now()
            ]);
            
        return redirect()->back()->with('success', 'Komponen berhasil diselesaikan');
    }

    /**
     * Update eksplorasi konsep dengan redirect response
     */
    public function updateEksplorasiKonsep(Request $request, $courseId, $materialId)
    {
        // Validasi akses
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        // Validasi input. isi_materi tidak wajib supaya guru bisa menyimpan
        // eksplorasi konsep yang hanya berupa PDF dan/atau video.
        $request->validate([
            'isi_materi' => 'nullable|string',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
            'video_link' => 'nullable|url|max:500',
            'video_link2' => 'nullable|url|max:500',
            'video_link3' => 'nullable|url|max:500'
        ]);

        $userId = Auth::id();

        // Cek apakah sudah ada data berdasarkan id_materi
        $existing = DB::table('eksplorasi_konsep')
            ->where('id_materi', $materialId)
            ->first();

        // Minimal salah satu konten harus terisi (teks / PDF baru / video / PDF lama)
        if (! $request->filled('isi_materi')
            && ! $request->hasFile('pdf_file')
            && ! $request->filled('video_link')
            && ! ($existing && $existing->pdf)) {
            return redirect()->back()
                ->with('error', 'Isi minimal salah satu: konten materi, PDF, atau link video.');
        }

        try {
            // Siapkan data untuk disimpan
            $data = [
                'isi_materi' => $request->isi_materi,
                'updated_at' => now()
            ];

            // Handle PDF upload jika ada
            if ($request->hasFile('pdf_file')) {
                // Hapus file PDF lama jika ada
                if ($existing && $existing->pdf && Storage::disk('public')->exists($existing->pdf)) {
                    Storage::disk('public')->delete($existing->pdf);
                }
                
                $pdfPath = $request->file('pdf_file')->store('eksplorasi_konsep/pdf', 'public');
                $data['pdf'] = $pdfPath;
            }

            // Handle video link - simpan ke field 'video' yang sudah ada
            if ($request->filled('video_link')) {
                $data['video'] = $request->video_link;
                $data['video2'] = $request->video_link2;
                $data['video3'] = $request->video_link3;
            }

            if ($existing) {
                // Update data yang sudah ada
                DB::table('eksplorasi_konsep')
                    ->where('id_materi', $materialId)
                    ->update($data);
                    
                $message = 'Eksplorasi konsep berhasil diperbarui';
            } else {
                // Insert data baru
                $data['id_materi'] = $materialId;
                $data['id_user'] = $userId;
                $data['created_at'] = now();
                
                DB::table('eksplorasi_konsep')->insert($data);
                
                $message = 'Eksplorasi konsep berhasil disimpan';
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Exception in updateEksplorasiKonsep', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function completeEksplorasiKonsep(Request $request, $courseId, $materialId)
    {
        $userId = Auth::id();

        // Update checklist
        DB::table('checklist_materi')
            ->where('id_user', $userId)
            ->where('id_materi', $materialId)
            ->update([
                'eksplorasi_konsep' => 'Y',
                'ruang_kolaborasi' => 'Y',
                'updated_at' => now()
            ]);

            return redirect()->back()->with('success', 'Eksplorasi konsep ditandai sebagai selesai dan bisa lanjut ke ruang kolaborasi untuk diskusi');

    }
    
    
    /**
     * Grade student answer (Teacher only)
     */
    function gradeAnswer(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        dd($request->all());

        $request->validate([
            'answer_id' => 'required|integer',
            'grade' => 'required|integer|min:0|max:100',
            'component' => 'required|in:mulai_dari_diri,demonstrasi_konseptual,elaborasi_pemahaman' // hapus refleksi_terbimbing
        ]);
        
        $table = 'jawaban_' . $request->component;
        $idField = 'id_jawaban_' . $request->component;
        
        // Update grade
        $updated = DB::table($table)
            ->where($idField, $request->answer_id)
            ->where('id_materi', $materialId)
            ->update([
                'nilai' => $request->grade,
                // 'nilai' => 90,
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

    /**
     * Upload soal perencanaan/refleksi/evaluasi (Teacher only)
     */
    public function uploadSoalPRE(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'component' => 'required|in:ruang_kolaborasi,demonstrasi_konseptual,elaborasi_pemahaman', // hapus refleksi_terbimbing
            'type' => 'required|in:perencanaan,refleksi,evaluasi',
            'soal_file' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);
        
        $userId = Auth::id();
        $component = $request->component;
        $type = $request->type;
        
        try {
            // Upload file
            $fileName = time() . '_' . $type . '_' . $request->file('soal_file')->getClientOriginalName();
            $path = $request->file('soal_file')->storeAs("soal_pre/{$component}/{$type}", $fileName, 'public');
            
            // Cek apakah sudah ada data
            $existing = DB::table('soal_perencanaan_refleksi_evaluasi')
                ->where('id_materi', $materialId)
                ->where('id_user', $userId)
                ->where('jenis', $component)
                ->first();
                
            if ($existing) {
                // Hapus file lama jika ada
                if ($existing->$type && Storage::disk('public')->exists($existing->$type)) {
                    Storage::disk('public')->delete($existing->$type);
                }
                
                // Update data yang sudah ada
                DB::table('soal_perencanaan_refleksi_evaluasi')
                    ->where('id_materi', $materialId)
                    ->where('id_user', $userId)
                    ->where('jenis', $component)
                    ->update([
                        $type => $path,
                        'updated_at' => now()
                    ]);
                    
                $message = "Soal {$type} berhasil diperbarui";
            } else {
                // Insert data baru
                DB::table('soal_perencanaan_refleksi_evaluasi')->insert([
                    'id_materi' => $materialId,
                    'id_user' => $userId,
                    'jenis' => $component,
                    $type => $path,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = "Soal {$type} berhasil diupload";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error upload soal PRE', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'material_id' => $materialId,
                'component' => $component,
                'type' => $type
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
        }
    }

    /**
     * Upload jawaban perencanaan/refleksi/evaluasi (Student only)
     */
    public function uploadJawabanPRE(Request $request, $courseId, $materialId)
    {
        // Pastikan siswa memiliki akses view
        if (!$this->hasViewAccess($courseId) || $this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'component' => 'required|in:ruang_kolaborasi,demonstrasi_konseptual,elaborasi_pemahaman', // hapus refleksi_terbimbing
            'type' => 'required|in:perencanaan,refleksi,evaluasi',
            'jawaban_file' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);
        
        $userId = Auth::id();
        $component = $request->component;
        $type = $request->type;
        
        try {
            // Upload file
            $fileName = time() . '_' . $userId . '_' . $type . '_' . $request->file('jawaban_file')->getClientOriginalName();
            $path = $request->file('jawaban_file')->storeAs("jawaban_pre/{$component}/{$type}", $fileName, 'public');
            
            // Cek apakah sudah ada data
            $existing = DB::table('jawaban_perencanaan_refleksi_evaluasi')
                ->where('id_materi', $materialId)
                ->where('id_user', $userId)
                ->where('jenis', $component)
                ->first();
                
            if ($existing) {
                // Hapus file lama jika ada
                if ($existing->$type && Storage::disk('public')->exists($existing->$type)) {
                    Storage::disk('public')->delete($existing->$type);
                }
                
                // Update data yang sudah ada
                $updateData = [
                    $type => $path,
                    'updated_at' => now()
                ];
                
                // Reset nilai ketika upload ulang
                $nilaiField = 'nilai_' . $type;
                $updateData[$nilaiField] = null;
                
                DB::table('jawaban_perencanaan_refleksi_evaluasi')
                    ->where('id_materi', $materialId)
                    ->where('id_user', $userId)
                    ->where('jenis', $component)
                    ->update($updateData);
                    
                $message = "Jawaban {$type} berhasil diperbarui";
            } else {
                // Insert data baru
                DB::table('jawaban_perencanaan_refleksi_evaluasi')->insert([
                    'id_materi' => $materialId,
                    'id_user' => $userId,
                    'jenis' => $component,
                    $type => $path,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = "Jawaban {$type} berhasil diupload";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error upload jawaban PRE', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'material_id' => $materialId,
                'component' => $component,
                'type' => $type
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
        }
    }

    /**
     * Get soal PRE data for display (used in show method)
     */
    private function getSoalPREData($materialId, $component = null)
    {
        \Log::info('getSoalPREData called with materialId: ' . $materialId . ', component: ' . $component);
        
        $query = DB::table('soal_perencanaan_refleksi_evaluasi')
            ->where('id_materi', $materialId);
            
        if ($component) {
            $query->where('jenis', $component);
        }
        
        $results = $query->get();
        \Log::info('getSoalPREData raw results:', $results->toArray());
        
        $formatted = [];
        
        foreach ($results as $result) {
            \Log::info('Processing result:', [
                'jenis' => $result->jenis,
                'perencanaan' => $result->perencanaan,
                'refleksi' => $result->refleksi,
                'evaluasi' => $result->evaluasi
            ]);
            
            // Periksa setiap field dan simpan jika tidak null/kosong
            if ($result->perencanaan !== null && trim($result->perencanaan) !== '') {
                $key = $result->jenis . '_perencanaan';
                $formatted[$key] = $result->perencanaan;
                \Log::info('Added perencanaan with key: ' . $key);
            }
            
            if ($result->refleksi !== null && trim($result->refleksi) !== '') {
                $key = $result->jenis . '_refleksi';
                $formatted[$key] = $result->refleksi;
                \Log::info('Added refleksi with key: ' . $key);
            }
            
            if ($result->evaluasi !== null && trim($result->evaluasi) !== '') {
                $key = $result->jenis . '_evaluasi';
                $formatted[$key] = $result->evaluasi;
                \Log::info('Added evaluasi with key: ' . $key);
            }
        }
        
        \Log::info('getSoalPREData final formatted result:', $formatted);
        return $formatted;
    }

    /**
     * Get jawaban PRE data for student (used in show method) - DEBUG & FIXED VERSION
     */
    private function getJawabanPREData($materialId, $userId, $component = null)
    {
        \Log::info('getJawabanPREData called with materialId: ' . $materialId . ', userId: ' . $userId . ', component: ' . $component);
        
        $query = DB::table('jawaban_perencanaan_refleksi_evaluasi')
            ->where('id_materi', $materialId)
            ->where('id_user', $userId);
            
        if ($component) {
            $query->where('jenis', $component);
        }
        
        $results = $query->get();
        \Log::info('getJawabanPREData raw results:', $results->toArray());
        
        $formatted = [];
        
        foreach ($results as $result) {
            \Log::info('Processing jawaban result:', [
                'jenis' => $result->jenis,
                'perencanaan' => $result->perencanaan,
                'refleksi' => $result->refleksi,
                'evaluasi' => $result->evaluasi,
                'nilai_perencanaan' => $result->nilai_perencanaan,
                'nilai_refleksi' => $result->nilai_refleksi,
                'nilai_evaluasi' => $result->nilai_evaluasi
            ]);
            
            // Periksa setiap field jawaban dan simpan jika tidak null/kosong
            if ($result->perencanaan !== null && trim($result->perencanaan) !== '') {
                $key = $result->jenis . '_perencanaan';
                $formatted[$key] = [
                    'jawaban' => $result->perencanaan,
                    'nilai' => $result->nilai_perencanaan
                ];
                \Log::info('Added jawaban perencanaan with key: ' . $key);
            }
            
            if ($result->refleksi !== null && trim($result->refleksi) !== '') {
                $key = $result->jenis . '_refleksi';
                $formatted[$key] = [
                    'jawaban' => $result->refleksi,
                    'nilai' => $result->nilai_refleksi
                ];
                \Log::info('Added jawaban refleksi with key: ' . $key);
            }
            
            if ($result->evaluasi !== null && trim($result->evaluasi) !== '') {
                $key = $result->jenis . '_evaluasi';
                $formatted[$key] = [
                    'jawaban' => $result->evaluasi,
                    'nilai' => $result->nilai_evaluasi
                ];
                \Log::info('Added jawaban evaluasi with key: ' . $key);
            }
        }
        
        \Log::info('getJawabanPREData final formatted result:', $formatted);
        return $formatted;
    }

    /**
     * Get student answers for PRE components (Teacher only)
     */
    public function getStudentAnswersPRE($courseId, $materialId, $component, $type)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validComponents = ['ruang_kolaborasi', 'demonstrasi_konseptual', 'elaborasi_pemahaman'];
        $validTypes = ['perencanaan', 'refleksi', 'evaluasi'];
        
        if (!in_array($component, $validComponents) || !in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid component or type'], 400);
        }
        
        $answers = DB::table('jawaban_perencanaan_refleksi_evaluasi')
            ->join('users', 'jawaban_perencanaan_refleksi_evaluasi.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where('jawaban_perencanaan_refleksi_evaluasi.id_materi', $materialId)
            ->where('jawaban_perencanaan_refleksi_evaluasi.jenis', $component)
            ->whereNotNull('jawaban_perencanaan_refleksi_evaluasi.' . $type)
            ->select(
                'jawaban_perencanaan_refleksi_evaluasi.id_jawaban_perencanaan_refleksi_evaluasi as id',
                'jawaban_perencanaan_refleksi_evaluasi.' . $type . ' as jawaban',
                'jawaban_perencanaan_refleksi_evaluasi.nilai_' . $type . ' as nilai',
                'jawaban_perencanaan_refleksi_evaluasi.created_at',
                'profile.nama as nama_siswa',
                'jawaban_perencanaan_refleksi_evaluasi.id_user'
            )
            ->orderBy('jawaban_perencanaan_refleksi_evaluasi.created_at', 'desc')
            ->get();
        
        return response()->json([
            'success' => true,
            'answers' => $answers
        ]);
    }

    /**
     * Grade student answer for PRE components (Teacher only)
     */
    public function gradeAnswerPRE(Request $request, $courseId, $materialId)
    {
        if (!$this->hasManageAccess($courseId)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Validasi yang lebih fleksibel
        $request->validate([
            'answer_id' => 'required|integer',
            'grade' => 'required_without:nilai|integer|min:0|max:100',
            'nilai' => 'required_without:grade|integer|min:0|max:100',
            'component' => 'required|in:ruang_kolaborasi,demonstrasi_konseptual,elaborasi_pemahaman',
            'type' => 'required|in:perencanaan,refleksi,evaluasi'
        ]);
        
        // Gunakan parameter 'grade' atau 'nilai'
        $nilaiValue = $request->grade ?? $request->nilai;
        
        $type = $request->type;
        $nilaiField = 'nilai_' . $type;
        
        try {
            // Update nilai
            $updated = DB::table('jawaban_perencanaan_refleksi_evaluasi')
                ->where('id_jawaban_perencanaan_refleksi_evaluasi', $request->answer_id)
                ->where('id_materi', $materialId)
                ->where('jenis', $request->component)
                ->update([
                    $nilaiField => $nilaiValue,
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
        } catch (\Exception $e) {
            \Log::error('Error grading PRE answer:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update soal essay PRE (Teacher only)
     */
    public function updateSoalEssayPRE(Request $request, $courseId, $materialId)
    {
         if (!$this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'component' => 'required|in:ruang_kolaborasi,demonstrasi_konseptual,elaborasi_pemahaman', // hapus refleksi_terbimbing
            'type' => 'required|in:perencanaan,refleksi,evaluasi',
            'soal_text' => 'required|string'
        ]);
        
        $userId = Auth::id();
        $component = $request->component;
        $type = $request->type;
        
        try {
            // Cek apakah sudah ada data
            $existing = DB::table('soal_perencanaan_refleksi_evaluasi')
                ->where('id_materi', $materialId)
                ->where('id_user', $userId)
                ->where('jenis', $component)
                ->first();
                
            if ($existing) {
                // Update data yang sudah ada
                DB::table('soal_perencanaan_refleksi_evaluasi')
                    ->where('id_materi', $materialId)
                    ->where('id_user', $userId)
                    ->where('jenis', $component)
                    ->update([
                        $type => $request->soal_text,
                        'updated_at' => now()
                    ]);
                    
                $message = "Soal {$type} berhasil diperbarui";
            } else {
                // Insert data baru
                DB::table('soal_perencanaan_refleksi_evaluasi')->insert([
                    'id_materi' => $materialId,
                    'id_user' => $userId,
                    'jenis' => $component,
                    $type => $request->soal_text,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = "Soal {$type} berhasil disimpan";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error update soal essay PRE', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'material_id' => $materialId,
                'component' => $component,
                'type' => $type
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    /**
     * Submit jawaban essay PRE (Student only)
     */
    public function submitJawabanEssayPRE(Request $request, $courseId, $materialId)
    {
        // Pastikan siswa memiliki akses view
        if (!$this->hasViewAccess($courseId) || $this->hasManageAccess($courseId)) {
            return redirect()->back()->with('error', 'Unauthorized');
        }
        
        $request->validate([
            'component' => 'required|in:ruang_kolaborasi,demonstrasi_konseptual,elaborasi_pemahaman', // hapus refleksi_terbimbing
            'type' => 'required|in:perencanaan,refleksi,evaluasi',
            'jawaban_text' => 'required|string'
        ]);
        
        $userId = Auth::id();
        $component = $request->component;
        $type = $request->type;
        
        try {
            // Cek apakah sudah ada data
            $existing = DB::table('jawaban_perencanaan_refleksi_evaluasi')
                ->where('id_materi', $materialId)
                ->where('id_user', $userId)
                ->where('jenis', $component)
                ->first();
                
            if ($existing) {
                // Update data yang sudah ada
                $updateData = [
                    $type => $request->jawaban_text,
                    'updated_at' => now()
                ];
                
                // Reset nilai ketika update jawaban
                $nilaiField = 'nilai_' . $type;
                $updateData[$nilaiField] = null;
                
                DB::table('jawaban_perencanaan_refleksi_evaluasi')
                    ->where('id_materi', $materialId)
                    ->where('id_user', $userId)
                    ->where('jenis', $component)
                    ->update($updateData);
                    
                $message = "Jawaban {$type} berhasil diperbarui";
            } else {
                // Insert data baru
                DB::table('jawaban_perencanaan_refleksi_evaluasi')->insert([
                    'id_materi' => $materialId,
                    'id_user' => $userId,
                    'jenis' => $component,
                    $type => $request->jawaban_text,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = "Jawaban {$type} berhasil disimpan";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Error submit jawaban essay PRE', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'material_id' => $materialId,
                'component' => $component,
                'type' => $type
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    
/**
 * Grade student answer with numeric value (Teacher only)
 * Untuk komponen: mulai_dari_diri, ruang_kolaborasi, demonstrasi_konseptual, elaborasi_pemahaman
 */
public function gradeAnswerNumeric(Request $request, $courseId, $materialId)
{
    if (!$this->hasManageAccess($courseId)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // Validasi input
    try {
        $validated = $request->validate([
            'answer_id' => 'required|integer',
            'nilai' => 'required|integer|min:0|max:100',
            'component' => 'required|string|in:mulai_dari_diri,ruang_kolaborasi,demonstrasi_konseptual,elaborasi_pemahaman'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    }
    
    // Mapping table dan primary key yang benar
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
    
    $component = $request->component;
    
    if (!isset($componentMapping[$component])) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid component specified'
        ], 400);
    }
    
    $table = $componentMapping[$component]['table'];
    $primaryKey = $componentMapping[$component]['pk'];
    
    try {
        \Log::info('Attempting to grade answer', [
            'teacher_id' => Auth::id(),
            'material_id' => $materialId,
            'component' => $component,
            'answer_id' => $request->answer_id,
            'nilai' => $request->nilai,
            'table' => $table,
            'primary_key' => $primaryKey
        ]);
        
        // Check if the answer exists
        $exists = DB::table($table)
            ->where($primaryKey, $request->answer_id)
            ->where('id_materi', $materialId)
            ->exists();
            
        if (!$exists) {
            return response()->json([
                'success' => false,
                'message' => 'Jawaban tidak ditemukan'
            ], 404);
        }
        
        // Update nilai
        $updated = DB::table($table)
            ->where($primaryKey, $request->answer_id)
            ->where('id_materi', $materialId)
            ->update([
                'nilai' => $request->nilai,
                'updated_at' => now()
            ]);
        
        \Log::info('Grade update result', [
            'updated_rows' => $updated,
            'answer_id' => $request->answer_id,
            'nilai' => $request->nilai
        ]);
        
        if ($updated) {
            // Cek dan update checklist jika perlu
            $this->checkAndUpdateChecklist($materialId, $request->answer_id, $component);
            
            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan',
                'nilai' => $request->nilai,
                'component' => $component
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada perubahan yang dilakukan'
            ], 200);
        }
        
    } catch (\Exception $e) {
        \Log::error('Error grading answer', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'teacher_id' => Auth::id(),
            'material_id' => $materialId,
            'component' => $component,
            'answer_id' => $request->answer_id
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Get student answers for grading (Teacher only) - UPDATED VERSION
 * Now returns proper data for numeric grading
 */
public function getStudentAnswers($courseId, $materialId, $component)
{
    if (!$this->hasManageAccess($courseId)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // Validasi komponen dengan lebih ketat
    $validComponents = [
        'mulai_dari_diri', 
        'ruang_kolaborasi',
        'demonstrasi_konseptual', 
        'elaborasi_pemahaman'
    ];
    
    if (!in_array($component, $validComponents)) {
        \Log::error('Invalid component selected', [
            'component' => $component,
            'valid_components' => $validComponents,
            'course_id' => $courseId,
            'material_id' => $materialId
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Invalid component selected',
            'component_received' => $component,
            'valid_components' => $validComponents
        ], 400);
    }
    
    // Mapping yang benar untuk tabel dan primary key
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
    
    $table = $componentMapping[$component]['table'];
    $primaryKey = $componentMapping[$component]['pk'];
    
    try {
        $answers = DB::table($table)
            ->join('users', $table . '.id_user', '=', 'users.id_user')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where($table . '.id_materi', $materialId)
            ->select(
                $table . '.' . $primaryKey . ' as id',
                $table . '.jawaban',
                $table . '.nilai',
                $table . '.created_at',
                $table . '.updated_at',
                'profile.nama as nama_siswa',
                'users.username',
                $table . '.id_user'
            )
            ->orderBy($table . '.created_at', 'desc')
            ->get();
        
        // Add statistics
        $stats = [
            'total' => $answers->count(),
            'graded' => $answers->filter(function($answer) {
                return $answer->nilai !== null;
            })->count(),
            'average' => $answers->filter(function($answer) {
                return $answer->nilai !== null;
            })->avg('nilai'),
            'ungraded' => $answers->filter(function($answer) {
                return $answer->nilai === null;
            })->count()
        ];
        
        \Log::info('Get student answers success', [
            'component' => $component,
            'material_id' => $materialId,
            'total_answers' => $stats['total']
        ]);
        
        return response()->json([
            'success' => true,
            'answers' => $answers,
            'stats' => $stats,
            'component' => $component
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error getting student answers', [
            'error' => $e->getMessage(),
            'material_id' => $materialId,
            'component' => $component,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Terjadi kesalahan saat mengambil data',
            'message' => $e->getMessage()
        ], 500);
    }
}
/**
 * Get detailed statistics for student answers (Teacher only)
 */
public function getAnswerStatistics($courseId, $materialId, $component)
{
    if (!$this->hasManageAccess($courseId)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $validComponents = [
        'mulai_dari_diri', 
        'ruang_kolaborasi',
        'demonstrasi_konseptual', 
        'elaborasi_pemahaman'
    ];
    
    if (!in_array($component, $validComponents)) {
        return response()->json(['error' => 'Invalid component'], 400);
    }
    
    $table = 'jawaban_' . $component;
    
    try {
        $stats = DB::table($table)
            ->where('id_materi', $materialId)
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN nilai IS NOT NULL THEN 1 END) as graded,
                COUNT(CASE WHEN nilai IS NULL THEN 1 END) as ungraded,
                AVG(nilai) as average,
                MAX(nilai) as highest,
                MIN(nilai) as lowest,
                COUNT(CASE WHEN nilai >= 85 THEN 1 END) as excellent,
                COUNT(CASE WHEN nilai >= 75 AND nilai < 85 THEN 1 END) as good,
                COUNT(CASE WHEN nilai >= 65 AND nilai < 75 THEN 1 END) as fair,
                COUNT(CASE WHEN nilai < 65 AND nilai IS NOT NULL THEN 1 END) as poor
            ')
            ->first();
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Error getting statistics', [
            'error' => $e->getMessage(),
            'material_id' => $materialId,
            'component' => $component
        ]);
        
        return response()->json([
            'error' => 'Terjadi kesalahan'
        ], 500);
    }
}

private function checkAndUpdateChecklist($materialId, $answerId, $component)
{
    try {
        $tableMapping = [
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

        $table = $tableMapping[$component]['table'];
        $primaryKey = $tableMapping[$component]['pk'];

        $answer = DB::table($table)
            ->where($primaryKey, $answerId)
            ->first();

        if (!$answer) {
            return;
        }

        $userId = $answer->id_user;

        // Cek apakah komponen sudah selesai
        $componentsWithPRE = ['ruang_kolaborasi', 'demonstrasi_konseptual', 'elaborasi_pemahaman'];
        
        $isComplete = true;

        // Cek jawaban utama
        if ($answer->nilai === null) {
            $isComplete = false;
        }

        // Jika komponen punya PRE, cek juga jawaban PRE
        if (in_array($component, $componentsWithPRE)) {
            $jawabanPRE = DB::table('jawaban_perencanaan_refleksi_evaluasi')
                ->where('id_materi', $materialId)
                ->where('id_user', $userId)
                ->where('jenis', $component)
                ->first();

            if ($jawabanPRE) {
                $types = ['perencanaan', 'refleksi', 'evaluasi'];
                foreach ($types as $type) {
                    $nilaiField = 'nilai_' . $type;
                    // Cek jika ada jawaban tapi belum dinilai
                    if ($jawabanPRE->$type !== null && $jawabanPRE->$nilaiField === null) {
                        $isComplete = false;
                        break;
                    }
                }
            }
        }

        // Update checklist jika complete
        if ($isComplete) {
            DB::table('checklist_materi')
                ->where('id_materi', $materialId)
                ->where('id_user', $userId)
                ->update([
                    $component => 'Y',
                    'updated_at' => now()
                ]);
                
            \Log::info('Checklist updated', [
                'material_id' => $materialId,
                'user_id' => $userId,
                'component' => $component
            ]);
        }

    } catch (\Exception $e) {
        \Log::error('Error updating checklist: ' . $e->getMessage(), [
            'material_id' => $materialId,
            'answer_id' => $answerId,
            'component' => $component
        ]);
    }
}

/**
 * Menyimpan nilai untuk banyak jawaban subjektif sekaligus (Perencanaan, Refleksi, Evaluasi).
 */
public function gradeJawabanSubjektifBatch(Request $request)
{
    // Validasi input: Harapkan array 'grades'
    $request->validate([
        'grades' => 'required|array',
        'grades.*.id' => 'required|exists:jawaban_perencanaan_refleksi_evaluasi,id',
        'grades.*.type' => 'required|in:perencanaan,refleksi,evaluasi',
        'grades.*.nilai' => 'nullable|numeric|min:0|max:100', // Nilai boleh kosong/null
        'id_materi' => 'required|exists:materi,id_materi'
    ]);

    // Otorisasi: Pastikan pengguna adalah guru/admin (anda harus memastikan ini diimplementasikan)
    // Asumsi otorisasi dihandle di middleware atau logic lain.
    
    $gradedCount = 0;

    DB::beginTransaction();
    try {
        foreach ($request->input('grades') as $gradeData) {
            $jawabanId = $gradeData['id'];
            $type = $gradeData['type']; // perencanaan, refleksi, atau evaluasi
            $nilai = $gradeData['nilai'];
            $nilaiField = 'nilai_' . $type;

            // Hanya proses jawaban jika nilai tidak NULL (misal: guru mengisi nilai)
            if (!is_null($nilai)) { 
                 // Ambil data jawaban (diperlukan untuk updateChecklist)
                $jawaban = DB::table('jawaban_perencanaan_refleksi_evaluasi')
                    ->where('id', $jawabanId)
                    ->first();

                if (!$jawaban) {
                    continue; // Lewati jika jawaban tidak ditemukan
                }

                // Lakukan update nilai
                DB::table('jawaban_perencanaan_refleksi_evaluasi')
                    ->where('id', $jawabanId)
                    ->update([
                        $nilaiField => ($nilai === '') ? null : $nilai, // Set NULL jika string kosong
                        'updated_at' => now(),
                    ]);
                
                $gradedCount++;

                // Panggil fungsi updateChecklist yang sudah ada
                $this->updateChecklist($jawaban->id_materi, $jawaban->id_user, $jawaban->jenis, $jawabanId);
            }
        }

        DB::commit();

        return redirect()->back()->with('success', "Berhasil menyimpan nilai untuk **{$gradedCount}** komponen jawaban siswa.");

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error in subjective batch grading: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal menyimpan nilai secara massal. Pesan error: ' . $e->getMessage());
    }
}
}