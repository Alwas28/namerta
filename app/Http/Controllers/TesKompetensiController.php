<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisTesKompetensi;
use App\Models\SoalTesKompetensi;
use App\Models\SoalEssayTesKompetensi;
use App\Models\JawabanTesKompetensi;
use App\Models\JawabanEssayTesKompetensi;
use App\Models\Modul;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TesKompetensiController extends Controller
{
    /**
     * Check if user has permission to manage tes kompetensi (CRUD)
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
     * Tampilkan halaman utama tes kompetensi
     */
    public function index($courseId, $modulId)
    {
        try {
            $modul = Modul::findOrFail($modulId);
            $canManage = $this->hasManageAccess($courseId);
            $userId = Auth::id();
            
            // Get data tes kompetensi
            $jenisTestKompetensi = JenisTesKompetensi::where('id_modul', $modulId)->first();
            $soalPilgan = collect();
            $soalEssay = null;
            $jawabanTesKompetensi = collect();
            $jawabanEssay = collect();
            
            if ($jenisTestKompetensi) {
                if ($jenisTestKompetensi->essay == 'N') {
                    // Pilihan Ganda
                    $soalPilgan = SoalTesKompetensi::where('id_modul', $modulId)
                        ->orderBy('created_at', 'asc')
                        ->get();
                        
                    if (!$canManage && $userId) { // jika siswa
                        $jawabanTesKompetensi = JawabanTesKompetensi::join('soal_tes_kompetensi', 'jawaban_tes_kompetensi.id_soal_tes_kompetensi', '=', 'soal_tes_kompetensi.id_soal_tes_kompetensi')
                            ->where('soal_tes_kompetensi.id_modul', $modulId)
                            ->where('jawaban_tes_kompetensi.id_user', $userId)
                            ->select('jawaban_tes_kompetensi.*')
                            ->get();
                    }
                } else {
                    // Essay
                    $soalEssay = SoalEssayTesKompetensi::where('id_modul', $modulId)->first();
                    if (!$canManage && $userId) { // jika siswa
                        $jawabanEssay = JawabanEssayTesKompetensi::join('soal_essay_tes_kompetensi', 'jawaban_essay_tes_kompetensi.id_soal_essay', '=', 'soal_essay_tes_kompetensi.id_soal_essay')
                            ->where('soal_essay_tes_kompetensi.id_modul', $modulId)
                            ->where('jawaban_essay_tes_kompetensi.id_user', $userId)
                            ->select('jawaban_essay_tes_kompetensi.*')
                            ->get();
                    }
                }
            }
            
            return view('mata_pelajaran.tes-kompetensi', compact(
                'modul', 'courseId', 'canManage',
                'jenisTestKompetensi', 'soalPilgan', 'soalEssay',
                'jawabanTesKompetensi', 'jawabanEssay'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update jenis tes kompetensi (pilihan ganda atau essay)
     */
    public function updateJenisTes(Request $request, $courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return redirect()->back()->with('error', 'Unauthorized');
            }

            $request->validate([
                'jenis_tes' => 'required|in:pilgan,essay'
            ]);

            $isEssay = $request->jenis_tes === 'essay' ? 'Y' : 'N';

            JenisTesKompetensi::updateOrCreate(
                ['id_modul' => $modulId],
                ['essay' => $isEssay]
            );

            return redirect()->back()->with('success', 'Jenis tes kompetensi berhasil diupdate');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupdate jenis tes: ' . $e->getMessage());
        }
    }

    /**
     * Store soal tes kompetensi pilihan ganda
     */
    public function storeSoalPilgan(Request $request, $courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'soal' => 'required|string|max:1000',
                'a' => 'required|string|max:255',
                'b' => 'required|string|max:255',
                'c' => 'required|string|max:255',
                'd' => 'required|string|max:255',
                'jawaban_benar' => 'required|in:a,b,c,d'
            ]);

            // Verifikasi modul exists
            $modul = Modul::findOrFail($modulId);

            SoalTesKompetensi::create([
                'id_modul' => $modulId,
                'soal' => $request->soal,
                'a' => $request->a,
                'b' => $request->b,
                'c' => $request->c,
                'd' => $request->d,
                'jawaban_benar' => $request->jawaban_benar
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil ditambahkan'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', array_flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update soal tes kompetensi pilihan ganda
     */
    public function updateSoalPilgan(Request $request, $courseId, $modulId, $soalId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'soal' => 'required|string|max:1000',
                'a' => 'required|string|max:255',
                'b' => 'required|string|max:255',
                'c' => 'required|string|max:255',
                'd' => 'required|string|max:255',
                'jawaban_benar' => 'required|in:a,b,c,d'
            ]);

            $soal = SoalTesKompetensi::where('id_soal_tes_kompetensi', $soalId)
                ->where('id_modul', $modulId)
                ->firstOrFail();

            $soal->update([
                'soal' => $request->soal,
                'a' => $request->a,
                'b' => $request->b,
                'c' => $request->c,
                'd' => $request->d,
                'jawaban_benar' => $request->jawaban_benar
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil diupdate'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', array_flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete soal tes kompetensi pilihan ganda
     */
    public function deleteSoalPilgan($courseId, $modulId, $soalId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            DB::beginTransaction();

            $soal = SoalTesKompetensi::where('id_soal_tes_kompetensi', $soalId)
                ->where('id_modul', $modulId)
                ->firstOrFail();

            // Hapus jawaban siswa terkait
            JawabanTesKompetensi::where('id_soal_tes_kompetensi', $soalId)->delete();

            // Hapus soal
            $soal->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Soal berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus soal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store atau update soal essay
     */
    public function storeUpdateSoalEssay(Request $request, $courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return redirect()->back()->with('error', 'Unauthorized');
            }

            $request->validate([
                'soal_essay' => 'required|string|max:5000',
                'file_soal' => 'nullable|file|mimes:pdf,doc,docx|max:10240' // 10MB max
            ]);

            // Verifikasi modul exists
            $modul = Modul::findOrFail($modulId);

            $soalData = ['soal' => $request->soal_essay];

            // Handle file upload jika ada
            if ($request->hasFile('file_soal')) {
                // Hapus file lama jika ada
                $existingSoal = SoalEssayTesKompetensi::where('id_modul', $modulId)->first();
                if ($existingSoal && $existingSoal->file_soal && Storage::disk('public')->exists($existingSoal->file_soal)) {
                    Storage::disk('public')->delete($existingSoal->file_soal);
                }

                $file = $request->file('file_soal');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('soal_essay', $fileName, 'public');
                $soalData['file_soal'] = $filePath;
            }

            SoalEssayTesKompetensi::updateOrCreate(
                ['id_modul' => $modulId],
                $soalData
            );

            return redirect()->back()->with('success', 'Soal essay berhasil disimpan');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan soal essay: ' . $e->getMessage());
        }
    }

    /**
     * Submit jawaban tes kompetensi pilihan ganda (siswa)
     */
    public function submitJawabanPilgan(Request $request, $courseId, $modulId)
    {
        try {
            if ($this->hasManageAccess($courseId)) {
                return redirect()->back()->with('error', 'Only students can submit answers');
            }

            $userId = Auth::id();
            $soalIds = SoalTesKompetensi::where('id_modul', $modulId)->pluck('id_soal_tes_kompetensi');

            if ($soalIds->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada soal tersedia');
            }

            DB::beginTransaction();

            $totalJawaban = 0;
            foreach ($soalIds as $soalId) {
                $jawabanKey = 'jawaban_' . $soalId;
                
                if ($request->has($jawabanKey) && !empty($request->input($jawabanKey))) {
                    JawabanTesKompetensi::updateOrCreate(
                        [
                            'id_user' => $userId,
                            'id_soal_tes_kompetensi' => $soalId
                        ],
                        [
                            'jawaban' => $request->input($jawabanKey),
                            'benar' => null // Akan dinilai kemudian
                        ]
                    );
                    $totalJawaban++;
                }
            }

            DB::commit();

            return redirect()->back()->with('success', "Berhasil menyimpan {$totalJawaban} jawaban");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan jawaban: ' . $e->getMessage());
        }
    }

    /**
     * Submit jawaban tes kompetensi essay (siswa)
     */
    public function submitJawabanEssay(Request $request, $courseId, $modulId)
    {
        try {
            if ($this->hasManageAccess($courseId)) {
                return redirect()->back()->with('error', 'Only students can submit answers');
            }

            $request->validate([
                'jawaban' => 'required|string|max:10000'
            ]);

            $userId = Auth::id();
            $soalEssay = SoalEssayTesKompetensi::where('id_modul', $modulId)->firstOrFail();

            JawabanEssayTesKompetensi::updateOrCreate(
                [
                    'id_user' => $userId,
                    'id_soal_essay' => $soalEssay->id_soal_essay
                ],
                [
                    'jawaban' => $request->jawaban
                ]
            );

            return redirect()->back()->with('success', 'Jawaban essay berhasil disimpan');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan jawaban essay: ' . $e->getMessage());
        }
    }

    /**
     * Grade jawaban essay (Teacher only)
     */
    public function gradeJawabanEssay(Request $request, $courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'jawaban_id' => 'required|exists:jawaban_essay_tes_kompetensi,id_jawaban_essay',
                'nilai' => 'required|integer|min:0|max:100'
            ]);

            $jawaban = JawabanEssayTesKompetensi::findOrFail($request->jawaban_id);
            $jawaban->nilai = $request->nilai;
            $jawaban->save();

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get jawaban tes kompetensi untuk guru
     */
    public function getJawabanSiswa($courseId, $modulId, $type)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($type === 'pilgan') {
                $answers = JawabanTesKompetensi::join('users', 'jawaban_tes_kompetensi.id_user', '=', 'users.id_user')
                    ->join('profile', 'users.id_user', '=', 'profile.id_user')
                    ->join('soal_tes_kompetensi', 'jawaban_tes_kompetensi.id_soal_tes_kompetensi', '=', 'soal_tes_kompetensi.id_soal_tes_kompetensi')
                    ->where('soal_tes_kompetensi.id_modul', $modulId)
                    ->select(
                        'jawaban_tes_kompetensi.*',
                        'profile.nama as nama_siswa',
                        'soal_tes_kompetensi.jawaban_benar',
                        'soal_tes_kompetensi.soal'
                    )
                    ->orderBy('profile.nama')
                    ->get();

            } elseif ($type === 'essay') {
                $answers = JawabanEssayTesKompetensi::join('users', 'jawaban_essay_tes_kompetensi.id_user', '=', 'users.id_user')
                    ->join('profile', 'users.id_user', '=', 'profile.id_user')
                    ->join('soal_essay_tes_kompetensi', 'jawaban_essay_tes_kompetensi.id_soal_essay', '=', 'soal_essay_tes_kompetensi.id_soal_essay')
                    ->where('soal_essay_tes_kompetensi.id_modul', $modulId)
                    ->select(
                        'jawaban_essay_tes_kompetensi.*',
                        'profile.nama as nama_siswa',
                        'soal_essay_tes_kompetensi.soal'
                    )
                    ->orderBy('profile.nama')
                    ->get();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipe tes tidak valid'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'answers' => $answers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data jawaban: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Penilaian otomatis untuk jawaban pilihan ganda
     */
    public function gradeJawabanPilgan(Request $request, $courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'user_id' => 'required|exists:users,id_user'
            ]);

            $userId = $request->user_id;

            DB::beginTransaction();

            // Ambil semua jawaban siswa untuk modul ini beserta jawaban benar
            $jawaban = JawabanTesKompetensi::join('soal_tes_kompetensi', 'jawaban_tes_kompetensi.id_soal_tes_kompetensi', '=', 'soal_tes_kompetensi.id_soal_tes_kompetensi')
                ->where('soal_tes_kompetensi.id_modul', $modulId)
                ->where('jawaban_tes_kompetensi.id_user', $userId)
                ->select('jawaban_tes_kompetensi.*', 'soal_tes_kompetensi.jawaban_benar')
                ->get();

            if ($jawaban->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada jawaban yang ditemukan untuk siswa ini'
                ], 404);
            }

            $gradedCount = 0;
            foreach ($jawaban as $item) {
                $benar = ($item->jawaban === $item->jawaban_benar) ? 'Y' : 'N';
                
                JawabanTesKompetensi::where('id_jawaban_tes_kompetensi', $item->id_jawaban_tes_kompetensi)
                    ->update(['benar' => $benar]);
                
                $gradedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Penilaian otomatis berhasil dilakukan untuk {$gradedCount} jawaban",
                'graded_count' => $gradedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan penilaian otomatis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grade semua jawaban pilihan ganda untuk modul
     */
    public function gradeAllJawabanPilgan($courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            DB::beginTransaction();

            // Ambil semua jawaban yang belum dinilai
            $jawaban = JawabanTesKompetensi::join('soal_tes_kompetensi', 'jawaban_tes_kompetensi.id_soal_tes_kompetensi', '=', 'soal_tes_kompetensi.id_soal_tes_kompetensi')
                ->where('soal_tes_kompetensi.id_modul', $modulId)
                ->whereNull('jawaban_tes_kompetensi.benar')
                ->select('jawaban_tes_kompetensi.*', 'soal_tes_kompetensi.jawaban_benar')
                ->get();

            $gradedCount = 0;
            foreach ($jawaban as $item) {
                $benar = ($item->jawaban === $item->jawaban_benar) ? 'Y' : 'N';
                
                JawabanTesKompetensi::where('id_jawaban_tes_kompetensi', $item->id_jawaban_tes_kompetensi)
                    ->update(['benar' => $benar]);
                
                $gradedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Semua jawaban berhasil dinilai otomatis ({$gradedCount} jawaban)",
                'graded_count' => $gradedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan penilaian otomatis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistik tes kompetensi untuk modul
     */
    public function getStatistik($courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $jenisTest = JenisTesKompetensi::where('id_modul', $modulId)->first();
            
            if (!$jenisTest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis tes belum ditentukan'
                ], 404);
            }

            $statistik = [];

            if ($jenisTest->essay === 'N') {
                // Statistik pilihan ganda
                $totalSoal = SoalTesKompetensi::where('id_modul', $modulId)->count();
                $totalSiswaJawab = JawabanTesKompetensi::join('soal_tes_kompetensi', 'jawaban_tes_kompetensi.id_soal_tes_kompetensi', '=', 'soal_tes_kompetensi.id_soal_tes_kompetensi')
                    ->where('soal_tes_kompetensi.id_modul', $modulId)
                    ->distinct('jawaban_tes_kompetensi.id_user')
                    ->count();

                $sudahDinilai = JawabanTesKompetensi::join('soal_tes_kompetensi', 'jawaban_tes_kompetensi.id_soal_tes_kompetensi', '=', 'soal_tes_kompetensi.id_soal_tes_kompetensi')
                    ->where('soal_tes_kompetensi.id_modul', $modulId)
                    ->whereNotNull('jawaban_tes_kompetensi.benar')
                    ->distinct('jawaban_tes_kompetensi.id_user')
                    ->count();

                $statistik = [
                    'jenis' => 'pilgan',
                    'total_soal' => $totalSoal,
                    'total_siswa_jawab' => $totalSiswaJawab,
                    'sudah_dinilai' => $sudahDinilai,
                    'belum_dinilai' => $totalSiswaJawab - $sudahDinilai
                ];

            } else {
                // Statistik essay
                $totalSoal = SoalEssayTesKompetensi::where('id_modul', $modulId)->count();
                $totalSiswaJawab = JawabanEssayTesKompetensi::join('soal_essay_tes_kompetensi', 'jawaban_essay_tes_kompetensi.id_soal_essay', '=', 'soal_essay_tes_kompetensi.id_soal_essay')
                    ->where('soal_essay_tes_kompetensi.id_modul', $modulId)
                    ->count();

                $statistik = [
                    'jenis' => 'essay',
                    'total_soal' => $totalSoal,
                    'total_siswa_jawab' => $totalSiswaJawab
                ];
            }

            return response()->json([
                'success' => true,
                'statistik' => $statistik
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset semua jawaban tes untuk modul (untuk testing/debugging)
     */
    public function resetJawaban($courseId, $modulId)
    {
        try {
            if (!$this->hasManageAccess($courseId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            DB::beginTransaction();

            // Reset jawaban pilihan ganda
            $deletedPilgan = DB::table('jawaban_tes_kompetensi')
                ->join('soal_tes_kompetensi', 'jawaban_tes_kompetensi.id_soal_tes_kompetensi', '=', 'soal_tes_kompetensi.id_soal_tes_kompetensi')
                ->where('soal_tes_kompetensi.id_modul', $modulId)
                ->delete();

            // Reset jawaban essay
            $deletedEssay = DB::table('jawaban_essay_tes_kompetensi')
                ->join('soal_essay_tes_kompetensi', 'jawaban_essay_tes_kompetensi.id_soal_essay', '=', 'soal_essay_tes_kompetensi.id_soal_essay')
                ->where('soal_essay_tes_kompetensi.id_modul', $modulId)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Semua jawaban berhasil direset (Pilgan: {$deletedPilgan}, Essay: {$deletedEssay})"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset jawaban: ' . $e->getMessage()
            ], 500);
        }
    }
}