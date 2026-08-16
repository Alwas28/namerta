<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AIChatController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiskusiController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KelasMataPelajaranController;
use App\Http\Controllers\KelasTahunAjaranController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\MateriLihatController;
use App\Http\Controllers\ModulController;
use App\Http\Controllers\PengetahuanMetakognisiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiswaKelasController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\TesKompetensiController;
use App\Http\Controllers\TestOpenAIController;
use App\Http\Controllers\UserController;
use App\Models\AiChatMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route Home Page
Route::get('/', [HomeController::class, 'index'])->name('home');


// Authentication routes
Route::middleware(['guest'])->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    
});

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// / Protected route - Dashboard untuk semua role
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard_guru', [AdminDashboardController::class, 'guru'])->name('guru.dashboard');
    Route::get('/dashboard_siswa', [AdminDashboardController::class, 'siswa'])->name('siswa.dashboard');
    Route::get('/dashboard_tendik', [AdminDashboardController::class, 'tendik'])->name('tendik.dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
    
});

Route::middleware(['auth'])->group(function () {
    // User CRUD Routes
    // Route::resource('users', UserController::class);
    
    // Atau jika ingin lebih eksplisit:
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth'])->group(function () {
    // Existing role routes
    // Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('roles', RoleController::class);
    
    // New permission page routes
    Route::get('roles/{id}/permissions', [RoleController::class, 'showPermissions'])->name('roles.permissions.show');
    Route::post('roles/{id}/permissions/update', [RoleController::class, 'updatePermissionsPage'])->name('roles.permissions.update');
});

Route::middleware(['auth'])->group(function () {
    // Tahun Ajaran Management Routes
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    Route::post('tahun-ajaran/{id}/set-active', [TahunAjaranController::class, 'setActive'])->name('tahun-ajaran.set-active');
});

Route::middleware(['auth'])->group(function () {
    // Mata Pelajaran Management Routes
    Route::resource('mata-pelajaran', MataPelajaranController::class);
    Route::post('mata-pelajaran/{id}/toggle-status', [MataPelajaranController::class, 'toggleStatus'])->name('mata-pelajaran.toggle-status');
});

Route::middleware(['auth'])->group(function () {
    // Mata Pelajaran Management Routes
    Route::resource('kelas', KelasController::class);
    Route::post('kelas/{id}/toggle-status', [KelasController::class, 'toggleStatus'])->name('kelas.toggle-status');
});

// Kelas Tahun Ajaran routes
Route::middleware(['auth'])->group(function () {
    Route::get('/kelas-ta', [KelasTahunAjaranController::class, 'index'])->name('kelas-ta.index');
    Route::post('/kelas-ta', [KelasTahunAjaranController::class, 'store']);
    Route::delete('/kelas-ta/{id}', [KelasTahunAjaranController::class, 'destroy']);
    Route::post('/kelas-ta/copy-previous', [KelasTahunAjaranController::class, 'copyFromPreviousTA']);
});

// Kelas Mata Pelajaran routes
Route::middleware(['auth'])->group(function () {
    Route::get('/kelas-mapel', [KelasMataPelajaranController::class, 'index'])->name('kelas-mapel.index');
    Route::post('/kelas-mapel', [KelasMataPelajaranController::class, 'store']);
    Route::get('/kelas-mapel/{id}', [KelasMataPelajaranController::class, 'show']);
    Route::put('/kelas-mapel/{id}', [KelasMataPelajaranController::class, 'update']);
    Route::delete('/kelas-mapel/{id}', [KelasMataPelajaranController::class, 'destroy']);
});

// Siswa Kelas routes
Route::middleware(['auth'])->group(function () {
    Route::get('/siswa-kelas', [SiswaKelasController::class, 'index'])->name('siswa-kelas.index');
    Route::post('/siswa-kelas', [SiswaKelasController::class, 'store']);
    Route::get('/siswa-kelas/{id}', [SiswaKelasController::class, 'show']);
    Route::put('/siswa-kelas/{id}', [SiswaKelasController::class, 'update']);
    Route::delete('/siswa-kelas/{id}', [SiswaKelasController::class, 'destroy']);
    Route::post('/siswa-kelas/copy-kelas', [SiswaKelasController::class, 'copyFromKelas']);
});


// Route untuk authenticated users saja
Route::middleware(['auth'])->group(function () {
    // Courses
    Route::get('/courses', [CoursesController::class, 'index'])->name('courses.index');
    // Route::get('/courses/{id}', [CoursesController::class, 'show'])->name('courses.show');
});

Route::middleware(['auth'])->group(function () {

    // Routes untuk ModulController - tambahkan di dalam grup yang sesuai
    Route::get('/courses/{courseId}/modul', [ModulController::class, 'index'])->name('modul.index');    

    Route::get('/courses/{courseId}/modul/{modulId}', [ModulController::class, 'show'])->name('modul.show');
    Route::delete('/courses/{courseId}/modul/{modulId}', [ModulController::class, 'destroy'])->name('modul.destroy');
    Route::post('/courses/{courseId}/modul', [ModulController::class, 'store'])->name('modul.store');

    // Routes untuk Pengalaman Belajar
    Route::put('/courses/{courseId}/modules/{modulId}/pengalaman-belajar', [ModulController::class, 'updatePengalamanBelajar'])->name('modul.updatePengalamanBelajar');
    Route::post('/courses/{courseId}/modules/{modulId}/complete-pengalaman-belajar', [ModulController::class, 'completePengalamanBelajar'])->name('modul.completePengalamanBelajar');

    // Routes untuk Material Management
    Route::post('/courses/{courseId}/modules/{modulId}/materials', [ModulController::class, 'storeMaterial'])->name('materi.store');
    Route::put('/courses/{courseId}/materials/{materialId}', [ModulController::class, 'updateMaterial'])->name('materi.update');
    Route::delete('/courses/{courseId}/materials/{materialId}', [ModulController::class, 'destroyMaterial'])->name('materi.destroy');
    Route::get('/courses/{courseId}/materials/{materialId}', [ModulController::class, 'showMaterial'])->name('materi.show');
    Route::post('/courses/{courseId}/modules/{modulId}/complete-material', [ModulController::class, 'completeMaterial'])->name('modul.completeMaterial');

    // Routes untuk Koneksi Materi
    Route::post('/courses/{courseId}/modules/{modulId}/koneksi-materi/soal', [ModulController::class, 'uploadKoneksiMateriSoal'])->name('modul.uploadKoneksiMateriSoal');
    Route::post('/courses/{courseId}/modules/{modulId}/koneksi-materi/jawaban', [ModulController::class, 'uploadKoneksiMateriJawaban'])->name('modul.uploadKoneksiMateriJawaban');

    // Routes untuk Aksi Nyata
    Route::put('/courses/{courseId}/modules/{modulId}/aksi-nyata', [ModulController::class, 'updateAksiNyataSoal'])->name('modul.updateAksiNyataSoal');
    Route::post('/courses/{courseId}/modules/{modulId}/aksi-nyata/jawaban', [ModulController::class, 'submitAksiNyataJawaban'])->name('modul.submitAksiNyataJawaban');

    Route::get('/courses/{courseId}/modules/{modulId}/answers/{type}', [ModulController::class, 'getStudentAnswers'])->name('modul.getStudentAnswers');
    Route::post('/courses/{courseId}/modules/{modulId}/grade-answer', [ModulController::class, 'gradeAnswer'])->name('modul.gradeAnswer');
});

Route::middleware(['auth'])->group(function () {

    // Material routes - gunakan MateriController
    Route::get('/courses/{courseId}/materials/{materialId}', [MateriController::class, 'show'])->name('materi.show');
    
    // Essay PRE routes
    Route::post('/courses/{courseId}/materi/{materialId}/soal-essay-pre', [MateriController::class, 'updateSoalEssayPRE'])->name('materi.updateSoalEssayPRE');
    Route::post('/courses/{courseId}/materi/{materialId}/jawaban-essay-pre', [MateriController::class, 'submitJawabanEssayPRE'])->name('materi.submitJawabanEssayPRE');
// Material component routes
    Route::post('/courses/{courseId}/materials/{materialId}/upload-soal', [MateriController::class, 'uploadSoal'])->name('materi.uploadSoal');
    Route::post('/courses/{courseId}/materials/{materialId}/upload-jawaban', [MateriController::class, 'uploadJawaban'])->name('materi.uploadJawaban');
    Route::post('/courses/{courseId}/materials/{materialId}/complete-component', [MateriController::class, 'completeComponent'])->name('materi.completeComponent');

    // Grading routes
    Route::get('/courses/{courseId}/materials/{materialId}/answers/{component}', [MateriController::class, 'getStudentAnswers'])->name('materi.getStudentAnswers');
    
    Route::post('/courses/{courseId}/materials/{materialId}/grade-answer', [MateriController::class, 'gradeAnswer'])->name('materi.gradeAnswer');

    // Routes untuk Eksplorasi Konsep
    Route::post('/courses/{courseId}/materials/{materialId}/update-eksplorasi-konsep', [MateriController::class, 'updateEksplorasiKonsep'])->name('materi.updateEksplorasiKonsep');
    Route::post('/courses/{courseId}/materials/{materialId}/complete-eksplorasi-konsep', [MateriController::class, 'completeEksplorasiKonsep'])->name('materi.completeEksplorasiKonsep');

    // Routes untuk upload soal dan jawaban Perencanaan/Refleksi/Evaluasi
    Route::post('/courses/{courseId}/materials/{materialId}/upload-soal-pre', [MateriController::class, 'uploadSoalPRE'])->name('materi.uploadSoalPRE');
    Route::post('/courses/{courseId}/materials/{materialId}/upload-jawaban-pre', [MateriController::class, 'uploadJawabanPRE'])->name('materi.uploadJawabanPRE');

    // Routes untuk lihat dan nilai jawaban PRE
    Route::get('/courses/{courseId}/materials/{materialId}/student-answers-pre/{component}/{type}', [MateriController::class, 'getStudentAnswersPRE'])->name('materi.getStudentAnswersPRE');
    Route::post('/courses/{courseId}/materials/{materialId}/grade-answer-pre', [MateriController::class, 'gradeAnswerPRE'])->name('materi.gradeAnswerPRE');
    
    Route::post('/courses/{courseId}/modules/{modulId}/set-jenis-tes', [ModulController::class, 'setJenisTes']);
    
    // Routes untuk Pengetahuan Metakognisi - Controller Baru
    Route::post('/courses/{courseId}/materials/{materialId}/metakognisi/upload-soal', [PengetahuanMetakognisiController::class, 'uploadSoal'])->name('metakognisi.uploadSoal');
    // Route::post('/courses/{courseId}/materials/{materialId}/metakognisi/upload-jawaban', [PengetahuanMetakognisiController::class, 'uploadJawaban'])->name('metakognisi.uploadJawaban');
    Route::delete('/courses/{courseId}/materials/{materialId}/metakognisi/delete-soal/{type}', [PengetahuanMetakognisiController::class, 'deleteSoal'])->name('metakognisi.deleteSoal');
    
    // Metakognisi Essay Routes (Teacher)
    Route::post('/courses/{courseId}/materi/{materialId}/metakognisi/updateSoal', [MateriController::class, 'updateSoalMetakognisi'])->name('metakognisi.updateSoal');
    Route::get('/courses/{courseId}/materi/{materialId}/metakognisi/getStudentAnswers', [MateriController::class, 'getStudentMetakognisiAnswers'])->name('metakognisi.getStudentAnswers');
    Route::post('/courses/{courseId}/materi/{materialId}/metakognisi/gradeAnswer', [MateriController::class, 'gradeMetakognisiAnswer'])->name('metakognisi.gradeAnswer');

    // Metakognisi Essay Routes (Student)  
    Route::post('/courses/{courseId}/materi/{materialId}/metakognisi/uploadJawaban', [MateriController::class, 'uploadJawabanMetakognisi'])->name('metakognisi.uploadJawaban');

    // Routes untuk sistem penilaian numerik (Mulai Dari Diri, dll)
    Route::post('/courses/{courseId}/materials/{materialId}/grade-numeric', [MateriController::class, 'gradeAnswerNumeric'])->name('materi.gradeAnswerNumeric');
    
    // Route untuk statistik jawaban
    Route::get('/courses/{courseId}/materials/{materialId}/statistics/{component}', [MateriController::class, 'getAnswerStatistics'])->name('materi.getAnswerStatistics');

        // Routes yang sudah ada di document Anda sudah benar, pastikan ini ada:
    Route::get('/courses/{courseId}/materials/{materialId}/student-answers-pre/{component}/{type}', [MateriController::class, 'getStudentAnswersPRE'])->name('materi.getStudentAnswersPRE');
    Route::post('/courses/{courseId}/materials/{materialId}/grade-answer-pre', [MateriController::class, 'gradeAnswerPRE'])->name('materi.gradeAnswerPRE');


    // Rute BARU untuk Penilaian Massal Jawaban Subjektif (Guru)
    // Rute ini akan menerima array of answer IDs dan grades
    Route::post('/materi/grade-subjective-batch', [MateriController::class, 'gradeJawabanSubjektifBatch'])->name('materi.gradeJawabanSubjektifBatch');
});

Route::prefix('courses/{courseId}/modules/{modulId}/tes-kompetensi')->group(function () {
    
    // Route utama untuk menampilkan halaman tes kompetensi
    Route::get('/', [TesKompetensiController::class, 'index'])->name('tes-kompetensi.index');
    
    // Routes untuk pengaturan jenis tes (Teacher only)
    Route::put('/jenis-tes', [TesKompetensiController::class, 'updateJenisTes'])->name('tes-kompetensi.updateJenisTes');
    
    // Routes untuk soal pilihan ganda (Teacher only)
    Route::post('/soal-pilgan', [TesKompetensiController::class, 'storeSoalPilgan'])->name('tes-kompetensi.storeSoalPilgan');
    Route::put('/soal-pilgan/{soalId}', [TesKompetensiController::class, 'updateSoalPilgan'])->name('tes-kompetensi.updateSoalPilgan');
    Route::delete('/soal-pilgan/{soalId}', [TesKompetensiController::class, 'deleteSoalPilgan'])->name('tes-kompetensi.deleteSoalPilgan');
    
    // Routes untuk soal essay (Teacher only)
    Route::post('/soal-essay', [TesKompetensiController::class, 'storeUpdateSoalEssay'])->name('tes-kompetensi.storeUpdateSoalEssay');
    
    // Routes untuk submit jawaban (Student only)
    Route::post('/submit-pilgan', [TesKompetensiController::class, 'submitJawabanPilgan'])->name('tes-kompetensi.submitJawabanPilgan');
    Route::post('/submit-essay', [TesKompetensiController::class, 'submitJawabanEssay'])->name('tes-kompetensi.submitJawabanEssay');
    
    // Routes untuk melihat jawaban siswa (Teacher only)
    Route::get('/jawaban/{type}', [TesKompetensiController::class, 'getJawabanSiswa'])->name('tes-kompetensi.getJawabanSiswa');
    
    // Routes untuk penilaian (Teacher only)
    Route::post('/grade-pilgan', [TesKompetensiController::class, 'gradeJawabanPilgan'])->name('tes-kompetensi.gradeJawabanPilgan');
    Route::post('/grade-all-pilgan', [TesKompetensiController::class, 'gradeAllJawabanPilgan'])->name('tes-kompetensi.gradeAllJawabanPilgan');
    
    // Routes untuk statistik dan utilitas (Teacher only)
    Route::get('/statistik', [TesKompetensiController::class, 'getStatistik'])->name('tes-kompetensi.getStatistik');
    Route::delete('/reset-jawaban', [TesKompetensiController::class, 'resetJawaban'])->name('tes-kompetensi.resetJawaban');

    Route::post('/grade-essay', [TesKompetensiController::class, 'gradeJawabanEssay'])->name('tes-kompetensi.gradeJawabanEssay');
    
});

// AI Chat Routes - gunakan parameter modulId dan materiId
Route::middleware(['auth'])->group(function () {
    Route::post('/api/ai-chat/{modulId}/{materiId}', [AIChatController::class, 'sendMessage'])
        ->name('ai.chat.send');
    
    Route::get('/api/ai-chat/{modulId}/{materiId}/history', [AIChatController::class, 'getChatHistory'])
        ->name('ai.chat.history');
    
    Route::delete('/api/ai-chat/{modulId}/{materiId}/clear', [AIChatController::class, 'clearChatHistory'])
        ->name('ai.chat.clear');
});

Route::middleware(['auth'])->group(function () {
    // Route untuk halaman materi baru
    Route::get('/courses/{courseId}/materials/{materialId}/view', [MateriLihatController::class, 'index'])
        ->name('materi.lihat');
    
    // Route untuk AJAX grading
    Route::get('/courses/{courseId}/materials/{materialId}/grading/answers', [MateriLihatController::class, 'getAnswersForGrading'])
        ->name('materi.lihat.getAnswers');
    
    Route::post('/courses/{courseId}/materials/{materialId}/grading/submit', [MateriLihatController::class, 'submitGrade'])
        ->name('materi.lihat.submitGrade');
});

// Routes untuk Tes Kompetensi
Route::group(['prefix' => 'courses/{courseId}/modules/{modulId}/tes-kompetensi'], function () {
    
    // Routes untuk Guru/Admin
    Route::middleware(['auth'])->group(function() {
        
        Route::get('/', [TesKompetensiController::class, 'index'])->name('tes-kompetensi.index');
        
        // Update jenis tes kompetensi (pilihan ganda atau essay)
        Route::put('/jenis-tes', [TesKompetensiController::class, 'updateJenisTes'])
            ->name('tes-kompetensi.updateJenisTes');
        
        // Routes untuk Soal Pilihan Ganda
        Route::post('/soal-pilgan', [TesKompetensiController::class, 'storeSoalPilgan'])
            ->name('tes-kompetensi.storeSoalPilgan');
        
        Route::put('/soal-pilgan/{soalId}', [TesKompetensiController::class, 'updateSoalPilgan'])
            ->name('tes-kompetensi.updateSoalPilgan');
        
        Route::delete('/soal-pilgan/{soalId}', [TesKompetensiController::class, 'deleteSoalPilgan'])
            ->name('tes-kompetensi.deleteSoalPilgan');
        
        // Routes untuk Soal Essay
        Route::post('/soal-essay', [TesKompetensiController::class, 'storeUpdateSoalEssay'])
            ->name('tes-kompetensi.storeUpdateSoalEssay');
        
        // Routes untuk melihat jawaban siswa (Guru)
        Route::get('/jawaban/{type}', [TesKompetensiController::class, 'getJawabanSiswa'])
            ->name('tes-kompetensi.getJawabanSiswa')
            ->where('type', 'pilgan|essay');
        
        // Routes untuk penilaian
        Route::post('/grade-pilgan', [TesKompetensiController::class, 'gradeJawabanPilgan'])
            ->name('tes-kompetensi.gradeJawabanPilgan');
        
        Route::post('/grade-all-pilgan', [TesKompetensiController::class, 'gradeAllJawabanPilgan'])
            ->name('tes-kompetensi.gradeAllJawabanPilgan');
        
        // Route untuk statistik
        Route::get('/statistik', [TesKompetensiController::class, 'getStatistik'])
            ->name('tes-kompetensi.getStatistik');
        
        // Route untuk reset jawaban (hanya untuk development/testing)
        Route::delete('/reset-jawaban', [TesKompetensiController::class, 'resetJawaban'])
            ->name('tes-kompetensi.resetJawaban');
    });
    
    // Routes untuk Siswa
    Route::middleware(['auth'])->group(function() {
        
        // Submit jawaban pilihan ganda
        Route::post('/submit-pilgan', [TesKompetensiController::class, 'submitJawabanPilgan'])
            ->name('tes-kompetensi.submitJawabanPilgan');
        
        // Submit jawaban essay
        Route::post('/submit-essay', [TesKompetensiController::class, 'submitJawabanEssay'])
            ->name('tes-kompetensi.submitJawabanEssay');
    });
});



// Add these routes to your web.php file

// Diskusi Routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('courses/{courseId}/materials/{materialId}/diskusi')->group(function () {
        // Get all diskusi topics
        Route::get('/', [DiskusiController::class, 'index'])
            ->name('diskusi.index');
        
        // Create new diskusi topic (Guru only)
        Route::post('/', [DiskusiController::class, 'store'])
            ->name('diskusi.store');
        
        // Get diskusi detail
        Route::get('/{topikId}', [DiskusiController::class, 'show'])
            ->name('diskusi.show');
        
        // Add comment
        Route::post('/{topikId}/comments', [DiskusiController::class, 'addComment'])
            ->name('diskusi.addComment');
        
        // Edit comment
        Route::put('/{topikId}/comments/{komentarId}', [DiskusiController::class, 'editComment'])
            ->name('diskusi.editComment');
        
        // Delete comment
        Route::delete('/{topikId}/comments/{komentarId}', [DiskusiController::class, 'deleteComment'])
            ->name('diskusi.deleteComment');
        
        // Toggle like
        Route::post('/{topikId}/comments/{komentarId}/like', [DiskusiController::class, 'toggleLike'])
            ->name('diskusi.toggleLike');
        
        // Close topic (Guru only)
        Route::post('/{topikId}/close', [DiskusiController::class, 'closeTopic'])
            ->name('diskusi.close');
        
        // Reopen topic (Guru only)
        Route::post('/{topikId}/reopen', [DiskusiController::class, 'reopenTopic'])
            ->name('diskusi.reopen');
    });


    Route::get('/courses/{courseId}/materials/{materialId}/export/{component}', [ExportController::class, 'exportNilaiMateri'])
        ->name('materi.exportNilai');

        // Profile Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');
        Route::post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('profile.uploadPhoto');
    });
});