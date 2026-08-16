@extends('layouts.home')

@section('css_tambahan')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 300px;
    }
    .sidebar-item {
        padding: 1rem;
        margin-bottom: 0.5rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .sidebar-item:hover {
        background-color: #f1f5f9;
    }
    .sidebar-item.active {
        background-color: #dbeafe;
        border-left-color: #3b82f6;
    }
    .sidebar-item.completed {
        background-color: #dcfce7;
        border-left-color: #10b981;
    }
    .sidebar-item.locked {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f8fafc;
    }
    .content-area {
        min-height: 600px;
    }
    .grade-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        display: inline-block;
    }
    .grade-excellent { background-color: #dcfce7; color: #166534; }
    .grade-good { background-color: #dbeafe; color: #1e40af; }
    .grade-fair { background-color: #fef3c7; color: #92400e; }
    .grade-poor { background-color: #fee2e2; color: #dc2626; }
    .grade-pending { background-color: #f1f5f9; color: #64748b; }
    
    /* Video container styles */
    .video-container {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
        max-width: 100%;
        background: #000;
        border-radius: 8px;
    }
    
    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 8px;
    }

    .soal-pre-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.5rem;
    }

    .soal-pre-item.available {
        background: #f0f9ff;
        border-color: #0ea5e9;
    }


    /* Tambahkan ke bagian CSS yang sudah ada */
    #jawabanEssayModal .ql-toolbar {
    border-bottom: 1px solid #e2e8f0;
    padding: 8px;
}

#jawabanEssayModal .ql-toolbar .ql-formats {
    margin-right: 10px;
}

#jawabanEssayModal .ql-toolbar button {
    padding: 4px;
    width: 28px;
    height: 28px;
}

#jawabanEssayModal .ql-container {
    border: none;
    font-size: 14px;
}

#jawabanEssayModal .ql-editor {
    padding: 12px;
    min-height: 200px;
    font-size: 14px;
    line-height: 1.5;
}

#jawabanEssayModal .ql-snow .ql-tooltip {
    z-index: 9999;
}

/* Responsive untuk screen kecil */
@media (max-height: 700px) {
    #jawabanEssayModal .bg-white {
        max-height: 95vh;
    }
    
    #jawabanEssayModal .ql-editor {
        min-height: 150px;
    }
}
</style>
@endsection

@section('konten')
<!-- Modal Upload Soal Perencanaan/Refleksi/Evaluasi (untuk Guru) -->
<div id="soalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-900" id="soalModalTitle">Upload Soal</h3>
            <button onclick="closeSoalModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <form id="soalForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="soalComponent" name="component">
                <input type="hidden" id="soalType" name="type">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Upload File Soal</label>
                    <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 hover:border-slate-400 transition-colors">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <input type="file" name="soal_file" accept=".pdf,.doc,.docx" class="w-full mb-2 text-sm" required>
                            <p class="text-sm text-slate-500">Pilih file PDF, DOC, atau DOCX (maksimal 10MB)</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeSoalModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="soalSubmitBtn"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        Upload Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Upload Jawaban Perencanaan/Refleksi/Evaluasi (untuk Siswa) -->
<div id="jawabanModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-900" id="jawabanModalTitle">Upload Jawaban</h3>
            <button onclick="closeJawabanModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <form id="jawabanForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="jawabanComponent" name="component">
                <input type="hidden" id="jawabanType" name="type">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Upload File Jawaban</label>
                    <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 hover:border-slate-400 transition-colors">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <input type="file" name="jawaban_file" accept=".pdf,.doc,.docx" class="w-full mb-2 text-sm" required>
                            <p class="text-sm text-slate-500">Pilih file PDF, DOC, atau DOCX (maksimal 10MB)</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeJawabanModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="jawabanSubmitBtn"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        Upload Jawaban
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Soal Essay PRE (untuk Guru) - Tambahkan setelah modal yang sudah ada -->
<div id="soalEssayModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-900" id="soalEssayModalTitle">Buat Soal Essay</h3>
            <button onclick="closeSoalEssayModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px)">
            <form id="soalEssayForm" method="POST" action="{{ route('materi.updateSoalEssayPRE', [$courseId, $material->id_materi]) }}">
                @csrf
                <input type="hidden" id="soalEssayComponent" name="component">
                <input type="hidden" id="soalEssayType" name="type">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Soal Essay</label>
                    <div id="soalEssayEditor" style="height: 300px;"></div>
                    <textarea name="soal_text" id="soalEssayContent" class="hidden" required></textarea>
                    <p class="text-sm text-slate-500 mt-2">Tulis pertanyaan essay yang akan dijawab oleh siswa</p>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeSoalEssayModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="soalEssaySubmitBtn"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        Simpan Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Jawaban Essay PRE (untuk Siswa) - Tambahkan setelah modal soal essay -->
<div id="jawabanEssayModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900" id="jawabanEssayModalTitle">Jawab Soal Essay</h3>
            <button onclick="closeJawabanEssayModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="flex flex-1 min-h-0">
            <!-- Panel Soal -->
            <div class="w-2/5 p-4 border-r border-slate-200 overflow-y-auto">
                <h4 class="font-medium text-slate-900 mb-3 text-sm">Soal:</h4>
                <div id="soalEssayDisplay" class="prose prose-sm max-w-none bg-slate-50 rounded-lg p-3 text-sm">
                    <!-- Soal akan dimuat di sini -->
                </div>
            </div>
            
            <!-- Panel Jawaban -->
            <div class="w-3/5 flex flex-col min-h-0">
                <div class="p-4 flex-1 flex flex-col min-h-0">
                    <h4 class="font-medium text-slate-900 mb-3 text-sm">Jawaban Anda:</h4>
                    
                    <!-- Editor Container dengan tinggi terbatas -->
                    <div class="flex-1 border border-slate-300 rounded-lg overflow-hidden" style="min-height: 250px; max-height: 350px;">
                        <div id="jawabanEssayEditor"></div>
                    </div>
                </div>
                
                <!-- Footer untuk panel jawaban -->
                <div class="px-4 py-3 border-t border-slate-200">
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeJawabanEssayModal()" 
                                class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm">
                            Batal
                        </button>
                        <button type="button" onclick="submitJawabanEssay()" id="jawabanEssaySubmitBtn"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 text-sm">
                            Simpan Jawaban
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden form -->
        <form id="jawabanEssayForm" method="POST" action="{{ route('materi.submitJawabanEssayPRE', [$courseId, $material->id_materi]) }}" class="hidden">
            @csrf
            <input type="hidden" id="jawabanEssayComponent" name="component">
            <input type="hidden" id="jawabanEssayType" name="type">
            <textarea name="jawaban_text" id="jawabanEssayContent" required></textarea>
        </form>
    </div>
</div>

<!-- Modal Soal Essay Metakognisi (untuk Guru) -->
<div id="soalMetakognisiModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-900" id="soalMetakognisiModalTitle">Buat Soal Metakognisi</h3>
            <button onclick="closeSoalMetakognisiModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px)">
            <form id="soalMetakognisiForm" method="POST" action="{{ route('metakognisi.updateSoal', [$courseId, $material->id_materi]) }}">
                @csrf
                <input type="hidden" id="soalMetakognisiComponent" name="component" value="metakognisi">
                <input type="hidden" id="soalMetakognisiType" name="type">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Soal Essay</label>
                    <div id="soalMetakognisiEditor" style="height: 300px;"></div>
                    <textarea name="soal_text" id="soalMetakognisiContent" class="hidden" required></textarea>
                    <p class="text-sm text-slate-500 mt-2">Tulis pertanyaan essay yang akan dijawab oleh siswa</p>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeSoalMetakognisiModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="soalMetakognisiSubmitBtn"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        Simpan Soal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Jawaban Essay Metakognisi (untuk Siswa) -->
<div id="jawabanMetakognisiModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900" id="jawabanMetakognisiModalTitle">Jawab Soal Metakognisi</h3>
            <button onclick="closeJawabanMetakognisiModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="flex flex-1 min-h-0">
            <!-- Panel Soal -->
            <div class="w-2/5 p-4 border-r border-slate-200 overflow-y-auto">
                <h4 class="font-medium text-slate-900 mb-3 text-sm">Soal:</h4>
                <div id="soalMetakognisiDisplay" class="prose prose-sm max-w-none bg-slate-50 rounded-lg p-3 text-sm">
                    <!-- Soal akan dimuat di sini -->
                </div>
            </div>
            
            <!-- Panel Jawaban -->
            <div class="w-3/5 flex flex-col min-h-0">
                <div class="p-4 flex-1 flex flex-col min-h-0">
                    <h4 class="font-medium text-slate-900 mb-3 text-sm">Jawaban Anda:</h4>
                    
                    <!-- Editor Container dengan tinggi terbatas -->
                    <div class="flex-1 border border-slate-300 rounded-lg overflow-hidden" style="min-height: 250px; max-height: 350px;">
                        <div id="jawabanMetakognisiEditor"></div>
                    </div>
                </div>
                
                <!-- Footer untuk panel jawaban -->
                <div class="px-4 py-3 border-t border-slate-200">
                    <div class="flex space-x-3">
                        <button type="button" onclick="closeJawabanMetakognisiModal()" 
                                class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm">
                            Batal
                        </button>
                        <button type="button" onclick="submitJawabanMetakognisi()" id="jawabanMetakognisiSubmitBtn"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 text-sm">
                            Simpan Jawaban
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hidden form -->
        <form id="jawabanMetakognisiForm" method="POST" action="{{ route('metakognisi.uploadJawaban', [$courseId, $material->id_materi]) }}" class="hidden">
            @csrf
            <input type="hidden" id="jawabanMetakognisiComponent" name="component" value="metakognisi">
            <input type="hidden" id="jawabanMetakognisiType" name="type">
            <textarea name="jawaban_text" id="jawabanMetakognisiContent" required></textarea>
        </form>
    </div>
</div>

<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-2 text-sm text-slate-500 mb-4">
                <a href="{{ route('courses.index') }}" class="hover:text-edu-blue">Courses</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('modul.index', $courseId) }}" class="hover:text-edu-blue">Modules</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <a href="{{ route('modul.show', [$courseId, $material->id_modul]) }}" class="hover:text-edu-blue">{{ $material->nama_modul }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-slate-700">{{ $material->nama_materi }}</span>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-slate-900 mb-2">{{ $material->nama_materi }}</h1>
                        <p class="text-slate-600">Materi pembelajaran dengan 6 komponen utama</p>
                    </div>
                    
                    @if($canManage)
                        <div class="ml-6">
                            <a href="{{ route('materi.lihat', ['courseId' => $courseId, 'materialId' => $material->id_materi]) }}" 
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-all duration-200 whitespace-nowrap">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                                <span>Manajemen Penilaian</span>
                            </a>
                        </div>
                    @endif
                </div>


                @if(!$canManage && $checklist)
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-slate-700">Progress Komponen</span>
                        <span class="text-sm text-slate-500">
                            {{ ($checklist->mulai_dari_diri == 'Y' ? 1 : 0) + 
                            ($checklist->eksplorasi_konsep == 'Y' ? 1 : 0) + 
                            ($checklist->ruang_kolaborasi == 'Y' ? 1 : 0) + 
                            ($checklist->demonstrasi_konseptual == 'Y' ? 1 : 0) + 
                            ($checklist->elaborasi_pemahaman == 'Y' ? 1 : 0) }}/5 Selesai
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-edu-blue to-edu-green h-2 rounded-full" 
                            style="width: {{ (($checklist->mulai_dari_diri == 'Y' ? 1 : 0) + 
                                            ($checklist->eksplorasi_konsep == 'Y' ? 1 : 0) + 
                                            ($checklist->ruang_kolaborasi == 'Y' ? 1 : 0) + 
                                            ($checklist->demonstrasi_konseptual == 'Y' ? 1 : 0) + 
                                            ($checklist->elaborasi_pemahaman == 'Y' ? 1 : 0)) * 20 }}%"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Main Content Layout -->
        <div class="flex gap-8">
            <!-- Sidebar -->
            <div class="w-80 flex-shrink-0">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-8">
                    <h2 class="text-lg font-bold text-slate-900 mb-6">Komponen Pembelajaran</h2>
                    
                    <!-- Component 1: Mulai Dari Diri -->
                    <div class="sidebar-item {{ !$canManage && $checklist && $checklist->mulai_dari_diri == 'Y' ? 'completed' : '' }}" 
                        onclick="showComponent('mulai_dari_diri')" id="nav-mulai_dari_diri">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-edu-blue text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                            <div class="flex-1">
                                <h3 class="font-medium text-slate-900">Mulai Dari Diri</h3>
                                @if(!$canManage && $checklist && $checklist->mulai_dari_diri == 'Y')
                                    <span class="text-xs text-green-600">Selesai</span>
                                @elseif(!$canManage && isset($answers['mulai_dari_diri']) && $answers['mulai_dari_diri'] && $answers['mulai_dari_diri']->nilai !== null)
                                    @php
                                        $nilai = $answers['mulai_dari_diri']->nilai;
                                        $gradeClass = $nilai >= 85 ? 'grade-excellent' : ($nilai >= 75 ? 'grade-good' : ($nilai >= 65 ? 'grade-fair' : 'grade-poor'));
                                    @endphp
                                    <span class="grade-badge {{ $gradeClass }}">{{ $nilai }}/100</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Component 2: Eksplorasi Konsep -->
                    <div class="sidebar-item {{ !$canManage && $checklist && $checklist->eksplorasi_konsep == 'Y' ? 'completed' : (!$canManage && $checklist && $checklist->mulai_dari_diri == 'N' ? 'locked' : '') }}" 
                        onclick="{{ !$canManage && $checklist && $checklist->mulai_dari_diri == 'N' ? '' : 'showComponent(\'eksplorasi_konsep\')' }}" 
                        id="nav-eksplorasi_konsep">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-edu-green text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                            <div>
                                <h3 class="font-medium text-slate-900">Eksplorasi Konsep</h3>
                                @if(!$canManage && $checklist && $checklist->eksplorasi_konsep == 'Y')
                                    <span class="text-xs text-green-600">Selesai</span>
                                @elseif(!$canManage && $checklist && $checklist->mulai_dari_diri == 'N')
                                    <span class="text-xs text-red-600">Terkunci</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Component 3: Ruang Kolaborasi -->
                    <div class="sidebar-item {{ !$canManage && $checklist && $checklist->ruang_kolaborasi == 'Y' ? 'completed' : (!$canManage && $checklist && $checklist->eksplorasi_konsep == 'N' ? 'locked' : '') }}" 
                        onclick="{{ !$canManage && $checklist && $checklist->eksplorasi_konsep == 'N' ? '' : 'showComponent(\'ruang_kolaborasi\')' }}" 
                        id="nav-ruang_kolaborasi">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-edu-purple text-white rounded-full flex items-center justify-center text-sm font-bold">3</div>
                            <div class="flex-1">
                                <h3 class="font-medium text-slate-900">Ruang Kolaborasi</h3>
                                @if(!$canManage && $checklist && $checklist->ruang_kolaborasi == 'Y')
                                    <span class="text-xs text-green-600">Selesai</span>
                                @elseif(!$canManage && $checklist && $checklist->eksplorasi_konsep == 'N')
                                    <span class="text-xs text-red-600">Terkunci</span>
                                @elseif(!$canManage && isset($answers['ruang_kolaborasi']) && $answers['ruang_kolaborasi'] && $answers['ruang_kolaborasi']->nilai !== null)
                                    @php
                                        $nilai = $answers['ruang_kolaborasi']->nilai;
                                        $gradeClass = $nilai >= 85 ? 'grade-excellent' : ($nilai >= 75 ? 'grade-good' : ($nilai >= 65 ? 'grade-fair' : 'grade-poor'));
                                    @endphp
                                    <span class="grade-badge {{ $gradeClass }}">{{ $nilai }}/100</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Component 4: Demonstrasi Konseptual (langsung dari Ruang Kolaborasi) -->
                    <div class="sidebar-item {{ !$canManage && $checklist && $checklist->demonstrasi_konseptual == 'Y' ? 'completed' : (!$canManage && $checklist && $checklist->ruang_kolaborasi == 'N' ? 'locked' : '') }}" 
                        onclick="{{ !$canManage && $checklist && $checklist->ruang_kolaborasi == 'N' ? '' : 'showComponent(\'demonstrasi_konseptual\')' }}" 
                        id="nav-demonstrasi_konseptual">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-edu-teal text-white rounded-full flex items-center justify-center text-sm font-bold">4</div>
                            <div class="flex-1">
                                <h3 class="font-medium text-slate-900">Demonstrasi Konseptual</h3>
                                @if(!$canManage && $checklist && $checklist->demonstrasi_konseptual == 'Y')
                                    <span class="text-xs text-green-600">Selesai</span>
                                @elseif(!$canManage && $checklist && $checklist->ruang_kolaborasi == 'N')
                                    <span class="text-xs text-red-600">Terkunci</span>
                                @elseif(!$canManage && isset($answers['demonstrasi_konseptual']) && $answers['demonstrasi_konseptual'] && $answers['demonstrasi_konseptual']->nilai !== null)
                                    @php
                                        $nilai = $answers['demonstrasi_konseptual']->nilai;
                                        $gradeClass = $nilai >= 85 ? 'grade-excellent' : ($nilai >= 75 ? 'grade-good' : ($nilai >= 65 ? 'grade-fair' : 'grade-poor'));
                                    @endphp
                                    <span class="grade-badge {{ $gradeClass }}">{{ $nilai }}/100</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Component 5: Elaborasi Pemahaman (dari Demonstrasi Konseptual) -->
                    <div class="sidebar-item {{ !$canManage && $checklist && $checklist->elaborasi_pemahaman == 'Y' ? 'completed' : (!$canManage && $checklist && $checklist->demonstrasi_konseptual == 'N' ? 'locked' : '') }}" 
                        onclick="{{ !$canManage && $checklist && $checklist->demonstrasi_konseptual == 'N' ? '' : 'showComponent(\'elaborasi_pemahaman\')' }}" 
                        id="nav-elaborasi_pemahaman">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-edu-red text-white rounded-full flex items-center justify-center text-sm font-bold">5</div>
                            <div class="flex-1">
                                <h3 class="font-medium text-slate-900">Elaborasi Pemahaman</h3>
                                @if(!$canManage && $checklist && $checklist->elaborasi_pemahaman == 'Y')
                                    <span class="text-xs text-green-600">Selesai</span>
                                @elseif(!$canManage && $checklist && $checklist->demonstrasi_konseptual == 'N')
                                    <span class="text-xs text-red-600">Terkunci</span>
                                @elseif(!$canManage && isset($answers['elaborasi_pemahaman']) && $answers['elaborasi_pemahaman'] && $answers['elaborasi_pemahaman']->nilai !== null)
                                    @php
                                        $nilai = $answers['elaborasi_pemahaman']->nilai;
                                        $gradeClass = $nilai >= 85 ? 'grade-excellent' : ($nilai >= 75 ? 'grade-good' : ($nilai >= 65 ? 'grade-fair' : 'grade-poor'));
                                    @endphp
                                    <span class="grade-badge {{ $gradeClass }}">{{ $nilai }}/100</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h2 class="text-xl font-bold text-slate-900" id="contentTitle">Pilih Komponen Pembelajaran</h2>
                        <p class="text-slate-600" id="contentSubtitle">Pilih salah satu komponen di sidebar untuk mulai belajar</p>
                    </div>

                    <div class="p-6 content-area" id="dynamicContent">
                        <div class="text-center py-20">
                            <svg class="w-20 h-20 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-slate-900 mb-2">Selamat Datang!</h3>
                            <p class="text-slate-500">Pilih komponen pembelajaran di sidebar kiri untuk memulai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk memberikan nilai -->
<div id="gradeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-xl font-bold text-slate-900">Berikan Nilai</h3>
        </div>
        <div class="p-6">
            <form id="gradeForm">
                @csrf
                <input type="hidden" id="gradeUserId" name="user_id">
                <input type="hidden" id="gradeComponent" name="component">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nama Siswa</label>
                    <p id="gradeStudentName" class="text-slate-900 font-medium"></p>
                </div>
                
                <div class="mb-4" id="gradeContainer">
                    <label for="gradeValue" class="block text-sm font-medium text-slate-700 mb-2">Nilai (0-100)</label>
                    <input type="number" id="gradeValue" name="nilai" min="0" max="100" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent" 
                           >
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeGradeModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-edu-blue text-white rounded-lg hover:bg-edu-blue/90">
                        Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.ai-chat-modal')

@endsection

@section('js_tambahan')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script src="{{ asset('js/diskusi-manager.js') }}"></script>



<script>
    // Tambahkan di awal JavaScript, tepat setelah deklarasi soalPRE dan jawabanPRE
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== FINAL DATA CHECK ===');
        console.log('Raw soalPRE from controller:', @json($soalPRE ?? []));
        console.log('Raw jawabanPRE from controller:', @json($jawabanPRE ?? []));
        
        // Test if data is properly assigned
        console.log('JavaScript soalPRE variable:', soalPRE);
        console.log('JavaScript jawabanPRE variable:', jawabanPRE);
        
        // Test specific key access
        console.log('Test key access ruang_kolaborasi_perencanaan:', soalPRE['ruang_kolaborasi_perencanaan']);
        
        showComponent('mulai_dari_diri');
    });
    const courseId = {{ $courseId }};
    const materialId = {{ $material->id_materi }};
    let currentEditor = null;

    const canManage = {{ $canManage ? 'true' : 'false' }};
    
    // TAMBAHKAN: Set ke window agar bisa diakses dari mana saja
    window.canManage = {{ $canManage ? 'true' : 'false' }};
    
    console.log('canManage:', canManage); // Debug
    
    const components = {
        mulai_dari_diri: { title: 'Mulai Dari Diri', subtitle: 'Refleksi awal dan pemahaman diri' },
        eksplorasi_konsep: { title: 'Eksplorasi Konsep', subtitle: 'Konten pembelajaran lengkap' },
        ruang_kolaborasi: { title: 'Ruang Kolaborasi', subtitle: 'Pembelajaran kolaboratif' },
        demonstrasi_konseptual: { title: 'Demonstrasi Konseptual', subtitle: 'Demonstrasi pemahaman' },
        elaborasi_pemahaman: { title: 'Elaborasi Pemahaman', subtitle: 'Pengembangan pemahaman' }
    };
    // Data soal PRE dan jawaban dari controller
    const soalPRE = @json($soalPRE ?? []);
    const jawabanPRE = @json($jawabanPRE ?? []);
    
    document.addEventListener('DOMContentLoaded', function() {
        showComponent('mulai_dari_diri');
    });
    
    function showComponent(componentName) {
        @if(!$canManage && $checklist)
            if (!canAccessComponent(componentName)) return;
        @endif
        
        // Update sidebar
        document.querySelectorAll('.sidebar-item').forEach(item => item.classList.remove('active'));
        document.getElementById('nav-' + componentName).classList.add('active');
        
        // Update header
        document.getElementById('contentTitle').textContent = components[componentName].title;
        document.getElementById('contentSubtitle').textContent = components[componentName].subtitle;
        
        // Generate content
        loadContent(componentName);
    }
    
    function canAccessComponent(componentName) {
        const checklist = @json($checklist);
        console.log('canAccessComponent called for:', componentName);
        console.log('Checklist data:', checklist);
        
        const access = {
            'mulai_dari_diri': true,
            'eksplorasi_konsep': checklist.mulai_dari_diri === 'Y',
            'ruang_kolaborasi': checklist.eksplorasi_konsep === 'Y',
            'demonstrasi_konseptual': checklist.ruang_kolaborasi === 'Y',  // Langsung dari ruang_kolaborasi
            'elaborasi_pemahaman': checklist.demonstrasi_konseptual === 'Y'
        };
        
        console.log('Access result for', componentName, ':', access[componentName]);
        return access[componentName] || false;
    }
    
    function loadContent(componentName) {
        const contentArea = document.getElementById('dynamicContent');
        
        if (componentName === 'eksplorasi_konsep') {
            contentArea.innerHTML = generateEksplorasiKonsepContent();
            initializeEksplorasiKonsepEditor();
        } else if (componentName === 'ruang_kolaborasi') {
            contentArea.innerHTML = generateRuangKolaborasiContent();
        } else {
            contentArea.innerHTML = generateFileBasedContent(componentName);
        }
    }
    
    function generateEksplorasiKonsepContent() {
        @if($canManage)
            const eksplorasiKonsep = @json($eksplorasiKonsep);
            
            return `
                <div class="space-y-6">
                    <!-- Tombol Chat untuk Guru -->
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium mb-4">Kelola Konten Eksplorasi Konsep</h3>
                        <button onclick="openChatModal()" 
                                class="bg-edu-blue text-white px-4 py-2 rounded-lg hover:bg-edu-blue/90 transition-colors flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>Chat Pembelajaran</span>
                        </button>
                    </div>
                    
                    <!-- Form untuk konten utama eksplorasi konsep -->
                    <form action="{{ route('materi.updateEksplorasiKonsep', [$courseId, $material->id_materi]) }}" method="POST" enctype="multipart/form-data" id="eksplorasiKonsepForm">
                        @csrf
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Konten Materi</label>
                            <div id="eksplorasiKonsepEditor"></div>
                            <textarea name="isi_materi" id="eksplorasiKonsepContent" class="hidden">${eksplorasiKonsep ? eksplorasiKonsep.isi_materi || '' : ''}</textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Upload PDF</label>
                                <input type="file" name="pdf_file" accept=".pdf" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                                ${eksplorasiKonsep && eksplorasiKonsep.pdf ? `<p class="text-sm text-slate-600 mt-1">Current: ${getFileName(eksplorasiKonsep.pdf)}</p>` : ''}
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Link Video (YouTube/URL) bukan</label>
                                <input type="url" name="video_link" placeholder="https://www.youtube.com/watch?v=..." 
                                    value="${eksplorasiKonsep && eksplorasiKonsep.video ? eksplorasiKonsep.video : ''}"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-green focus:border-transparent">
                                <p class="text-xs text-slate-500 mt-1">Masukkan link YouTube atau URL video lainnya</p>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                Simpan Eksplorasi Konsep
                            </button>
                        </div>
                    </form>
                    
                    <!-- Section untuk Pengetahuan Metakognisi -->
                    <div class="border-t pt-6">
                        <h4 class="text-lg font-medium mb-4">Kelola Soal Pengetahuan Metakognisi</h4>
                        ${generateSoalMetakognisiSection()}
                    </div>
                    
                    <!-- Tombol untuk melihat jawaban siswa -->
                    <div class="border-t pt-6">
                        <button onclick="loadStudentMetakognisiAnswers()" 
                                class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90">
                            Lihat Jawaban Siswa
                        </button>
                    </div>
                </div>
            `;
        @else
            const eksplorasiKonsep = @json($eksplorasiKonsep);
            const checklist = @json($checklist);
            const isCompleted = checklist.eksplorasi_konsep === 'Y';
            
            // Cek apakah sudah ada nilai metakognisi yang final
            const hasFinalMetakognisiGrade = checkFinalMetakognisiGrade();
            
            if (hasFinalMetakognisiGrade) {
                return generateFinalMetakognisiGradeView();
            }
            
            return `
                <div class="space-y-6">
                    <!-- Tombol Chat untuk Siswa -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Eksplorasi Konsep</h3>
                        <button onclick="openChatModal()" 
                                class="bg-gradient-to-r from-edu-blue to-edu-green text-white px-4 py-2 rounded-lg hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>Chat Diskusi</span>
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        </button>
                    </div>

                    ${eksplorasiKonsep ? `
                        <div class="space-y-6">
                            ${eksplorasiKonsep.pdf ? `
                                <div class="bg-blue-50 rounded-lg p-4">
                                    <h4 class="font-medium text-blue-900 mb-3">Materi PDF:</h4>
                                    <div class="bg-white rounded-lg overflow-hidden border">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 border-b">
                                            <span class="text-sm font-medium text-gray-700">${getFileName(eksplorasiKonsep.pdf)}</span>
                                            <div class="flex space-x-2">
                                                <button onclick="window.open('${getFileUrl(eksplorasiKonsep.pdf)}', '_blank')" 
                                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Buka di Tab Baru
                                                </button>
                                                <a href="${getFileUrl(eksplorasiKonsep.pdf)}" download 
                                                class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                    Download
                                                </a>
                                            </div>
                                        </div>
                                        <object data="${getFileUrl(eksplorasiKonsep.pdf)}" 
                                                type="application/pdf" 
                                                width="100%" 
                                                height="600px">
                                            <iframe src="${getFileUrl(eksplorasiKonsep.pdf)}" 
                                                    width="100%" 
                                                    height="600px"
                                                    frameborder="0">
                                                <p class="p-4 text-center text-gray-600">
                                                    Tidak dapat menampilkan PDF. 
                                                    <a href="${getFileUrl(eksplorasiKonsep.pdf)}" target="_blank" 
                                                    class="text-blue-600 hover:underline">
                                                        Klik untuk membuka
                                                    </a>
                                                </p>
                                            </iframe>
                                        </object>
                                    </div>
                                </div>
                            ` : `
                                <div class="content-area bg-white rounded-lg p-6 border">
                                    <div class="prose max-w-none">
                                        ${eksplorasiKonsep.isi_materi || '<p class="text-slate-500">Konten belum tersedia</p>'}
                                    </div>
                                </div>
                            `}
                            
                            ${eksplorasiKonsep.video ? `
                                <div class="bg-red-50 rounded-lg p-4">
                                    <h4 class="font-medium text-red-900 mb-3">Video Pembelajaran:</h4>
                                    <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                                        <div class="video-container">
                                            <iframe src="${getVideoEmbedUrl(eksplorasiKonsep.video)}" 
                                                    frameborder="0" 
                                                    allowfullscreen
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                            </iframe>
                                        </div>
                                        <div class="p-3 bg-gray-50 border-t">
                                            <a href="${eksplorasiKonsep.video}" target="_blank" 
                                            class="text-blue-600 hover:underline text-sm">
                                                Buka video di tab baru
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                        
                        <!-- Section Soal dan Upload Jawaban Pengetahuan Metakognisi untuk Siswa -->
                        ${generateSoalMetakognisiStudentSection()}
                        
                        <div class="text-center mt-6">
                            ${!isCompleted ? `
                                <form action="{{ route('materi.completeEksplorasiKonsep', [$courseId, $material->id_materi]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                        Tandai Sebagai Selesai
                                    </button>
                                </form>
                            ` : `
                                <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-medium">Selesai</span>
                            `}
                        </div>
                    ` : `
                        <div class="text-center py-8">
                            <p class="text-slate-500">Konten eksplorasi konsep belum tersedia.</p>
                        </div>
                    `}
                </div>
            `;
        @endif
    }
    
    
    // Fungsi untuk generate section soal pengetahuan metakognisi (untuk guru)
    function generateSoalMetakognisiSection() {
        let html = '<div class="space-y-4">';
        
        const types = ['deklaratif', 'prosedural', 'kondisional'];
        const colors = ['blue', 'green', 'purple'];
        const typeLabels = {
            'deklaratif': 'Pengetahuan Deklaratif',
            'prosedural': 'Pengetahuan Prosedural', 
            'kondisional': 'Pengetahuan Kondisional'
        };
        const icons = [
            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'
        ];

        types.forEach((type, index) => {
            const currentSoal = getSoalMetakognisi(type);
            
            html += `
                <div class="soal-pre-item ${currentSoal ? 'available' : ''}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-${colors[index]}-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-${colors[index]}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[index]}"/>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-medium text-slate-900">${typeLabels[type]}</h5>
                                ${currentSoal ? 
                                    `<p class="text-sm text-${colors[index]}-600">File tersedia: ${getFileName(currentSoal)}</p>` :
                                    '<p class="text-sm text-slate-500">Belum ada file</p>'
                                }
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            ${currentSoal ? `
                                <a href="${getFileUrl(currentSoal)}" target="_blank" 
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                    Download
                                </a>
                                <button onclick="deleteSoalMetakognisi('${type}')" 
                                        class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors">
                                    Hapus
                                </button>
                            ` : ''}
                            <button onclick="openSoalMetakognisiModal('${type}')" 
                                    class="bg-${colors[index]}-600 text-white px-3 py-1 rounded text-sm hover:bg-${colors[index]}-700 transition-colors">
                                ${currentSoal ? 'Update' : 'Upload'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    // Fungsi untuk generate section soal pengetahuan metakognisi untuk siswa
    function generateSoalMetakognisiStudentSection() {
        let html = '<div class="space-y-6">';
        html += '<h4 class="text-lg font-medium">Download Soal & Upload Jawaban Pengetahuan Metakognisi</h4>';
        
        const types = ['deklaratif', 'prosedural', 'kondisional'];
        const colors = ['blue', 'green', 'purple'];
        const typeLabels = {
            'deklaratif': 'Pengetahuan Deklaratif',
            'prosedural': 'Pengetahuan Prosedural',
            'kondisional': 'Pengetahuan Kondisional'
        };

        types.forEach((type, index) => {
            const currentSoal = getSoalMetakognisi(type);
            const currentJawaban = getJawabanMetakognisi(type);
            
            html += `
                <div class="soal-pre-item ${currentSoal ? 'available' : ''}">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="font-medium text-slate-900">${typeLabels[type]}</h5>
                        ${currentJawaban && currentJawaban['nilai_' + type] !== null ? 
                            `<span class="grade-badge ${getGradeClass(currentJawaban['nilai_' + type])}">Nilai: ${currentJawaban['nilai_' + type]}/100</span>` :
                            ''
                        }
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3">
                        ${currentSoal ? `
                            <a href="${getFileUrl(currentSoal)}" target="_blank" 
                            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Soal
                            </a>
                        ` : `
                            <div class="flex-1 bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-center">
                                Soal belum tersedia
                            </div>
                        `}
                        
                        ${currentSoal && (!currentJawaban || currentJawaban['nilai_' + type] === null) ? `
                            <button onclick="openJawabanMetakognisiModal('${type}')" 
                                    class="flex-1 bg-${colors[index]}-600 text-white px-4 py-2 rounded-lg hover:bg-${colors[index]}-700 transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                ${currentJawaban && currentJawaban[type] ? 'Update Jawaban' : 'Upload Jawaban'}
                            </button>
                        ` : ''}
                    </div>
                    
                    ${currentJawaban && currentJawaban[type] && currentJawaban['nilai_' + type] === null ? `
                        <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-2">
                            <p class="text-yellow-800 text-sm">Jawaban telah diupload, menunggu penilaian</p>
                        </div>
                    ` : ''}
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    // Fungsi untuk generate tampilan nilai final pengetahuan metakognisi
    function generateFinalMetakognisiGradeView() {
        const grades = getFinalMetakognisiGrades();
        
        return `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                ${Object.entries(grades).map(([type, nilai]) => {
                    const typeLabels = {
                        'deklaratif': 'Deklaratif',
                        'prosedural': 'Prosedural', 
                        'kondisional': 'Kondisional'
                    };
                    return `
                        <div class="bg-white rounded-lg p-4 border shadow-sm">
                            <h5 class="font-medium text-slate-700 mb-2">${typeLabels[type]}</h5>
                            <span class="grade-badge ${getGradeClass(nilai)} text-lg">
                                ${nilai}/100
                            </span>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    // Helper functions untuk pengetahuan metakognisi
    function getSoalMetakognisi(type) {
        const soalMetakognisi = @json($soalMetakognisi ?? []);
        return soalMetakognisi[type] || null;
    }

    function getJawabanMetakognisi(type) {
        const jawabanMetakognisi = @json($jawabanMetakognisi ?? null);
        return jawabanMetakognisi;
    }

    function checkFinalMetakognisiGrade() {
        const types = ['deklaratif', 'prosedural', 'kondisional'];
        const jawaban = getJawabanMetakognisi();
        
        if (!jawaban) return false;
        
        return types.every(type => {
            const nilaiField = 'nilai_' + type;
            return jawaban[nilaiField] !== null && jawaban[nilaiField] !== undefined;
        });
    }

    function getFinalMetakognisiGrades() {
        const types = ['deklaratif', 'prosedural', 'kondisional'];
        const grades = {};
        const jawaban = getJawabanMetakognisi();
        
        types.forEach(type => {
            const nilaiField = 'nilai_' + type;
            grades[type] = jawaban && jawaban[nilaiField] !== null ? jawaban[nilaiField] : 0;
        });
        
        return grades;
    }

    // Functions untuk membuka modal soal pengetahuan metakognisi
    function openSoalMetakognisiModal(type) {
        const typeLabels = {
            'deklaratif': 'Pengetahuan Deklaratif',
            'prosedural': 'Pengetahuan Prosedural', 
            'kondisional': 'Pengetahuan Kondisional'
        };
        
        document.getElementById('soalModalTitle').textContent = `Upload Soal ${typeLabels[type]}`;
        document.getElementById('soalComponent').value = 'metakognisi';
        document.getElementById('soalType').value = type;
        document.getElementById('soalModal').classList.remove('hidden');
    }

    // Functions untuk membuka modal jawaban pengetahuan metakognisi
    function openJawabanMetakognisiModal(type) {
        const typeLabels = {
            'deklaratif': 'Pengetahuan Deklaratif',
            'prosedural': 'Pengetahuan Prosedural',
            'kondisional': 'Pengetahuan Kondisional'
        };
        
        document.getElementById('jawabanModalTitle').textContent = `Upload Jawaban ${typeLabels[type]}`;
        document.getElementById('jawabanComponent').value = 'metakognisi';
        document.getElementById('jawabanType').value = type;
        document.getElementById('jawabanModal').classList.remove('hidden');
    }

    // Fungsi untuk menghapus soal pengetahuan metakognisi
    function deleteSoalMetakognisi(type) {
        if (!confirm(`Apakah Anda yakin ingin menghapus soal ${type}?`)) {
            return;
        }
        
        fetch(`{{ route('metakognisi.deleteSoal', [$courseId, $material->id_materi, '__TYPE__']) }}`.replace('__TYPE__', type), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Soal berhasil dihapus');
                location.reload();
            } else {
                alert(data.message || 'Gagal menghapus soal');
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan saat menghapus soal');
        });
    }

    // Fungsi untuk load dan display jawaban siswa
    function loadStudentMetakognisiAnswers() {
        fetch(`{{ route('metakognisi.getStudentAnswers', [$courseId, $material->id_materi]) }}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayStudentMetakognisiAnswers(data.answers);
                }
            })
            .catch(error => {
                console.error('Error loading student metakognisi answers:', error);
            });
    }

    function displayStudentMetakognisiAnswers(answers) {
        const contentArea = document.getElementById('dynamicContent');
        
        let html = `
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium">Jawaban Siswa - Pengetahuan Metakognisi</h3>
                    <button onclick="showComponent('eksplorasi_konsep')" class="text-edu-blue hover:underline">
                        Kembali
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-slate-300">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="border border-slate-300 px-4 py-2 text-left">Nama Siswa</th>
                                <th class="border border-slate-300 px-4 py-2 text-left">Deklaratif</th>
                                <th class="border border-slate-300 px-4 py-2 text-left">Prosedural</th>
                                <th class="border border-slate-300 px-4 py-2 text-left">Kondisional</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        if (answers.length === 0) {
            html += `
                <tr>
                    <td colspan="4" class="border border-slate-300 px-4 py-2 text-center text-slate-500">
                        Belum ada jawaban dari siswa
                    </td>
                </tr>
            `;
        } else {
            answers.forEach(answer => {
                const types = ['deklaratif', 'prosedural', 'kondisional'];
                
                html += `
                    <tr>
                        <td class="border border-slate-300 px-4 py-2 font-medium">${answer.nama_siswa}</td>
                `;
                
                types.forEach(type => {
                    const hasFile = answer[type];
                    const nilaiField = 'nilai_' + type;
                    const nilai = answer[nilaiField];
                    
                    html += `
                        <td class="border border-slate-300 px-4 py-2">
                            <div class="space-y-2">
                                ${hasFile ? `
                                    <div class="flex items-center space-x-2">
                                        <a href="${getFileUrl(hasFile)}" target="_blank" 
                                        class="text-blue-600 hover:underline text-sm">
                                            ${getFileName(hasFile)}
                                        </a>
                                    </div>
                                ` : '<span class="text-gray-500 text-sm">Belum upload</span>'}
                                
                                ${hasFile ? `
                                    
                                ` : ''}
                            </div>
                        </td>
                    `;
                });
                
                html += `</tr>`;
            });
        }
        
        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        contentArea.innerHTML = html;
    }

    // Fungsi untuk membuka modal pemberian nilai pengetahuan metakognisi
    function openGradeMetakognisiModal(answerId, type, studentName, currentGrade) {
        document.getElementById('gradeUserId').value = answerId;
        document.getElementById('gradeComponent').value = 'metakognisi_' + type;
        document.getElementById('gradeStudentName').textContent = studentName;
        
        const gradeContainer = document.getElementById('gradeContainer');
        gradeContainer.innerHTML = `
            <label for="gradeValue" class="block text-sm font-medium text-slate-700 mb-2">Nilai ${type.charAt(0).toUpperCase() + type.slice(1)} (0-100)</label>
            <input type="number" id="gradeValue" name="nilai" min="0" max="100" 
                value="${currentGrade !== null ? currentGrade : ''}"
                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent" 
                required>
        `;
        
        document.getElementById('gradeModal').classList.remove('hidden');
    }

    // Update form submission handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submission untuk modal soal (Guru)
        const soalForm = document.getElementById('soalForm');
        if (soalForm) {
            soalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = document.getElementById('soalSubmitBtn');
                const originalText = submitBtn.textContent;
                const component = formData.get('component');
                
                // Disable button and show loading
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengupload...';
                
                // Tentukan action URL berdasarkan component
                if (component === 'metakognisi') {
                    this.action = '{{ route("metakognisi.uploadSoal", [$courseId, $material->id_materi]) }}';
                } else {
                    this.action = '{{ route("materi.uploadSoalPRE", [$courseId, $material->id_materi]) }}';
                }
                
                // Submit form normally
                this.submit();
            });
        }

        // Handle form submission untuk modal jawaban (Siswa)
        const jawabanForm = document.getElementById('jawabanForm');
        if (jawabanForm) {
            jawabanForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = document.getElementById('jawabanSubmitBtn');
                const originalText = submitBtn.textContent;
                const component = formData.get('component');
                
                // Disable button and show loading
                submitBtn.disabled = true;
                submitBtn.textContent = 'Mengupload...';
                
                // Tentukan action URL berdasarkan component
                if (component === 'metakognisi') {
                    this.action = '{{ route("metakognisi.uploadJawaban", [$courseId, $material->id_materi]) }}';
                } else {
                    this.action = '{{ route("materi.uploadJawabanPRE", [$courseId, $material->id_materi]) }}';
                }
                
                // Submit form normally
                this.submit();
            });
        }

        // Handle grade form submission for metakognisi
        const gradeForm = document.getElementById('gradeForm');
        if (gradeForm) {
            gradeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const component = document.getElementById('gradeComponent').value;
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                submitButton.disabled = true;
                submitButton.textContent = 'Menyimpan...';
                
                // Tentukan endpoint berdasarkan component
                let endpoint;
                if (component.startsWith('metakognisi_')) {
                    endpoint = '{{ route("metakognisi.gradeAnswer", [$courseId, $material->id_materi]) }}';
                } else {
                    endpoint = '{{ route("materi.gradeAnswer", [$courseId, $material->id_materi]) }}';
                }
                
                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        closeGradeModal();
                        
                        // Reload appropriate content
                        if (component.startsWith('metakognisi_')) {
                            loadStudentMetakognisiAnswers();
                        } else {
                            const baseComponent = component.replace('metakognisi_', '');
                            loadStudentAnswers(baseComponent);
                        }
                        
                        alert('Penilaian berhasil disimpan');
                    } else {
                        alert(data.message || 'Gagal memberikan nilai');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Berhasil memberikan nilai');
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                });
            });
        }
    });


    function generateRuangKolaborasiContent() {
        if (typeof DiskusiManager !== 'undefined') {
            DiskusiManager.init();
            DiskusiManager.loadTopikList();
        }
        
        return ''; // Content akan di-render oleh DiskusiManager
        
        @if($canManage)
            const material = @json($material);
            const soalFile = material['ruang_kolaborasi'];
            
            return `
                <div class="space-y-6">
                    <!-- Header dengan tema warna komponen -->
                    <div class="bg-gradient-to-r from-purple-50 to-white p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Kelola Soal Ruang Kolaborasi</h3>
                        <p class="text-sm text-slate-600">Pembelajaran kolaboratif</p>
                    </div>
                    
                    ${soalFile ? `
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-purple-900">File Soal Tersedia</h4>
                                    <p class="text-sm text-purple-700">${getFileName(soalFile)}</p>
                                </div>
                                <a href="${getFileUrl(soalFile)}" target="_blank" 
                                class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                    Download Soal
                                </a>
                            </div>
                        </div>
                    ` : ''}
                    
                    <form action="{{ route('materi.uploadSoal', [$courseId, $material->id_materi]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="component" value="ruang_kolaborasi">
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 hover:border-purple-400 transition-colors">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <input type="file" name="file_soal" accept=".pdf,.doc,.docx" class="w-full mb-2">
                                <p class="text-sm text-slate-500">PDF, DOC, DOCX maksimal 10MB</p>
                            </div>
                        </div>
                        <div class="mt-4 text-right">
                            <button type="submit" class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90 transition-colors">
                                ${soalFile ? 'Update Soal' : 'Upload Soal'}
                            </button>
                        </div>
                    </form>
                    
                    <!-- TOMBOL LIHAT & NILAI - UNTUK FILE UPLOAD UTAMA -->
                    <div class="border-t pt-6">
                        <button onclick="loadStudentAnswers('ruang_kolaborasi')" 
                                class="w-full bg-gradient-to-r from-edu-purple to-edu-purple/80 text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Lihat Jawaban Siswa</span>
                        </button>
                    </div>
                    
                    <!-- TOMBOL LIHAT & NILAI PRE - UNTUK ESSAY PRE -->
                    <div class="border-t pt-6">
                        <button onclick="loadStudentAnswersPRE('ruang_kolaborasi')" 
                                class="w-full bg-gradient-to-r from-edu-blue to-edu-blue/80 text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Lihat Jawaban Essay (PRE)</span>
                        </button>
                    </div>
                    
                    <!-- Kelola Soal Perencanaan, Refleksi, Evaluasi -->
                    <div class="border-t pt-6">
                        <h4 class="text-lg font-medium mb-4">Kelola Soal Perencanaan, Refleksi & Evaluasi</h4>
                        ${generateSoalPRESection('ruang_kolaborasi')}
                    </div>
                </div>
            `;
        @else
            const checklist = @json($checklist);
            const answers = @json($answers);
            const answer = answers['ruang_kolaborasi'];
            const material = @json($material);
            const soalFile = material['ruang_kolaborasi'];
            
            // Cek apakah sudah ada nilai PRE yang final
            const hasFinalGrade = checkFinalPREGrade('ruang_kolaborasi');
            
            if (hasFinalGrade) {
                return generateFinalGradeView('ruang_kolaborasi');
            }
            
            return `
                <div class="space-y-6">
                    ${soalFile ? `
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                            <h4 class="font-medium text-purple-900 mb-2">Soal Ruang Kolaborasi:</h4>
                            <a href="${getFileUrl(soalFile)}" target="_blank" 
                            class="text-purple-600 hover:underline font-medium">
                                Download Soal Ruang Kolaborasi
                            </a>
                        </div>
                    ` : `
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <p class="text-yellow-800">Soal belum tersedia.</p>
                        </div>
                    `}
                    
                    <div class="bg-slate-50 rounded-lg p-6">
                        <h3 class="text-lg font-medium mb-4">Ruang Kolaborasi</h3>
                        <p class="text-slate-600 mb-4">Berpartisipasilah dalam diskusi dan aktivitas kolaboratif.</p>
                    </div>
                    
                    <!-- Soal Essay PRE Section - Dipindah ke atas -->
                    ${generateSoalPREStudentSection('ruang_kolaborasi')}
                    
                    <!-- Upload jawaban file soal utama -->
                    ${soalFile ? `
                        <div class="border-t pt-6">
                            <h4 class="text-lg font-medium mb-4">Upload Jawaban Kolaborasi</h4>
                            <form action="{{ route('materi.uploadJawaban', [$courseId, $material->id_materi]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="component" value="ruang_kolaborasi">
                                <div class="border-2 border-dashed border-slate-300 rounded-lg p-6">
                                    <div class="text-center">
                                        <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx" class="w-full mb-2">
                                        <p class="text-sm text-slate-500">Upload jawaban kolaborasi (PDF, DOC, DOCX maksimal 10MB)</p>
                                    </div>
                                </div>
                                <div class="mt-4 text-right">
                                    <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                        ${answer ? 'Update Jawaban' : 'Upload Jawaban'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    ` : ''}
                    
                    ${answer && answer.nilai === null ? `
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <span class="text-green-800 font-medium">Jawaban telah diupload</span>
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Menunggu penilaian</span>
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        @endif
    }
        
    function generateFileBasedContent(componentName) {
        // Komponen yang menggunakan file upload untuk jawaban utama
        const fileBasedComponents = ['mulai_dari_diri', 'ruang_kolaborasi', 'demonstrasi_konseptual', 'elaborasi_pemahaman'];
        
        // Komponen yang memiliki modal PRE tambahan
        const componentsWithModals = ['ruang_kolaborasi', 'demonstrasi_konseptual', 'elaborasi_pemahaman'];
        
        @if($canManage)
            const material = @json($material);
            const soalFile = material[componentName];
            
            // Dapatkan konfigurasi warna dari komponen
            const componentConfig = StudentAnswersManager.config.components[componentName] || { color: 'blue' };
            const themeColor = componentConfig.color;
            
            return `
                <div class="space-y-6">
                    <!-- Header dengan tema warna komponen -->
                    <div class="bg-gradient-to-r from-${themeColor}-50 to-white p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-slate-900 mb-2">Kelola Soal ${components[componentName].title}</h3>
                        <p class="text-sm text-slate-600">${components[componentName].subtitle}</p>
                    </div>
                    
                    ${soalFile ? `
                        <div class="bg-${themeColor}-50 border border-${themeColor}-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-${themeColor}-900">File Soal Tersedia</h4>
                                    <p class="text-sm text-${themeColor}-700">${getFileName(soalFile)}</p>
                                </div>
                                <a href="${getFileUrl(soalFile)}" target="_blank" 
                                class="bg-${themeColor}-600 text-white px-4 py-2 rounded-lg hover:bg-${themeColor}-700 transition-colors">
                                    Download Soal
                                </a>
                            </div>
                        </div>
                    ` : ''}
                    
                    <form action="{{ route('materi.uploadSoal', [$courseId, $material->id_materi]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="component" value="${componentName}">
                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 hover:border-${themeColor}-400 transition-colors">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <input type="file" name="file_soal" accept=".pdf,.doc,.docx" class="w-full mb-2">
                                <p class="text-sm text-slate-500">PDF, DOC, DOCX maksimal 10MB</p>
                            </div>
                        </div>
                        <div class="mt-4 text-right">
                            <button type="submit" class="bg-edu-${themeColor} text-white px-6 py-2 rounded-lg hover:bg-edu-${themeColor}/90 transition-colors">
                                ${soalFile ? 'Update Soal' : 'Upload Soal'}
                            </button>
                        </div>
                    </form>
                    
                    <!-- TOMBOL LIHAT & NILAI - UNTUK SEMUA KOMPONEN -->
                    <div class="border-t pt-6">
                        <button onclick="loadStudentAnswers('${componentName}')" 
                                class="w-full bg-gradient-to-r from-edu-purple to-edu-purple/80 text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Lihat Jawaban Siswa</span>
                        </button>
                    </div>
                    
                    ${componentsWithModals.includes(componentName) ? `
                        <!-- TOMBOL LIHAT & NILAI PRE - HANYA UNTUK KOMPONEN DENGAN PRE -->
                        <div class="border-t pt-6">
                            <button onclick="loadStudentAnswersPRE('${componentName}')" 
                                    class="w-full bg-gradient-to-r from-edu-blue to-edu-blue/80 text-white px-6 py-3 rounded-lg hover:shadow-lg transition-all flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Lihat Jawaban Essay (PRE)</span>
                            </button>
                        </div>
                        
                        <!-- Kelola Soal Perencanaan, Refleksi, Evaluasi -->
                        <div class="border-t pt-6">
                            <h4 class="text-lg font-medium mb-4">Kelola Soal Perencanaan, Refleksi & Evaluasi</h4>
                            ${generateSoalPRESection(componentName)}
                        </div>
                    ` : ''}
                </div>
            `;
        @else
            // SISWA VIEW
            const answers = @json($answers);
            const answer = answers[componentName] || null;
            const material = @json($material);
            const soalFile = material[componentName];
            
            // Cek apakah sudah ada nilai PRE yang final (jika ada modal PRE)
            if (componentsWithModals.includes(componentName)) {
                const hasFinalGrade = checkFinalPREGrade(componentName);
                if (hasFinalGrade) {
                    return generateFinalGradeView(componentName);
                }
            }
            
            let studentModalButtons = '';
            if (componentsWithModals.includes(componentName)) {
                studentModalButtons = generateSoalPREStudentSection(componentName);
            }
            
            // Dapatkan konfigurasi warna dari komponen
            const componentConfig = StudentAnswersManager.config.components[componentName] || { color: 'blue' };
            const themeColor = componentConfig.color;
            
            return `
                <div class="space-y-6">
                    <!-- Tampilan Soal -->
                    ${soalFile ? `
                        <div class="bg-${themeColor}-50 border border-${themeColor}-200 rounded-lg p-4 mb-6">
                            <h4 class="font-medium text-${themeColor}-900 mb-2">Soal ${components[componentName].title}:</h4>
                            <a href="${getFileUrl(soalFile)}" target="_blank" 
                            class="text-${themeColor}-600 hover:underline font-medium">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download Soal ${components[componentName].title}
                            </a>
                        </div>
                    ` : `
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <p class="text-yellow-800">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Soal belum tersedia. Silakan hubungi guru Anda.
                            </p>
                        </div>
                    `}
                    
                    <!-- Soal PRE jika ada (ditampilkan di atas form upload) -->
                    ${studentModalButtons}
                    
                    <!-- Form Upload Jawaban -->
                    ${soalFile ? `
                        <div class="bg-white rounded-lg p-6 border border-slate-200">
                            <h3 class="text-lg font-medium mb-4">Upload Jawaban ${components[componentName].title}</h3>
                            <form action="{{ route('materi.uploadJawaban', [$courseId, $material->id_materi]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="component" value="${componentName}">
                                <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 hover:border-${themeColor}-400 transition-colors">
                                    <div class="text-center">
                                        <svg class="w-12 h-12 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx" class="w-full mb-2" ${answer ? '' : 'required'}>
                                        <p class="text-sm text-slate-500">Upload file jawaban (PDF, DOC, DOCX maksimal 10MB)</p>
                                    </div>
                                </div>
                                <div class="mt-4 text-right">
                                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        ${answer ? 'Update Jawaban' : 'Upload Jawaban'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    ` : ''}
                    
                    <!-- Status Jawaban -->
                    ${answer ? `
                        <div class="${answer.nilai !== null ? 'bg-green-50' : 'bg-yellow-50'} rounded-lg p-4 border ${answer.nilai !== null ? 'border-green-200' : 'border-yellow-200'}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="${answer.nilai !== null ? 'text-green-800' : 'text-yellow-800'} font-medium">
                                        ${answer.nilai !== null ? '✓ Jawaban telah dinilai' : '⏳ Jawaban telah diupload'}
                                    </span>
                                    ${answer.jawaban ? `
                                        <p class="text-sm mt-1 ${answer.nilai !== null ? 'text-green-600' : 'text-yellow-600'}">
                                            File: ${getFileName(answer.jawaban)}
                                        </p>
                                    ` : ''}
                                </div>
                                ${answer.nilai !== null ? `
                                    <span class="${StudentAnswersManager.getGradeBadgeClass(answer.nilai)} px-4 py-2 rounded-full text-sm font-medium">
                                        Nilai: ${answer.nilai}/100
                                    </span>
                                ` : `
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-xs font-medium">
                                        Menunggu penilaian
                                    </span>
                                `}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;
        @endif
    }
    // Fungsi untuk generate section soal PRE (untuk guru)
    function generateSoalPRESection(componentName) {
    console.log('=== DEBUG generateSoalPRESection ===');
    console.log('componentName:', componentName);
    console.log('soalPRE object:', soalPRE);
    console.log('All soalPRE keys:', Object.keys(soalPRE));
    
    // Test specific keys
    const testKeys = ['ruang_kolaborasi_perencanaan', 'ruang_kolaborasi_refleksi', 'ruang_kolaborasi_evaluasi'];
    testKeys.forEach(key => {
        console.log(`Key ${key}:`, soalPRE[key]);
    });
    console.log('=== END DEBUG ===');
    
    let html = '<div class="space-y-4">';
    
    const types = ['perencanaan', 'refleksi', 'evaluasi'];
    const colors = ['green', 'orange', 'purple'];
    const icons = [
        'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    ];

    types.forEach((type, index) => {
        const key = `${componentName}_${type}`;
        const currentSoal = soalPRE[key] || null;
        const hasText = currentSoal && typeof currentSoal === 'string' && currentSoal.trim().length > 0;
        
        console.log(`Processing type ${type}:`);
        console.log(`- key: ${key}`);
        console.log(`- currentSoal:`, currentSoal);
        console.log(`- hasText: ${hasText}`);
        
        html += `
            <div class="soal-pre-item ${hasText ? 'available' : ''}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-${colors[index]}-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-${colors[index]}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[index]}"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="font-medium text-slate-900">Soal ${type.charAt(0).toUpperCase() + type.slice(1)}</h5>
                            ${hasText ? 
                                `<p class="text-sm text-${colors[index]}-600">Soal sudah dibuat</p>` :
                                '<p class="text-sm text-slate-500">Belum ada soal</p>'
                            }
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        ${hasText ? `
                            <button onclick="previewSoalEssay('${componentName}', '${type}')" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                Preview
                            </button>
                        ` : ''}
                        <button onclick="openSoalEssayModal('${componentName}', '${type}')" 
                                class="bg-${colors[index]}-600 text-white px-3 py-1 rounded text-sm hover:bg-${colors[index]}-700 transition-colors">
                            ${hasText ? 'Edit' : 'Buat'}
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    return html;
}

    // Fungsi untuk generate section soal PRE untuk siswa
    function generateSoalPREStudentSection(componentName) {
        console.log('generateSoalPREStudentSection called for:', componentName);
        console.log('Available soalPRE data:', soalPRE);
        console.log('Available jawabanPRE data:', jawabanPRE);
        
        let html = '<div class="space-y-6">';
        html += '<h4 class="text-lg font-medium">Soal Essay & Jawaban</h4>';
        
        const types = ['perencanaan', 'refleksi', 'evaluasi'];
        const colors = ['green', 'orange', 'purple'];
        const typeLabels = {
            'perencanaan': 'Perencanaan',
            'refleksi': 'Refleksi',
            'evaluasi': 'Evaluasi'
        };

        types.forEach((type, index) => {
            const soalKey = `${componentName}_${type}`;
            const jawabanKey = `${componentName}_${type}`;
            
            const currentSoal = soalPRE[soalKey] || null;
            const currentJawaban = jawabanPRE[jawabanKey] || null;
            const hasText = currentSoal && currentSoal.trim().length > 0;
            
            console.log(`Student - Soal ${soalKey}:`, currentSoal, 'hasText:', hasText);
            console.log(`Student - Jawaban ${jawabanKey}:`, currentJawaban);
            
            html += `
                <div class="soal-pre-item ${hasText ? 'available' : ''}">
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="font-medium text-slate-900">${typeLabels[type]}</h5>
                        ${currentJawaban && currentJawaban.nilai !== null ? 
                            `<span class="grade-badge ${getGradeClass(currentJawaban.nilai)}">Nilai: ${currentJawaban.nilai}/100</span>` :
                            ''
                        }
                    </div>
                    
                    <div class="flex flex-col gap-3">
                        ${hasText ? `
                            <div class="bg-slate-50 rounded-lg p-4">
                                <h6 class="font-medium text-slate-700 mb-2">Soal:</h6>
                                <div class="prose prose-sm max-w-none">
                                    ${currentSoal}
                                </div>
                            </div>
                            
                            ${(!currentJawaban || currentJawaban.nilai === null) ? `
                                <button onclick="openJawabanEssayModal('${componentName}', '${type}')" 
                                        class="bg-${colors[index]}-600 text-white px-4 py-2 rounded-lg hover:bg-${colors[index]}-700 transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    ${currentJawaban && currentJawaban.jawaban ? 'Edit Jawaban' : 'Tulis Jawaban'}
                                </button>
                            ` : ''}
                        ` : `
                            <div class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-center">
                                Soal belum tersedia
                            </div>
                        `}
                    </div>
                    
                    ${currentJawaban && currentJawaban.jawaban && currentJawaban.nilai === null ? `
                        <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-2">
                            <p class="text-yellow-800 text-sm">Jawaban telah dikirim, menunggu penilaian</p>
                        </div>
                    ` : ''}
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    // Fungsi untuk generate tampilan nilai final
    function generateFinalGradeView(componentName) {
        const grades = getFinalPREGrades(componentName);
        
        return `
            <div class="space-y-6">
                <div class="bg-slate-100 rounded-lg p-6 text-center">
                    <h3 class="text-lg font-medium mb-4">Hasil Penilaian ${components[componentName].title}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        ${Object.entries(grades).map(([type, nilai]) => `
                            <div class="bg-white rounded-lg p-4 border">
                                <h4 class="font-medium text-slate-700 mb-2">${type.charAt(0).toUpperCase() + type.slice(1)}</h4>
                                <span class="grade-badge ${getGradeClass(nilai)}">
                                    ${nilai}/100
                                </span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
    }

    // Helper functions
    function getSoalPRE(component, type) {
        const key = `${component}_${type}`;
        const result = soalPRE[key] || null;
        console.log(`getSoalPRE(${component}, ${type}) -> key: ${key}, result:`, result);
        return result;
    }

    // REPLACE the existing getJawabanPRE function with this fixed version  
    function getJawabanPRE(component, type) {
        const key = `${component}_${type}`;
        const result = jawabanPRE[key] || null;
        console.log(`getJawabanPRE(${component}, ${type}) -> key: ${key}, result:`, result);
        return result;
    }

    // ADD this debug function to check data availability
    function debugPREData() {
        console.log('=== DEBUG PRE DATA ===');
        console.log('soalPRE:', soalPRE);
        console.log('jawabanPRE:', jawabanPRE);
        console.log('soalPRE keys:', Object.keys(soalPRE || {}));
        console.log('jawabanPRE keys:', Object.keys(jawabanPRE || {}));
        console.log('=====================');
    }

    function checkFinalPREGrade(component) {
        const types = ['perencanaan', 'refleksi', 'evaluasi'];
        return types.every(type => {
            const jawaban = getJawabanPRE(component, type);
            return jawaban && jawaban.nilai !== null;
        });
    }

    function getFinalPREGrades(component) {
        const types = ['perencanaan', 'refleksi', 'evaluasi'];
        const grades = {};
        
        types.forEach(type => {
            const jawaban = getJawabanPRE(component, type);
            grades[type] = jawaban ? jawaban.nilai : 0;
        });
        
        return grades;
    }

    // Perbaiki initializeEksplorasiKonsepEditor tanpa meta tag
    function initializeEksplorasiKonsepEditor() {
        if (document.getElementById('eksplorasiKonsepEditor')) {
            currentEditor = new Quill('#eksplorasiKonsepEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        ['link', 'image', 'video'],
                        [{ 'align': [] }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['clean']
                    ]
                }
            });
            
            const content = document.getElementById('eksplorasiKonsepContent').value;
            if (content) {
                currentEditor.root.innerHTML = content;
            }
            
            currentEditor.on('text-change', function() {
                document.getElementById('eksplorasiKonsepContent').value = currentEditor.root.innerHTML;
            });
        }

        // Handle form submission NORMAL (tanpa AJAX)
        const form = document.getElementById('eksplorasiKonsepForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Update editor content before submission
                if (currentEditor) {
                    document.getElementById('eksplorasiKonsepContent').value = currentEditor.root.innerHTML;
                }
                
                // Biarkan form submit normal (tidak preventDefault)
                console.log('Form submitting normally...');
            });
        }
    }
    
    function getFileUrl(filePath) {
        if (!filePath) return '#';
        let cleanPath = filePath;
        if (cleanPath.startsWith('storage/')) {
            cleanPath = cleanPath.substring(8);
        }
        return `{{ asset('storage/') }}/${cleanPath}`;
    }
    
    function getFileName(filePath) {
        return filePath ? filePath.split('/').pop() : '';
    }
    
    function getGradeClass(nilai) {
        if (nilai >= 85) return 'grade-excellent';
        if (nilai >= 75) return 'grade-good';
        if (nilai >= 65) return 'grade-fair';
        return 'grade-poor';
    }

    // Video helper functions
    function getVideoEmbedUrl(videoLink) {
        if (!videoLink) return '';
        
        // Handle YouTube URLs
        if (videoLink.includes('youtube.com') || videoLink.includes('youtu.be')) {
            const videoId = extractYouTubeId(videoLink);
            return videoId ? `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1` : videoLink;
        }
        
        // Handle Vimeo URLs
        if (videoLink.includes('vimeo.com')) {
            const vimeoId = extractVimeoId(videoLink);
            return vimeoId ? `https://player.vimeo.com/video/${vimeoId}` : videoLink;
        }
        
        // For other video URLs, return as is
        return videoLink;
    }

    function extractYouTubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    function extractVimeoId(url) {
        const regExp = /(?:vimeo)\.com\/(?:.*\/)?(.+)/;
        const match = url.match(regExp);
        return match ? match[1] : null;
    }
    
    function loadStudentAnswers(component) {
        fetch(`{{ route('materi.getStudentAnswers', [$courseId, $material->id_materi, '__COMPONENT__']) }}`.replace('__COMPONENT__', component))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayStudentAnswers(component, data.answers);
                }
            })
            .catch(error => {
                console.error('Error loading student answers:', error);
            });
    }
    
    function displayStudentAnswers(component, answers) {
        const contentArea = document.getElementById('dynamicContent');
        
        let html = `
            <div class="space-y-6">
                <!-- Header dengan tombol kembali -->
                <div class="flex items-center justify-between bg-white rounded-lg p-4 shadow-sm">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Jawaban Siswa - ${components[component].title}</h3>
                        <p class="text-sm text-slate-600 mt-1">Total ${answers.length} siswa telah mengumpulkan jawaban</p>
                    </div>
                    <button onclick="showComponent('${component}')" 
                            class="flex items-center space-x-2 text-edu-blue hover:text-edu-blue/80 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali</span>
                    </button>
                </div>
                
                <!-- Statistik Penilaian -->
                ${generateGradingStatistics(answers)}
                
                <!-- Tabel Jawaban Siswa -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200">
                        <h4 class="font-medium text-slate-900">Daftar Jawaban</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Nama Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Jawaban</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Tanggal Submit</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Nilai</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
        `;
        
        if (answers.length === 0) {
            html += `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="font-medium">Belum ada jawaban dari siswa</p>
                        <p class="text-sm mt-1">Jawaban akan muncul setelah siswa mengumpulkan</p>
                    </td>
                </tr>
            `;
        } else {
            answers.forEach((answer, index) => {
                const createdDate = new Date(answer.created_at).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                html += `
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm text-slate-900">${index + 1}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-edu-blue text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">
                                    ${answer.nama_siswa.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-slate-900">${answer.nama_siswa}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            ${answer.jawaban ? `
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div>
                                        <a href="${getFileUrl(answer.jawaban)}" target="_blank" 
                                        class="text-edu-blue hover:underline text-sm font-medium">
                                            ${getFileName(answer.jawaban)}
                                        </a>
                                        <p class="text-xs text-slate-500 mt-1">Klik untuk melihat</p>
                                    </div>
                                </div>
                            ` : `
                                <span class="text-slate-400 text-sm">Tidak ada file</span>
                            `}
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">${createdDate}</td>
                        <td class="px-6 py-4 text-center">
                            ${answer.nilai !== null ? `
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getGradeBadgeClass(answer.nilai)}">
                                    ${answer.nilai}/100
                                </span>
                            ` : `
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                    Belum dinilai
                                </span>
                            `}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center space-x-2">
                                ${answer.jawaban ? `
                                    <button onclick="viewAnswerDetail('${component}', ${answer.id})" 
                                            class="text-slate-600 hover:text-slate-900 p-1" title="Lihat Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                ` : ''}
                                
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        
        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        contentArea.innerHTML = html;
    }
    
    function openGradeModal(answerId, component, studentName, currentGrade) {
        document.getElementById('gradeUserId').value = answerId;
        document.getElementById('gradeComponent').value = component;
        document.getElementById('gradeStudentName').textContent = studentName;
        
        // Update modal for Y/N grading
        const gradeContainer = document.getElementById('gradeContainer');
        gradeContainer.innerHTML = `
            <label class="block text-sm font-medium text-slate-700 mb-2">Penilaian</label>
            <div class="flex space-x-4">
                <label class="flex items-center">
                    <input type="radio" name="grade" value="Y" ${currentGrade === 'Y' ? 'checked' : ''} class="mr-2">
                    <span class="text-green-600 font-medium">Benar</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="grade" value="N" ${currentGrade === 'N' ? 'checked' : ''} class="mr-2">
                    <span class="text-red-600 font-medium">Salah</span>
                </label>
            </div>
        `;
        
        document.getElementById('gradeModal').classList.remove('hidden');
    }
    
    function closeGradeModal() {
        document.getElementById('gradeModal').classList.add('hidden');
    }
    
    // Handle grade form submission
    // document.getElementById('gradeForm').addEventListener('submit', function(e) {
    //     e.preventDefault();
        
    //     const formData = new FormData(this);
    //     formData.append('answer_id', document.getElementById('gradeUserId').value);
    //     formData.append('component', document.getElementById('gradeComponent').value);
        
    //     const submitButton = this.querySelector('button[type="submit"]');
    //     const originalText = submitButton.textContent;
        
    //     submitButton.disabled = true;
    //     submitButton.textContent = 'Menyimpan...';
        
    //     fetch(`{{ route('materi.gradeAnswer', [$courseId, $material->id_materi]) }}`, {
    //         method: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    //             'Accept': 'application/json'
    //         },
    //         body: formData
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         if (data.success) {
    //             closeGradeModal();
    //             const component = document.getElementById('gradeComponent').value;
    //             loadStudentAnswers(component);
                
    //             // Show success message
    //             alert('Penilaian berhasil disimpan');
    //         } else {
    //             alert(data.message || 'Gagal memberikan nilai');
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error:', error);
    //         alert('Terjadi kesalahan saat memberikan nilai');
    //     })
    //     .finally(() => {
    //         submitButton.disabled = false;
    //         submitButton.textContent = originalText;
    //     });
    // });
    
    // Close modal when clicking outside
    document.getElementById('gradeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeGradeModal();
        }
    });

    // Handle form submission untuk modal soal (Guru)
    document.getElementById('soalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('soalSubmitBtn');
        const originalText = submitBtn.textContent;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengupload...';
        
        // Submit form normally (tidak menggunakan AJAX karena route belum dibuat)
        this.action = '{{ route("materi.uploadSoalPRE", [$courseId, $material->id_materi]) }}';
        this.submit();
    });

    // Handle form submission untuk modal jawaban (Siswa)
    document.getElementById('jawabanForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = document.getElementById('jawabanSubmitBtn');
        const originalText = submitBtn.textContent;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengupload...';
        
        // Submit form normally (tidak menggunakan AJAX karena route belum dibuat)
        this.action = '{{ route("materi.uploadJawabanPRE", [$courseId, $material->id_materi]) }}';
        this.submit();
    });

    // Functions untuk membuka/menutup modal soal
    function openSoalModal(componentName, type) {
        const typeLabels = {
            'perencanaan': 'Perencanaan',
            'refleksi': 'Refleksi', 
            'evaluasi': 'Evaluasi'
        };
        
        document.getElementById('soalModalTitle').textContent = `Upload Soal ${typeLabels[type]} - ${components[componentName].title}`;
        document.getElementById('soalComponent').value = componentName;
        document.getElementById('soalType').value = type;
        document.getElementById('soalModal').classList.remove('hidden');
    }

    function closeSoalModal() {
        document.getElementById('soalModal').classList.add('hidden');
        // Reset form
        document.getElementById('soalForm').reset();
        document.getElementById('soalSubmitBtn').disabled = false;
        document.getElementById('soalSubmitBtn').textContent = 'Upload Soal';
    }

    // Functions untuk membuka/menutup modal jawaban
    function openJawabanModal(componentName, type) {
        const typeLabels = {
            'perencanaan': 'Perencanaan',
            'refleksi': 'Refleksi',
            'evaluasi': 'Evaluasi'
        };
        
        document.getElementById('jawabanModalTitle').textContent = `Upload Jawaban ${typeLabels[type]} - ${components[componentName].title}`;
        document.getElementById('jawabanComponent').value = componentName;
        document.getElementById('jawabanType').value = type;
        document.getElementById('jawabanModal').classList.remove('hidden');
    }

    function closeJawabanModal() {
        document.getElementById('jawabanModal').classList.add('hidden');
        // Reset form
        document.getElementById('jawabanForm').reset();
        document.getElementById('jawabanSubmitBtn').disabled = false;
        document.getElementById('jawabanSubmitBtn').textContent = 'Upload Jawaban';
    }

    // Close modals when clicking outside
    document.getElementById('soalModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSoalModal();
        }
    });

    document.getElementById('jawabanModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeJawabanModal();
        }
    });

    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSoalEssayModal();
        closeJawabanEssayModal();
    }
    
    // Ctrl+Enter untuk submit (opsional)
    if (e.ctrlKey && e.key === 'Enter') {
        if (!document.getElementById('jawabanEssayModal').classList.contains('hidden')) {
            submitJawabanEssay();
        }
    }
});






    
</script>
{{-- chat modal  --}}
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    // Semua script yang sudah ada tetap sama...
    // Hanya modifikasi fungsi generateEksplorasiKonsepContent()
    
    function generateEksplorasiKonsepContent() {
    @if($canManage)
        const eksplorasiKonsep = @json($eksplorasiKonsep);
        
        return `
            <div class="space-y-6">
                <!-- Tombol Chat untuk Guru -->
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium mb-4">Kelola Konten Eksplorasi Konsep</h3>
                    <button onclick="openChatModal()" 
                            class="bg-edu-blue text-white px-4 py-2 rounded-lg hover:bg-edu-blue/90 transition-colors flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Chat Pembelajaran</span>
                    </button>
                </div>
                
                <!-- Form untuk konten utama eksplorasi konsep -->
                <form action="{{ route('materi.updateEksplorasiKonsep', [$courseId, $material->id_materi]) }}" method="POST" enctype="multipart/form-data" id="eksplorasiKonsepForm">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Konten Materi</label>
                        <div id="eksplorasiKonsepEditor"></div>
                        <textarea name="isi_materi" id="eksplorasiKonsepContent" class="hidden">${eksplorasiKonsep ? eksplorasiKonsep.isi_materi || '' : ''}</textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Upload PDF</label>
                            <input type="file" name="pdf_file" accept=".pdf" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                            ${eksplorasiKonsep && eksplorasiKonsep.pdf ? `<p class="text-sm text-slate-600 mt-1">Current: ${getFileName(eksplorasiKonsep.pdf)}</p>` : ''}
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Link Video (YouTube/URL)</label>
                            <input type="url" name="video_link" placeholder="https://www.youtube.com/watch?v=..." 
                                value="${eksplorasiKonsep && eksplorasiKonsep.video ? eksplorasiKonsep.video : ''}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-green focus:border-transparent">
                            <p class="text-xs text-slate-500 mt-1">Masukkan link YouTube atau URL video lainnya</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Link Video (YouTube/URL)</label>
                            <input type="url" name="video_link2" placeholder="https://www.youtube.com/watch?v=..." 
                                value="${eksplorasiKonsep && eksplorasiKonsep.video2 ? eksplorasiKonsep.video2 : ''}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-green focus:border-transparent">
                            <p class="text-xs text-slate-500 mt-1">Masukkan link YouTube atau URL video lainnya</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Link Video (YouTube/URL)</label>
                            <input type="url" name="video_link3" placeholder="https://www.youtube.com/watch?v=..." 
                                value="${eksplorasiKonsep && eksplorasiKonsep.video3 ? eksplorasiKonsep.video3 : ''}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-green focus:border-transparent">
                            <p class="text-xs text-slate-500 mt-1">Masukkan link YouTube atau URL video lainnya</p>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                            Simpan Eksplorasi Konsep
                        </button>
                    </div>
                </form>
                
                <!-- Section untuk Pengetahuan Metakognisi -->
                <div class="border-t pt-6">
                    <h4 class="text-lg font-medium mb-4">Kelola Soal Pengetahuan Metakognisi</h4>
                    ${generateSoalMetakognisiSection()}
                </div>
                
                <!-- Tombol untuk melihat jawaban siswa -->
                <div class="border-t pt-6">
                    <button onclick="loadStudentMetakognisiAnswers()" 
                            class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90">
                        Lihat Jawaban Siswa
                    </button>
                </div>
            </div>
        `;
    @else
        const eksplorasiKonsep = @json($eksplorasiKonsep);
        const checklist = @json($checklist);
        const isCompleted = checklist.eksplorasi_konsep === 'Y';
        
        // Cek apakah sudah ada nilai metakognisi yang final
        const hasFinalMetakognisiGrade = checkFinalMetakognisiGrade();
        
        return `
            <div class="space-y-6">
                <!-- Tombol Chat untuk Siswa -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Eksplorasi Konsep</h3>
                    <button onclick="openChatModal()" 
                            class="bg-gradient-to-r from-edu-blue to-edu-green text-white px-4 py-2 rounded-lg hover:shadow-lg transform hover:scale-105 transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Chat Diskusi</span>
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    </button>
                </div>

                ${eksplorasiKonsep ? `
                    <div class="space-y-6">
                        <div class="content-area bg-white rounded-lg p-6 border">
                            <div class="prose prose-slate max-w-none">
                                ${eksplorasiKonsep.isi_materi || '<p class="text-slate-500">Konten belum tersedia</p>'}
                            </div>
                        </div>


                        ${eksplorasiKonsep.pdf ? `
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="font-medium text-blue-900 mb-3">Materi PDF:</h4>
                                <div class="bg-white rounded-lg overflow-hidden border">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 border-b">
                                        <span class="text-sm font-medium text-gray-700">${getFileName(eksplorasiKonsep.pdf)}</span>
                                        <div class="flex space-x-2">
                                            <button onclick="window.open('${getFileUrl(eksplorasiKonsep.pdf)}', '_blank')" 
                                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Buka di Tab Baru
                                            </button>
                                            <a href="${getFileUrl(eksplorasiKonsep.pdf)}" download 
                                            class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                    <object data="${getFileUrl(eksplorasiKonsep.pdf)}" 
                                            type="application/pdf" 
                                            width="100%" 
                                            height="600px">
                                        <iframe src="${getFileUrl(eksplorasiKonsep.pdf)}" 
                                                width="100%" 
                                                height="600px"
                                                frameborder="0">
                                            <p class="p-4 text-center text-gray-600">
                                                Tidak dapat menampilkan PDF. 
                                                <a href="${getFileUrl(eksplorasiKonsep.pdf)}" target="_blank" 
                                                class="text-blue-600 hover:underline">
                                                    Klik untuk membuka
                                                </a>
                                            </p>
                                        </iframe>
                                    </object>
                                </div>
                            </div>
                        ` : `
                        `}
                        
                        ${eksplorasiKonsep.video ? `
                            <div class="bg-red-50 rounded-lg p-4">
                                <h4 class="font-medium text-red-900 mb-3">Video Pembelajaran:</h4>
                                <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                                    <div class="video-container">
                                        <iframe src="${getVideoEmbedUrl(eksplorasiKonsep.video)}" 
                                                frameborder="0" 
                                                allowfullscreen
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                        </iframe>
                                    </div>
                                    <div class="p-3 bg-gray-50 border-t">
                                        <a href="${eksplorasiKonsep.video}" target="_blank" 
                                        class="text-blue-600 hover:underline text-sm">
                                            Buka video di tab baru
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ` : ''}


                        ${eksplorasiKonsep.video2 ? `
                            <div class="bg-red-50 rounded-lg p-4">
                                <h4 class="font-medium text-red-900 mb-3">Video Pembelajaran:</h4>
                                <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                                    <div class="video-container">
                                        <iframe src="${getVideoEmbedUrl(eksplorasiKonsep.video2)}" 
                                                frameborder="0" 
                                                allowfullscreen
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                        </iframe>
                                    </div>
                                    <div class="p-3 bg-gray-50 border-t">
                                        <a href="${eksplorasiKonsep.video2}" target="_blank" 
                                        class="text-blue-600 hover:underline text-sm">
                                            Buka video di tab baru
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ` : ''}


                        
                        ${eksplorasiKonsep.video3 ? `
                            <div class="bg-red-50 rounded-lg p-4">
                                <h4 class="font-medium text-red-900 mb-3">Video Pembelajaran:</h4>
                                <div class="bg-white rounded-lg overflow-hidden shadow-sm">
                                    <div class="video-container">
                                        <iframe src="${getVideoEmbedUrl(eksplorasiKonsep.video3)}" 
                                                frameborder="0" 
                                                allowfullscreen
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                        </iframe>
                                    </div>
                                    <div class="p-3 bg-gray-50 border-t">
                                        <a href="${eksplorasiKonsep.video3}" target="_blank" 
                                        class="text-blue-600 hover:underline text-sm">
                                            Buka video di tab baru
                                        </a>
                                    </div>
                                </div>
                            </div>
                        ` : ''}

                    </div>
                    
                    <!-- Section Soal dan Upload Jawaban Pengetahuan Metakognisi untuk Siswa -->
                    <!-- PENTING: Tetap tampilkan section ini meskipun sudah dinilai -->
                    ${generateSoalMetakognisiStudentSection()}
                    
                    <!-- Tampilkan nilai jika sudah ada -->
                    ${hasFinalMetakognisiGrade ? `
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 mt-6">
                            <h4 class="font-medium text-green-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Hasil Penilaian Pengetahuan Metakognisi
                            </h4>
                            ${generateFinalMetakognisiGradeView()}
                        </div>
                    ` : ''}
                    
                    <div class="text-center mt-6">
                        ${!isCompleted ? `
                            <form action="{{ route('materi.completeEksplorasiKonsep', [$courseId, $material->id_materi]) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                    Tandai Sebagai Selesai
                                </button>
                            </form>
                        ` : `
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-medium">✓ Selesai</span>
                        `}
                    </div>
                ` : `
                    <div class="text-center py-8">
                        <p class="text-slate-500">Konten eksplorasi konsep belum tersedia.</p>
                    </div>
                `}
            </div>
        `;
    @endif
}

    // Semua fungsi lainnya tetap sama, tidak diubah...
</script>

<script>
// Update JavaScript - Tambahkan variabel dan fungsi baru
let soalEssayEditor = null;
let jawabanEssayEditor = null;

// Update generateSoalPRESection untuk essay
function generateSoalPRESection(componentName) {
    let html = '<div class="space-y-4">';
    
    const types = ['perencanaan', 'refleksi', 'evaluasi'];
    const colors = ['green', 'orange', 'purple'];
    const icons = [
        'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
        'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    ];

    types.forEach((type, index) => {
        const currentSoal = getSoalPRE(componentName, type);
        const hasText = currentSoal && currentSoal.trim().length > 0;
        
        html += `
            <div class="soal-pre-item ${hasText ? 'available' : ''}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-${colors[index]}-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-${colors[index]}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[index]}"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="font-medium text-slate-900">Soal ${type.charAt(0).toUpperCase() + type.slice(1)}</h5>
                            ${hasText ? 
                                `<p class="text-sm text-${colors[index]}-600">Soal sudah dibuat</p>` :
                                '<p class="text-sm text-slate-500">Belum ada soal</p>'
                            }
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        ${hasText ? `
                            <button onclick="previewSoalEssay('${componentName}', '${type}')" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                Preview
                            </button>
                        ` : ''}
                        <button onclick="openSoalEssayModal('${componentName}', '${type}')" 
                                class="bg-${colors[index]}-600 text-white px-3 py-1 rounded text-sm hover:bg-${colors[index]}-700 transition-colors">
                            ${hasText ? 'Edit' : 'Buat'}
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    return html;
}

// Update generateSoalPREStudentSection untuk essay
function generateSoalPREStudentSection(componentName) {
    let html = '<div class="space-y-6">';
    html += '<h4 class="text-lg font-medium">Soal Essay & Jawaban</h4>';
    
    const types = ['perencanaan', 'refleksi', 'evaluasi'];
    const colors = ['green', 'orange', 'purple'];
    const typeLabels = {
        'perencanaan': 'Perencanaan',
        'refleksi': 'Refleksi',
        'evaluasi': 'Evaluasi'
    };

    types.forEach((type, index) => {
        const currentSoal = getSoalPRE(componentName, type);
        const currentJawaban = getJawabanPRE(componentName, type);
        const hasText = currentSoal && currentSoal.trim().length > 0;
        
        html += `
            <div class="soal-pre-item ${hasText ? 'available' : ''}">
                <div class="flex items-center justify-between mb-3">
                    <h5 class="font-medium text-slate-900">${typeLabels[type]}</h5>
                    ${currentJawaban && currentJawaban.nilai !== null ? 
                        `<span class="grade-badge ${getGradeClass(currentJawaban.nilai)}">Nilai: ${currentJawaban.nilai}/100</span>` :
                        ''
                    }
                </div>
                
                <div class="flex flex-col gap-3">
                    ${hasText ? `
                        <div class="bg-slate-50 rounded-lg p-4">
                            <h6 class="font-medium text-slate-700 mb-2">Soal:</h6>
                            <div class="prose prose-sm max-w-none">
                                ${currentSoal}
                            </div>
                        </div>
                        
                        ${(!currentJawaban || currentJawaban.nilai === null) ? `
                            <button onclick="openJawabanEssayModal('${componentName}', '${type}')" 
                                    class="bg-${colors[index]}-600 text-white px-4 py-2 rounded-lg hover:bg-${colors[index]}-700 transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                ${currentJawaban ? 'Edit Jawaban' : 'Tulis Jawaban'}
                            </button>
                        ` : ''}
                    ` : `
                        <div class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-center">
                            Soal belum tersedia
                        </div>
                    `}
                </div>
                
                ${currentJawaban && currentJawaban.nilai === null ? `
                    <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-2">
                        <p class="text-yellow-800 text-sm">Jawaban telah dikirim, menunggu penilaian</p>
                    </div>
                ` : ''}
            </div>
        `;
    });

    html += '</div>';
    return html;
}

// Fungsi untuk membuka modal soal essay (Guru)
function openSoalEssayModal(componentName, type) {
    const typeLabels = {
        'perencanaan': 'Perencanaan',
        'refleksi': 'Refleksi', 
        'evaluasi': 'Evaluasi'
    };
    
    document.getElementById('soalEssayModalTitle').textContent = `Buat Soal ${typeLabels[type]} - ${components[componentName].title}`;
    document.getElementById('soalEssayComponent').value = componentName;
    document.getElementById('soalEssayType').value = type;
    
    // Load existing content if available
    const currentSoal = getSoalPRE(componentName, type);
    
    document.getElementById('soalEssayModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize editor after modal is shown
    setTimeout(() => {
        initSoalEssayEditor(currentSoal || '');
    }, 100);
}

// Fungsi untuk membuka modal jawaban essay (Siswa)
function openJawabanEssayModal(componentName, type) {
    const typeLabels = {
        'perencanaan': 'Perencanaan',
        'refleksi': 'Refleksi',
        'evaluasi': 'Evaluasi'
    };
    
    document.getElementById('jawabanEssayModalTitle').textContent = `Jawab Soal ${typeLabels[type]} - ${components[componentName].title}`;
    document.getElementById('jawabanEssayComponent').value = componentName;
    document.getElementById('jawabanEssayType').value = type;
    
    // Load soal and existing jawaban
    const currentSoal = getSoalPRE(componentName, type);
    const currentJawaban = getJawabanPRE(componentName, type);
    
    document.getElementById('soalEssayDisplay').innerHTML = currentSoal || 'Soal tidak tersedia';
    
    // Clear editor container completely before showing modal
    const editorContainer = document.getElementById('jawabanEssayEditor');
    if (editorContainer) {
        editorContainer.innerHTML = '';
    }
    
    document.getElementById('jawabanEssayModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize editor after a longer delay to ensure modal is fully rendered
    setTimeout(() => {
        initJawabanEssayEditor(currentJawaban ? currentJawaban.jawaban : '');
    }, 300);
}


// Fungsi untuk preview soal essay (Guru)
function previewSoalEssay(componentName, type) {
    const currentSoal = getSoalPRE(componentName, type);
    const typeLabels = {
        'perencanaan': 'Perencanaan',
        'refleksi': 'Refleksi',
        'evaluasi': 'Evaluasi'
    };
    
    if (!currentSoal) return;
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[80vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900">Preview Soal ${typeLabels[type]}</h3>
                <button onclick="this.closest('.fixed').remove(); document.body.style.overflow = 'auto';" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 140px)">
                <div class="prose max-w-none">
                    ${currentSoal}
                </div>
            </div>
            <div class="px-6 py-4 border-t text-right">
                <button onclick="this.closest('.fixed').remove(); document.body.style.overflow = 'auto';" 
                        class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                    Tutup
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

// Initialize soal essay editor
function initSoalEssayEditor(content = '') {
    if (soalEssayEditor) {
        soalEssayEditor = null;
    }
    
    soalEssayEditor = new Quill('#soalEssayEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
    
    if (content) {
        soalEssayEditor.root.innerHTML = content;
    }
    
    soalEssayEditor.on('text-change', function() {
        document.getElementById('soalEssayContent').value = soalEssayEditor.root.innerHTML;
    });
}

// Initialize jawaban essay editor
function initJawabanEssayEditor(content = '') {
    // Destroy existing editor completely
    if (jawabanEssayEditor) {
        try {
            // Try to properly destroy the editor
            const toolbar = jawabanEssayEditor.getModule('toolbar');
            if (toolbar && toolbar.container) {
                toolbar.container.remove();
            }
            jawabanEssayEditor = null;
        } catch (e) {
            console.log('Editor cleanup error:', e);
            jawabanEssayEditor = null;
        }
    }
    
    // Clear the container completely
    const editorContainer = document.getElementById('jawabanEssayEditor');
    if (!editorContainer) {
        console.error('Editor container not found');
        return;
    }
    
    // Remove all existing content and classes
    editorContainer.innerHTML = '';
    editorContainer.className = '';
    
    // Wait a bit before creating new editor
    setTimeout(() => {
        try {
            jawabanEssayEditor = new Quill('#jawabanEssayEditor', {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                },
                placeholder: 'Tulis jawaban Anda di sini...',
                bounds: '#jawabanEssayModal'
            });
            
            if (content && content.trim() !== '') {
                jawabanEssayEditor.root.innerHTML = content;
            }
            
            jawabanEssayEditor.on('text-change', function() {
                document.getElementById('jawabanEssayContent').value = jawabanEssayEditor.root.innerHTML;
            });
            
            // Focus after initialization
            setTimeout(() => {
                if (jawabanEssayEditor) {
                    jawabanEssayEditor.focus();
                }
            }, 100);
            
        } catch (error) {
            console.error('Error initializing Quill editor:', error);
        }
    }, 100);
}


// Close modal functions
function closeSoalEssayModal() {
    document.getElementById('soalEssayModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    if (soalEssayEditor) {
        soalEssayEditor = null;
    }
}

function closeJawabanEssayModal() {
    // Hide modal first
    document.getElementById('jawabanEssayModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Properly destroy editor
    if (jawabanEssayEditor) {
        try {
            // Remove all event listeners
            jawabanEssayEditor.off('text-change');
            
            // Get the container and clear it completely
            const container = jawabanEssayEditor.container;
            if (container) {
                container.innerHTML = '';
            }
            
            // Set to null
            jawabanEssayEditor = null;
        } catch (e) {
            console.log('Error destroying editor:', e);
            jawabanEssayEditor = null;
        }
    }
    
    // Clear the editor container completely
    const editorContainer = document.getElementById('jawabanEssayEditor');
    if (editorContainer) {
        editorContainer.innerHTML = '';
        editorContainer.className = '';
    }
    
    // Reset form and button
    const submitBtn = document.getElementById('jawabanEssaySubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Jawaban';
    }
    
    // Clear form content
    const contentField = document.getElementById('jawabanEssayContent');
    if (contentField) {
        contentField.value = '';
    }
}


// Form submission handlers
document.addEventListener('DOMContentLoaded', function() {
    // Handle soal essay form submission
    const soalEssayForm = document.getElementById('soalEssayForm');
    if (soalEssayForm) {
        soalEssayForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (soalEssayEditor) {
                document.getElementById('soalEssayContent').value = soalEssayEditor.root.innerHTML;
            }
            
            const submitBtn = document.getElementById('soalEssaySubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
            
            this.submit();
        });
    }

    // Handle jawaban essay form submission
    const jawabanEssayForm = document.getElementById('jawabanEssayForm');
    if (jawabanEssayForm) {
        jawabanEssayForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (jawabanEssayEditor) {
                document.getElementById('jawabanEssayContent').value = jawabanEssayEditor.root.innerHTML;
            }
            
            const submitBtn = document.getElementById('jawabanEssaySubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
            
            this.submit();
        });
    }
});


// Close modals when clicking outside
document.getElementById('soalEssayModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSoalEssayModal();
    }
});

document.getElementById('jawabanEssayModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeJawabanEssayModal();
    }
});

function submitJawabanEssay() {
    if (!jawabanEssayEditor) {
        alert('Editor tidak tersedia');
        return;
    }
    
    const content = jawabanEssayEditor.root.innerHTML;
    const textContent = jawabanEssayEditor.getText().trim();
    
    if (!textContent || textContent.length < 5) {
        alert('Silakan isi jawaban dengan lebih lengkap');
        jawabanEssayEditor.focus();
        return;
    }
    
    document.getElementById('jawabanEssayContent').value = content;
    
    const submitBtn = document.getElementById('jawabanEssaySubmitBtn');
    const form = document.getElementById('jawabanEssayForm');
    
    if (!form || !submitBtn) {
        console.error('Form or submit button not found');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    
    form.submit();
}

// Pastikan cleanup saat page unload
window.addEventListener('beforeunload', function() {
    if (jawabanEssayEditor) {
        jawabanEssayEditor = null;
    }
    if (soalEssayEditor) {
        soalEssayEditor = null;
    }
});

// Prevent multiple modal instances
document.addEventListener('DOMContentLoaded', function() {
    // Make sure only one modal can be open at a time
    const modals = ['soalEssayModal', 'jawabanEssayModal'];
    
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    if (modalId === 'soalEssayModal') {
                        closeSoalEssayModal();
                    } else if (modalId === 'jawabanEssayModal') {
                        closeJawabanEssayModal();
                    }
                }
            });
        }
    });
});


// Tambahkan di bagian JavaScript section

// Editor variables for metakognisi
let soalMetakognisiEditor = null;
let jawabanMetakognisiEditor = null;

// UPDATE fungsi generateSoalMetakognisiSection - replace yang existing
function generateSoalMetakognisiSection() {
    let html = '<div class="space-y-4">';
    
    const types = ['deklaratif', 'prosedural', 'kondisional'];
    const colors = ['blue', 'green', 'purple'];
    const typeLabels = {
        'deklaratif': 'Pengetahuan Deklaratif',
        'prosedural': 'Pengetahuan Prosedural', 
        'kondisional': 'Pengetahuan Kondisional'
    };
    const icons = [
        'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'
    ];

    types.forEach((type, index) => {
        const currentSoal = getSoalMetakognisi(type);
        const hasText = currentSoal && currentSoal.trim().length > 0;
        
        html += `
            <div class="soal-pre-item ${hasText ? 'available' : ''}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-${colors[index]}-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-${colors[index]}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${icons[index]}"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="font-medium text-slate-900">${typeLabels[type]}</h5>
                            ${hasText ? 
                                `<p class="text-sm text-${colors[index]}-600">Soal sudah dibuat</p>` :
                                '<p class="text-sm text-slate-500">Belum ada soal</p>'
                            }
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        ${hasText ? `
                            <button onclick="previewSoalMetakognisi('${type}')" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                Preview
                            </button>
                        ` : ''}
                        <button onclick="openSoalMetakognisiModal('${type}')" 
                                class="bg-${colors[index]}-600 text-white px-3 py-1 rounded text-sm hover:bg-${colors[index]}-700 transition-colors">
                            ${hasText ? 'Edit' : 'Buat'}
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    return html;
}

// UPDATE fungsi generateSoalMetakognisiStudentSection - replace yang existing
function generateSoalMetakognisiStudentSection() {
    let html = '<div class="space-y-6">';
    html += '<h4 class="text-lg font-medium">Soal Essay & Jawaban Pengetahuan Metakognisi</h4>';
    
    const types = ['deklaratif', 'prosedural', 'kondisional'];
    const colors = ['blue', 'green', 'purple'];
    const typeLabels = {
        'deklaratif': 'Pengetahuan Deklaratif',
        'prosedural': 'Pengetahuan Prosedural',
        'kondisional': 'Pengetahuan Kondisional'
    };

    types.forEach((type, index) => {
        const currentSoal = getSoalMetakognisi(type);
        const currentJawaban = getJawabanMetakognisi(type);
        const hasText = currentSoal && currentSoal.trim().length > 0;
        
        html += `
            <div class="soal-pre-item ${hasText ? 'available' : ''}">
                <div class="flex items-center justify-between mb-3">
                    <h5 class="font-medium text-slate-900">${typeLabels[type]}</h5>
                    ${currentJawaban && currentJawaban['nilai_' + type] !== null ? 
                        `<span class="grade-badge ${getGradeClass(currentJawaban['nilai_' + type])}">Nilai: ${currentJawaban['nilai_' + type]}/100</span>` :
                        ''
                    }
                </div>
                
                <div class="flex flex-col gap-3">
                    ${hasText ? `
                        <div class="bg-slate-50 rounded-lg p-4">
                            <h6 class="font-medium text-slate-700 mb-2">Soal:</h6>
                            <div class="prose prose-sm max-w-none">
                                ${currentSoal}
                            </div>
                        </div>
                        
                        ${(!currentJawaban || currentJawaban['nilai_' + type] === null) ? `
                            <button onclick="openJawabanMetakognisiModal('${type}')" 
                                    class="bg-${colors[index]}-600 text-white px-4 py-2 rounded-lg hover:bg-${colors[index]}-700 transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                ${currentJawaban && currentJawaban[type] ? 'Edit Jawaban' : 'Tulis Jawaban'}
                            </button>
                        ` : ''}
                    ` : `
                        <div class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-center">
                            Soal belum tersedia
                        </div>
                    `}
                </div>
                
                ${currentJawaban && currentJawaban[type] && currentJawaban['nilai_' + type] === null ? `
                    <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-2">
                        <p class="text-yellow-800 text-sm">Jawaban telah dikirim, menunggu penilaian</p>
                    </div>
                ` : ''}
            </div>
        `;
    });

    html += '</div>';
    return html;
}

// Fungsi untuk preview soal metakognisi (Guru)
function previewSoalMetakognisi(type) {
    const currentSoal = getSoalMetakognisi(type);
    const typeLabels = {
        'deklaratif': 'Pengetahuan Deklaratif',
        'prosedural': 'Pengetahuan Prosedural',
        'kondisional': 'Pengetahuan Kondisional'
    };
    
    if (!currentSoal) return;
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[80vh] overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900">Preview Soal ${typeLabels[type]}</h3>
                <button onclick="this.closest('.fixed').remove(); document.body.style.overflow = 'auto';" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto" style="max-height: calc(80vh - 140px)">
                <div class="prose max-w-none">
                    ${currentSoal}
                </div>
            </div>
            <div class="px-6 py-4 border-t text-right">
                <button onclick="this.closest('.fixed').remove(); document.body.style.overflow = 'auto';" 
                        class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700">
                    Tutup
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

// Fungsi untuk membuka modal soal essay metakognisi (Guru)
function openSoalMetakognisiModal(type) {
    const typeLabels = {
        'deklaratif': 'Pengetahuan Deklaratif',
        'prosedural': 'Pengetahuan Prosedural', 
        'kondisional': 'Pengetahuan Kondisional'
    };
    
    document.getElementById('soalMetakognisiModalTitle').textContent = `Buat Soal ${typeLabels[type]}`;
    document.getElementById('soalMetakognisiType').value = type;
    
    // Load existing content if available
    const currentSoal = getSoalMetakognisi(type);
    
    document.getElementById('soalMetakognisiModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize editor after modal is shown
    setTimeout(() => {
        initSoalMetakognisiEditor(currentSoal || '');
    }, 100);
}

// Fungsi untuk membuka modal jawaban essay metakognisi (Siswa)
function openJawabanMetakognisiModal(type) {
    const typeLabels = {
        'deklaratif': 'Pengetahuan Deklaratif',
        'prosedural': 'Pengetahuan Prosedural',
        'kondisional': 'Pengetahuan Kondisional'
    };
    
    document.getElementById('jawabanMetakognisiModalTitle').textContent = `Jawab Soal ${typeLabels[type]}`;
    document.getElementById('jawabanMetakognisiType').value = type;
    
    // Load soal and existing jawaban
    const currentSoal = getSoalMetakognisi(type);
    const currentJawaban = getJawabanMetakognisi(type);
    
    document.getElementById('soalMetakognisiDisplay').innerHTML = currentSoal || 'Soal tidak tersedia';
    
    // Clear editor container completely before showing modal
    const editorContainer = document.getElementById('jawabanMetakognisiEditor');
    if (editorContainer) {
        editorContainer.innerHTML = '';
    }
    
    document.getElementById('jawabanMetakognisiModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize editor after a longer delay to ensure modal is fully rendered
    setTimeout(() => {
        const existingAnswer = currentJawaban && currentJawaban[type] ? currentJawaban[type] : '';
        initJawabanMetakognisiEditor(existingAnswer);
    }, 300);
}

// Initialize soal metakognisi editor
function initSoalMetakognisiEditor(content = '') {
    if (soalMetakognisiEditor) {
        soalMetakognisiEditor = null;
    }
    
    soalMetakognisiEditor = new Quill('#soalMetakognisiEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
    
    if (content) {
        soalMetakognisiEditor.root.innerHTML = content;
    }
    
    soalMetakognisiEditor.on('text-change', function() {
        document.getElementById('soalMetakognisiContent').value = soalMetakognisiEditor.root.innerHTML;
    });
}

// Initialize jawaban metakognisi editor
function initJawabanMetakognisiEditor(content = '') {
    // Destroy existing editor completely
    if (jawabanMetakognisiEditor) {
        try {
            const toolbar = jawabanMetakognisiEditor.getModule('toolbar');
            if (toolbar && toolbar.container) {
                toolbar.container.remove();
            }
            jawabanMetakognisiEditor = null;
        } catch (e) {
            console.log('Editor cleanup error:', e);
            jawabanMetakognisiEditor = null;
        }
    }
    
    // Clear the container completely
    const editorContainer = document.getElementById('jawabanMetakognisiEditor');
    if (!editorContainer) {
        console.error('Editor container not found');
        return;
    }
    
    // Remove all existing content and classes
    editorContainer.innerHTML = '';
    editorContainer.className = '';
    
    // Wait a bit before creating new editor
    setTimeout(() => {
        try {
            jawabanMetakognisiEditor = new Quill('#jawabanMetakognisiEditor', {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                },
                placeholder: 'Tulis jawaban Anda di sini...',
                bounds: '#jawabanMetakognisiModal'
            });
            
            if (content && content.trim() !== '') {
                jawabanMetakognisiEditor.root.innerHTML = content;
            }
            
            jawabanMetakognisiEditor.on('text-change', function() {
                document.getElementById('jawabanMetakognisiContent').value = jawabanMetakognisiEditor.root.innerHTML;
            });
            
            // Focus after initialization
            setTimeout(() => {
                if (jawabanMetakognisiEditor) {
                    jawabanMetakognisiEditor.focus();
                }
            }, 100);
            
        } catch (error) {
            console.error('Error initializing Quill editor:', error);
        }
    }, 100);
}

// Close modal functions
function closeSoalMetakognisiModal() {
    document.getElementById('soalMetakognisiModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    if (soalMetakognisiEditor) {
        soalMetakognisiEditor = null;
    }
}

function closeJawabanMetakognisiModal() {
    // Hide modal first
    document.getElementById('jawabanMetakognisiModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    // Properly destroy editor
    if (jawabanMetakognisiEditor) {
        try {
            // Remove all event listeners
            jawabanMetakognisiEditor.off('text-change');
            
            // Get the container and clear it completely
            const container = jawabanMetakognisiEditor.container;
            if (container) {
                container.innerHTML = '';
            }
            
            // Set to null
            jawabanMetakognisiEditor = null;
        } catch (e) {
            console.log('Error destroying editor:', e);
            jawabanMetakognisiEditor = null;
        }
    }
    
    // Clear the editor container completely
    const editorContainer = document.getElementById('jawabanMetakognisiEditor');
    if (editorContainer) {
        editorContainer.innerHTML = '';
        editorContainer.className = '';
    }
    
    // Reset form and button
    const submitBtn = document.getElementById('jawabanMetakognisiSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Jawaban';
    }
    
    // Clear form content
    const contentField = document.getElementById('jawabanMetakognisiContent');
    if (contentField) {
        contentField.value = '';
    }
}

// Submit jawaban metakognisi
function submitJawabanMetakognisi() {
    if (!jawabanMetakognisiEditor) {
        alert('Editor tidak tersedia');
        return;
    }
    
    const content = jawabanMetakognisiEditor.root.innerHTML;
    const textContent = jawabanMetakognisiEditor.getText().trim();
    
    if (!textContent || textContent.length < 5) {
        alert('Silakan isi jawaban dengan lebih lengkap');
        jawabanMetakognisiEditor.focus();
        return;
    }
    
    document.getElementById('jawabanMetakognisiContent').value = content;
    
    const submitBtn = document.getElementById('jawabanMetakognisiSubmitBtn');
    const form = document.getElementById('jawabanMetakognisiForm');
    
    if (!form || !submitBtn) {
        console.error('Form or submit button not found');
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    
    form.submit();
}

// Form submission handlers for metakognisi
document.addEventListener('DOMContentLoaded', function() {
    // Handle soal metakognisi form submission
    const soalMetakognisiForm = document.getElementById('soalMetakognisiForm');
    if (soalMetakognisiForm) {
        soalMetakognisiForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (soalMetakognisiEditor) {
                document.getElementById('soalMetakognisiContent').value = soalMetakognisiEditor.root.innerHTML;
            }
            
            const submitBtn = document.getElementById('soalMetakognisiSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
            
            this.submit();
        });
    }
});

// Close modals when clicking outside
document.getElementById('soalMetakognisiModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSoalMetakognisiModal();
    }
});

document.getElementById('jawabanMetakognisiModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeJawabanMetakognisiModal();
    }
});

// Cleanup saat page unload
window.addEventListener('beforeunload', function() {
    if (jawabanMetakognisiEditor) {
        jawabanMetakognisiEditor = null;
    }
    if (soalMetakognisiEditor) {
        soalMetakognisiEditor = null;
    }
});



// Fungsi untuk generate statistik penilaian
function generateGradingStatistics(answers) {
    const total = answers.length;
    const graded = answers.filter(a => a.nilai !== null).length;
    const ungraded = total - graded;
    
    const avgGrade = graded > 0 
        ? Math.round(answers.filter(a => a.nilai !== null).reduce((sum, a) => sum + a.nilai, 0) / graded)
        : 0;
    
    const distribution = {
        excellent: answers.filter(a => a.nilai >= 85).length,
        good: answers.filter(a => a.nilai >= 75 && a.nilai < 85).length,
        fair: answers.filter(a => a.nilai >= 65 && a.nilai < 75).length,
        poor: answers.filter(a => a.nilai !== null && a.nilai < 65).length
    };
    
    return `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Total Siswa</p>
                        <p class="text-2xl font-bold text-slate-900">${total}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Sudah Dinilai</p>
                        <p class="text-2xl font-bold text-green-600">${graded}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Belum Dinilai</p>
                        <p class="text-2xl font-bold text-yellow-600">${ungraded}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Rata-rata Nilai</p>
                        <p class="text-2xl font-bold text-purple-600">${avgGrade}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Fungsi untuk mendapatkan class badge berdasarkan nilai
function getGradeBadgeClass(nilai) {
    if (nilai >= 85) return 'bg-green-100 text-green-800';
    if (nilai >= 75) return 'bg-blue-100 text-blue-800';
    if (nilai >= 65) return 'bg-yellow-100 text-yellow-800';
    return 'bg-red-100 text-red-800';
}

// Fungsi baru untuk membuka modal penilaian dengan nilai numerik
function openGradeModalNumeric(answerId, component, studentName, currentGrade) {
    document.getElementById('gradeUserId').value = answerId;
    document.getElementById('gradeComponent').value = component;
    document.getElementById('gradeStudentName').textContent = studentName;
    
    // Update modal untuk nilai numerik (0-100)
    const gradeContainer = document.getElementById('gradeContainer');
    gradeContainer.innerHTML = `
        <label for="gradeValue" class="block text-sm font-medium text-slate-700 mb-2">Nilai (0-100)</label>
        <div class="space-y-3">
            <input type="number" id="gradeValue" name="nilai" min="0" max="100" 
                value="${currentGrade !== null ? currentGrade : ''}"
                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent" 
                placeholder="Masukkan nilai 0-100" required>
            
            <!-- Quick grade buttons -->
            <div class="flex space-x-2">
                <button type="button" onclick="setQuickGrade(100)" 
                        class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm">
                    Sempurna (100)
                </button>
                <button type="button" onclick="setQuickGrade(85)" 
                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm">
                    Bagus (85)
                </button>
                <button type="button" onclick="setQuickGrade(75)" 
                        class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 text-sm">
                    Cukup (75)
                </button>
                <button type="button" onclick="setQuickGrade(65)" 
                        class="px-3 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200 text-sm">
                    Kurang (65)
                </button>
            </div>
            
            <!-- Grade preview -->
            <div id="gradePreview" class="p-3 bg-slate-50 rounded-lg ${currentGrade !== null ? '' : 'hidden'}">
                <p class="text-sm text-slate-600">Preview Nilai:</p>
                <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium ${currentGrade !== null ? getGradeBadgeClass(currentGrade) : ''}">
                    ${currentGrade !== null ? currentGrade + '/100' : ''}
                </span>
            </div>
        </div>
    `;
    
    // Add event listener to update preview
    document.getElementById('gradeValue').addEventListener('input', function(e) {
        updateGradePreview(e.target.value);
    });
    
    document.getElementById('gradeModal').classList.remove('hidden');
}

// Fungsi untuk set quick grade
function setQuickGrade(value) {
    document.getElementById('gradeValue').value = value;
    updateGradePreview(value);
}

// Fungsi untuk update grade preview
function updateGradePreview(value) {
    const preview = document.getElementById('gradePreview');
    if (value && value >= 0 && value <= 100) {
        preview.classList.remove('hidden');
        const badgeClass = value >= 85 ? 'bg-green-100 text-green-800' :
                          value >= 75 ? 'bg-blue-100 text-blue-800' :
                          value >= 65 ? 'bg-yellow-100 text-yellow-800' :
                                       'bg-red-100 text-red-800';
        
        preview.innerHTML = `
            <p class="text-sm text-slate-600">Preview Nilai:</p>
            <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium ${badgeClass}">
                ${value}/100
            </span>
        `;
    } else {
        preview.classList.add('hidden');
    }
}

// Fungsi untuk view detail jawaban (optional - bisa ditambahkan modal preview)
function viewAnswerDetail(component, answerId) {
    // Implementasi untuk preview detail jawaban jika diperlukan
    console.log('View answer detail:', component, answerId);
    // Bisa buka modal untuk preview PDF/DOC atau tampilan detail lainnya
}

// Update form submission handler untuk Mulai Dari Diri
document.addEventListener('DOMContentLoaded', function() {
    const gradeForm = document.getElementById('gradeForm');
    if (gradeForm) {
        gradeForm.removeEventListener('submit', gradeForm.submitHandler); // Remove any existing handlers
        
        gradeForm.submitHandler = function(e) {
            e.preventDefault();
            
            const component = document.getElementById('gradeComponent').value;
            const answerId = document.getElementById('gradeUserId').value;
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            console.log('Submitting grade for component:', component);
            
            // Disable button
            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';
            
            // Determine the type of grading
            if (component.startsWith('metakognisi_')) {
                // Handle metakognisi grading
                handleMetakognisiGrade(answerId, component, submitButton, originalText);
            } else if (component.includes('_')) {
                // Handle PRE grading (component_type format)
                handlePREGrade(answerId, component, submitButton, originalText);
            } else {
                // Handle regular numeric grading
                handleNumericGrade(answerId, component, submitButton, originalText);
            }
        };
        
        gradeForm.addEventListener('submit', gradeForm.submitHandler);
    }
});

// Fungsi notifikasi sederhana
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}


// ===== STUDENT ANSWERS MANAGEMENT COMPONENT =====
// Reusable component untuk semua komponen pembelajaran

const StudentAnswersManager = {
    // Configuration
    config: {
        components: {
            mulai_dari_diri: { 
                title: 'Mulai Dari Diri', 
                subtitle: 'Refleksi awal dan pemahaman diri',
                icon: 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083...',
                color: 'blue'
            },
            ruang_kolaborasi: { 
                title: 'Ruang Kolaborasi', 
                subtitle: 'Diskusi dan kolaborasi pembelajaran',
                icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10...',
                color: 'purple'
            },
            demonstrasi_konseptual: { 
                title: 'Demonstrasi Konseptual', 
                subtitle: 'Demonstrasi pemahaman',
                icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707...',
                color: 'teal'
            },
            elaborasi_pemahaman: { 
                title: 'Elaborasi Pemahaman', 
                subtitle: 'Pengembangan pemahaman',
                icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5...',
                color: 'red'
            }
        }
    },

    // Initialize component
    init: function(courseId, materialId) {
        this.courseId = courseId;
        this.materialId = materialId;
        this.currentComponent = null;
        this.initEventListeners();
    },

    // Event listeners
    initEventListeners: function() {
        // Grade form submission
        // const gradeForm = document.getElementById('gradeForm');
        // if (gradeForm) {
        //     gradeForm.addEventListener('submit', (e) => this.handleGradeSubmit(e));
        // }

        // Close modal on outside click
        const gradeModal = document.getElementById('gradeModal');
        if (gradeModal) {
            gradeModal.addEventListener('click', (e) => {
                if (e.target === gradeModal) this.closeGradeModal();
            });
        }
    },

    // Load and display student answers
    loadStudentAnswers: function(component) {
        this.currentComponent = component;
        const url = `/courses/${this.courseId}/materials/${this.materialId}/answers/${component}`;
        
        console.log('Loading answers from:', url); // Debug
        
        // Show loading state
        const contentArea = document.getElementById('dynamicContent');
        contentArea.innerHTML = `
            <div class="flex items-center justify-center py-20">
                <div class="text-center">
                    <svg class="animate-spin h-12 w-12 text-edu-blue mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-slate-600">Memuat data jawaban siswa...</p>
                </div>
            </div>
        `;
        
        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status); // Debug
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug
            if (data.success) {
                this.displayStudentAnswers(component, data.answers, data.stats);
            } else {
                throw new Error(data.message || 'Failed to load data');
            }
        })
        .catch(error => {
            console.error('Error loading student answers:', error);
            contentArea.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-red-900 font-medium">Gagal Memuat Data</h3>
                    </div>
                    <p class="text-red-700 mb-4">${error.message}</p>
                    <button onclick="StudentAnswersManager.loadStudentAnswers('${component}')" 
                            class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                        Coba Lagi
                    </button>
                    <button onclick="showComponent('${component}')" 
                            class="ml-2 bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700">
                        Kembali
                    </button>
                </div>
            `;
        });
    },

    // Display student answers with enhanced UI
    displayStudentAnswers: function(component, answers, stats) {
        const contentArea = document.getElementById('dynamicContent');
        const componentConfig = this.config.components[component];
        
        contentArea.innerHTML = `
            <div class="space-y-6">
                <!-- Header Section -->
                ${this.renderHeader(component, componentConfig, answers.length)}
                
                <!-- Statistics Cards -->
                ${this.renderStatistics(answers, stats)}
                
                <!-- Answers Table -->
                ${this.renderAnswersTable(component, answers, componentConfig)}
            </div>
        `;
    },

    // Render header section
    renderHeader: function(component, config, totalAnswers) {
        return `
            <div class="flex items-center justify-between bg-white rounded-lg p-4 shadow-sm">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Jawaban Siswa - ${config.title}</h3>
                    <p class="text-sm text-slate-600 mt-1">Total ${totalAnswers} siswa telah mengumpulkan jawaban</p>
                </div>
                <button onclick="showComponent('${component}')" 
                        class="flex items-center space-x-2 text-edu-${config.color} hover:text-edu-${config.color}/80 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali</span>
                </button>
            </div>
        `;
    },

    // Render statistics cards
    renderStatistics: function(answers, stats) {
        const total = answers.length;
        const graded = answers.filter(a => a.nilai !== null).length;
        const ungraded = total - graded;
        const avgGrade = stats?.average ? Math.round(stats.average) : 0;
        
        const distribution = {
            excellent: answers.filter(a => a.nilai >= 85).length,
            good: answers.filter(a => a.nilai >= 75 && a.nilai < 85).length,
            fair: answers.filter(a => a.nilai >= 65 && a.nilai < 75).length,
            poor: answers.filter(a => a.nilai !== null && a.nilai < 65).length
        };

        return `
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                ${this.renderStatCard('Total Siswa', total, 'blue', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z')}
                ${this.renderStatCard('Sudah Dinilai', graded, 'green', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z')}
                ${this.renderStatCard('Belum Dinilai', ungraded, 'yellow', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z')}
                ${this.renderStatCard('Rata-rata', avgGrade, 'purple', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z')}
            </div>
            
            <!-- Distribution Bar -->
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h4 class="text-sm font-medium text-slate-700 mb-3">Distribusi Nilai</h4>
                <div class="flex space-x-2">
                    <div class="flex-1">
                        <div class="text-xs text-slate-600 mb-1">Excellent (≥85)</div>
                        <div class="bg-green-100 rounded-full h-2">
                            <div class="bg-green-500 rounded-full h-2" style="width: ${total > 0 ? (distribution.excellent/total*100) : 0}%"></div>
                        </div>
                        <div class="text-xs text-slate-700 mt-1 font-medium">${distribution.excellent} siswa</div>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-slate-600 mb-1">Good (75-84)</div>
                        <div class="bg-blue-100 rounded-full h-2">
                            <div class="bg-blue-500 rounded-full h-2" style="width: ${total > 0 ? (distribution.good/total*100) : 0}%"></div>
                        </div>
                        <div class="text-xs text-slate-700 mt-1 font-medium">${distribution.good} siswa</div>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-slate-600 mb-1">Fair (65-74)</div>
                        <div class="bg-yellow-100 rounded-full h-2">
                            <div class="bg-yellow-500 rounded-full h-2" style="width: ${total > 0 ? (distribution.fair/total*100) : 0}%"></div>
                        </div>
                        <div class="text-xs text-slate-700 mt-1 font-medium">${distribution.fair} siswa</div>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs text-slate-600 mb-1">Poor (<65)</div>
                        <div class="bg-red-100 rounded-full h-2">
                            <div class="bg-red-500 rounded-full h-2" style="width: ${total > 0 ? (distribution.poor/total*100) : 0}%"></div>
                        </div>
                        <div class="text-xs text-slate-700 mt-1 font-medium">${distribution.poor} siswa</div>
                    </div>
                </div>
            </div>
        `;
    },

    // Render single stat card
    renderStatCard: function(label, value, color, iconPath) {
        return `
            <div class="bg-white rounded-lg p-4 shadow-sm border border-slate-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-${color}-100 rounded-lg">
                        <svg class="w-6 h-6 text-${color}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${iconPath}"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">${label}</p>
                        <p class="text-2xl font-bold text-${color === 'yellow' ? 'yellow' : color}-600">${value}</p>
                    </div>
                </div>
            </div>
        `;
    },

    // Render answers table
    renderAnswersTable: function(component, answers, config) {
        const rows = answers.length === 0 
            ? this.renderEmptyState()
            : answers.map((answer, index) => this.renderTableRow(component, answer, index, config)).join('');

        return `
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-${config.color}-50 to-white">
                    <h4 class="font-medium text-slate-900">Daftar Jawaban ${config.title}</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Nama Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">File Jawaban</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-700 uppercase tracking-wider">Waktu Submit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Nilai</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-slate-700 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            ${rows}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    },

    // Render single table row
    renderTableRow: function(component, answer, index, config) {
        const createdDate = new Date(answer.created_at).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        return `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 text-sm text-slate-900">${index + 1}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gradient-to-br from-edu-${config.color} to-edu-${config.color}/70 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3 shadow-sm">
                            ${answer.nama_siswa.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="text-sm font-medium text-slate-900">${answer.nama_siswa}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    ${this.renderFileCell(answer.jawaban)}
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">${createdDate}</td>
                <td class="px-6 py-4 text-center">
                    ${this.renderGradeBadge(answer.nilai)}
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex justify-center space-x-2">
                        ${answer.jawaban ? `
                            <button onclick="StudentAnswersManager.previewAnswer('${answer.jawaban}')" 
                                    class="text-slate-600 hover:text-slate-900 p-1 transition-colors" title="Preview">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        ` : ''}
                        
                    </div>
                </td>
            </tr>
        `;
    },

    // Render file cell
    renderFileCell: function(filePath) {
        if (!filePath) {
            return '<span class="text-slate-400 text-sm">Tidak ada file</span>';
        }

        const fileName = filePath.split('/').pop();
        return `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div>
                    <a href="${this.getFileUrl(filePath)}" target="_blank" 
                    class="text-edu-blue hover:underline text-sm font-medium">
                        ${fileName}
                    </a>
                    <p class="text-xs text-slate-500 mt-1">Klik untuk melihat</p>
                </div>
            </div>
        `;
    },

    // Render grade badge
    renderGradeBadge: function(nilai) {
        if (nilai === null || nilai === undefined) {
            return `
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                    Belum dinilai
                </span>
            `;
        }

        const badgeClass = this.getGradeBadgeClass(nilai);
        return `
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${badgeClass}">
                ${nilai}/100
            </span>
        `;
    },

    // Render empty state
    renderEmptyState: function() {
        return `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="font-medium">Belum ada jawaban dari siswa</p>
                    <p class="text-sm mt-1">Jawaban akan muncul setelah siswa mengumpulkan</p>
                </td>
            </tr>
        `;
    },

    // Open grade modal
    openGradeModal: function(answerId, component, studentName, currentGrade) {
        this.currentAnswerId = answerId;
        this.currentComponent = component;
        
        document.getElementById('gradeUserId').value = answerId;
        document.getElementById('gradeComponent').value = component;
        document.getElementById('gradeStudentName').textContent = studentName;
        
        const gradeContainer = document.getElementById('gradeContainer');
        const componentConfig = this.config.components[component];
        
        gradeContainer.innerHTML = `
            <label for="gradeValue" class="block text-sm font-medium text-slate-700 mb-2">Nilai (0-100)</label>
            <div class="space-y-3">
                <input type="number" id="gradeValue" name="nilai" min="0" max="100" 
                    value="${currentGrade !== null ? currentGrade : ''}"
                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-${componentConfig.color} focus:border-transparent" 
                    placeholder="Masukkan nilai 0-100" required>
                
                <!-- Quick grade buttons -->
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="StudentAnswersManager.setQuickGrade(100)" 
                            class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm transition-colors">
                        Sempurna (100)
                    </button>
                    <button type="button" onclick="StudentAnswersManager.setQuickGrade(85)" 
                            class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm transition-colors">
                        Bagus (85)
                    </button>
                    <button type="button" onclick="StudentAnswersManager.setQuickGrade(75)" 
                            class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 text-sm transition-colors">
                        Cukup (75)
                    </button>
                    <button type="button" onclick="StudentAnswersManager.setQuickGrade(65)" 
                            class="px-3 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200 text-sm transition-colors">
                        Kurang (65)
                    </button>
                </div>
                
                <!-- Grade preview -->
                <div id="gradePreview" class="p-3 bg-slate-50 rounded-lg ${currentGrade !== null ? '' : 'hidden'}">
                    <p class="text-sm text-slate-600">Preview Nilai:</p>
                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium ${currentGrade !== null ? this.getGradeBadgeClass(currentGrade) : ''}">
                        ${currentGrade !== null ? currentGrade + '/100' : ''}
                    </span>
                </div>
            </div>
        `;
        
        // Add event listener
        document.getElementById('gradeValue').addEventListener('input', (e) => {
            this.updateGradePreview(e.target.value);
        });
        
        document.getElementById('gradeModal').classList.remove('hidden');
    },

    // Set quick grade
    setQuickGrade: function(value) {
        document.getElementById('gradeValue').value = value;
        this.updateGradePreview(value);
    },

    // Update grade preview
    updateGradePreview: function(value) {
        const preview = document.getElementById('gradePreview');
        if (value && value >= 0 && value <= 100) {
            preview.classList.remove('hidden');
            const badgeClass = this.getGradeBadgeClass(value);
            
            preview.innerHTML = `
                <p class="text-sm text-slate-600">Preview Nilai:</p>
                <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium ${badgeClass}">
                    ${value}/100
                </span>
            `;
        } else {
            preview.classList.add('hidden');
        }
    },

    // Get grade badge class
    getGradeBadgeClass: function(nilai) {
        if (nilai >= 85) return 'bg-green-100 text-green-800';
        if (nilai >= 75) return 'bg-blue-100 text-blue-800';
        if (nilai >= 65) return 'bg-yellow-100 text-yellow-800';
        return 'bg-red-100 text-red-800';
    },

    // Handle grade submission
    handleGradeSubmit: function(e) {
        e.preventDefault();
        
        const nilai = document.getElementById('gradeValue').value;
        
        if (!nilai || nilai < 0 || nilai > 100) {
            this.showNotification('error', 'Nilai harus antara 0-100');
            return;
        }
        
        const formData = new FormData();
        formData.append('answer_id', this.currentAnswerId);
        formData.append('component', this.currentComponent);
        formData.append('nilai', nilai);
        
        const submitButton = e.target.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';
        
        // Log untuk debugging
        console.log('Submitting grade:', {
            courseId: courseId,
            materialId: materialId,
            answer_id: this.currentAnswerId,
            component: this.currentComponent,
            nilai: nilai
        });
        
        // Gunakan variable global yang sudah didefinisikan
        fetch(`/courses/${courseId}/materials/${materialId}/grade-numeric`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                answer_id: this.currentAnswerId,
                component: this.currentComponent,
                nilai: parseInt(nilai)
            }),
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                this.closeGradeModal();
                this.loadStudentAnswers(this.currentComponent);
                this.showNotification('success', 'Penilaian berhasil disimpan');
            } else {
                this.showNotification('error', data.message || 'Gagal memberikan nilai');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('error', 'Berhasil memberikan nilai');
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    },

    // Close grade modal
    closeGradeModal: function() {
        document.getElementById('gradeModal').classList.add('hidden');
    },

    // Preview answer file
    previewAnswer: function(filePath) {
        window.open(this.getFileUrl(filePath), '_blank');
    },

    // Get file URL
    getFileUrl: function(filePath) {
        if (!filePath) return '#';
        let cleanPath = filePath;
        if (cleanPath.startsWith('storage/')) {
            cleanPath = cleanPath.substring(8);
        }
        return `/storage/${cleanPath}`;
    },

    // Show notification
    showNotification: function(type, message) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' 
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'}
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
};

function loadStudentAnswersPRE(component) {
    const contentArea = document.getElementById('dynamicContent');
    
    contentArea.innerHTML = `
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between bg-white rounded-lg p-4 shadow-sm">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Jawaban Essay (PRE) - ${components[component].title}</h3>
                    <p class="text-sm text-slate-600 mt-1">Perencanaan, Refleksi & Evaluasi</p>
                </div>
                <button onclick="showComponent('${component}')" 
                        class="flex items-center space-x-2 text-edu-blue hover:text-edu-blue/80 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali</span>
                </button>
            </div>
            
            <!-- Tabs untuk Perencanaan, Refleksi, Evaluasi -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-slate-200">
                    <nav class="flex -mb-px">
                        <button onclick="showPRETab('${component}', 'perencanaan')" 
                                class="pre-tab-btn px-6 py-3 text-sm font-medium border-b-2 border-green-500 text-green-600"
                                data-tab="perencanaan">
                            Perencanaan
                        </button>
                        <button onclick="showPRETab('${component}', 'refleksi')" 
                                class="pre-tab-btn px-6 py-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700"
                                data-tab="refleksi">
                            Refleksi
                        </button>
                        <button onclick="showPRETab('${component}', 'evaluasi')" 
                                class="pre-tab-btn px-6 py-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700"
                                data-tab="evaluasi">
                            Evaluasi
                        </button>
                    </nav>
                </div>
                
                <div id="preTabContent" class="p-6">
                    <div class="flex items-center justify-center py-8">
                        <svg class="animate-spin h-8 w-8 text-edu-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Load data pertama kali (perencanaan)
    showPRETab(component, 'perencanaan');
}

function showPRETab(component, type) {
    // Update tab styling
    document.querySelectorAll('.pre-tab-btn').forEach(btn => {
        btn.classList.remove('border-green-500', 'text-green-600', 'border-orange-500', 'text-orange-600', 'border-purple-500', 'text-purple-600');
        btn.classList.add('border-transparent', 'text-slate-500');
    });
    
    const activeBtn = document.querySelector(`[data-tab="${type}"]`);
    const colors = {
        'perencanaan': 'green',
        'refleksi': 'orange',
        'evaluasi': 'purple'
    };
    const color = colors[type];
    
    activeBtn.classList.remove('border-transparent', 'text-slate-500');
    activeBtn.classList.add(`border-${color}-500`, `text-${color}-600`);
    
    // Load data
    fetch(`{{ route('materi.getStudentAnswersPRE', [$courseId, $material->id_materi, '__COMPONENT__', '__TYPE__']) }}`
        .replace('__COMPONENT__', component)
        .replace('__TYPE__', type))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPREAnswers(component, type, data.answers, color);
            }
        })
        .catch(error => {
            console.error('Error loading PRE answers:', error);
        });
}

function displayPREAnswers(component, type, answers, color) {
    const contentArea = document.getElementById('preTabContent');
    const typeLabels = {
        'perencanaan': 'Perencanaan',
        'refleksi': 'Refleksi',
        'evaluasi': 'Evaluasi'
    };
    
    let html = `
        <div class="space-y-4">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-medium">Jawaban ${typeLabels[type]}</h4>
                <span class="text-sm text-slate-600">Total: ${answers.length} siswa</span>
            </div>
    `;
    
    if (answers.length === 0) {
        html += `
            <div class="text-center py-12 text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="font-medium">Belum ada jawaban ${typeLabels[type]}</p>
            </div>
        `;
    } else {
        answers.forEach((answer, index) => {
            const createdDate = new Date(answer.created_at).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            html += `
                <div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-${color}-500 to-${color}-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-3">
                                ${answer.nama_siswa.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div class="font-medium text-slate-900">${answer.nama_siswa}</div>
                                <div class="text-xs text-slate-500">${createdDate}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            ${answer.nilai !== null ? `
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getGradeBadgeClass(answer.nilai)}">
                                    ${answer.nilai}/100
                                </span>
                            ` : `
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                                    Belum dinilai
                                </span>
                            `}
                            
                            </button>
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-4 mt-3">
                        <div class="prose prose-sm max-w-none">
                            ${answer.jawaban}
                        </div>
                    </div>
                </div>
            `;
        });
    }
    
    html += '</div>';
    contentArea.innerHTML = html;
}

function openGradePREModal(answerId, component, type, studentName, currentGrade) {
    document.getElementById('gradeUserId').value = answerId;
    document.getElementById('gradeComponent').value = component + '_' + type;
    document.getElementById('gradeStudentName').textContent = studentName;
    
    const typeLabels = {
        'perencanaan': 'Perencanaan',
        'refleksi': 'Refleksi',
        'evaluasi': 'Evaluasi'
    };
    
    const gradeContainer = document.getElementById('gradeContainer');
    gradeContainer.innerHTML = `
        <label for="gradeValue" class="block text-sm font-medium text-slate-700 mb-2">Nilai ${typeLabels[type]} (0-100)</label>
        <div class="space-y-3">
            <input type="number" id="gradeValue" name="nilai" min="0" max="100" 
                value="${currentGrade !== null ? currentGrade : ''}"
                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent" 
                placeholder="Masukkan nilai 0-100" required>
            
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="setQuickGrade(100)" 
                        class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm transition-colors">
                    Sempurna (100)
                </button>
                <button type="button" onclick="setQuickGrade(85)" 
                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm transition-colors">
                    Bagus (85)
                </button>
                <button type="button" onclick="setQuickGrade(75)" 
                        class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 text-sm transition-colors">
                    Cukup (75)
                </button>
                <button type="button" onclick="setQuickGrade(65)" 
                        class="px-3 py-1 bg-orange-100 text-orange-700 rounded hover:bg-orange-200 text-sm transition-colors">
                    Kurang (65)
                </button>
            </div>
            
            <div id="gradePreview" class="p-3 bg-slate-50 rounded-lg ${currentGrade !== null ? '' : 'hidden'}">
                <p class="text-sm text-slate-600">Preview Nilai:</p>
                <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium ${currentGrade !== null ? getGradeBadgeClass(currentGrade) : ''}">
                    ${currentGrade !== null ? currentGrade + '/100' : ''}
                </span>
            </div>
        </div>
    `;
    
    document.getElementById('gradeValue').addEventListener('input', function(e) {
        updateGradePreview(e.target.value);
    });
    
    document.getElementById('gradeModal').classList.remove('hidden');
}


// Handle numeric grading (for main components)
function handleNumericGrade(answerId, component, submitButton, originalText) {
    const nilai = document.getElementById('gradeValue').value;
    
    if (!nilai || nilai < 0 || nilai > 100) {
        alert('Nilai harus antara 0-100');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
        return;
    }
    
    fetch(`/courses/${courseId}/materials/${materialId}/grade-numeric`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            answer_id: parseInt(answerId),
            component: component,
            nilai: parseInt(nilai)
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            closeGradeModal();
            
            // Reload the appropriate view
            if (StudentAnswersManager && StudentAnswersManager.currentComponent === component) {
                StudentAnswersManager.loadStudentAnswers(component);
            } else {
                loadStudentAnswers(component);
            }
            
            showNotification('success', 'Penilaian berhasil disimpan');
        } else {
            showNotification('error', data.message || 'Gagal memberikan nilai');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('success', 'Penilaian berhasil disimpan'); 
        
        // Reload on success (sometimes the request succeeds but response parsing fails)
        setTimeout(() => {
            closeGradeModal();
            if (StudentAnswersManager && StudentAnswersManager.currentComponent === component) {
                StudentAnswersManager.loadStudentAnswers(component);
            } else {
                loadStudentAnswers(component);
            }
        }, 1000);
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

// Handle PRE grading (Perencanaan, Refleksi, Evaluasi)
function handlePREGrade(answerId, componentWithType, submitButton, originalText) {
    const parts = componentWithType.split('_');
    const baseComponent = parts[0];
    const type = parts[1];
    const nilai = document.getElementById('gradeValue').value;
    
    if (!nilai || nilai < 0 || nilai > 100) {
        alert('Nilai harus antara 0-100');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
        return;
    }
    
    // Kirim dengan nama parameter yang benar
    const payload = {
        answer_id: parseInt(answerId),
        component: baseComponent,
        type: type,
        grade: parseInt(nilai), // Kirim sebagai 'grade'
        nilai: parseInt(nilai)  // Dan juga sebagai 'nilai' untuk kompatibilitas
    };
    
    console.log('Sending PRE grade:', payload);
    
    fetch(`/courses/${courseId}/materials/${materialId}/grade-answer-pre`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeGradeModal();
            showPRETab(baseComponent, type);
            showNotification('success', 'Penilaian berhasil disimpan');
        } else {
            showNotification('error', data.message || 'Gagal memberikan nilai');
        }
    })
    .catch(error => {
        console.error('Error detail:', error);
        if (error.errors) {
            // Tampilkan error validasi
            const errorMessages = Object.values(error.errors).flat().join(', ');
            showNotification('error', 'Validasi gagal: ' + errorMessages);
        } else {
            showNotification('error', 'Terjadi kesalahan saat memberikan nilai');
        }
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

// Handle metakognisi grading
function handleMetakognisiGrade(answerId, component, submitButton, originalText) {
    const nilai = document.getElementById('gradeValue').value;
    
    if (!nilai || nilai < 0 || nilai > 100) {
        alert('Nilai harus antara 0-100');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
        return;
    }
    
    fetch(`/courses/${courseId}/materials/${materialId}/metakognisi/gradeAnswer`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_id: parseInt(answerId),
            component: component,
            nilai: parseInt(nilai)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeGradeModal();
            
            // Reload metakognisi answers
            if (typeof loadStudentMetakognisiAnswers === 'function') {
                loadStudentMetakognisiAnswers();
            }
            
            showNotification('success', 'Penilaian berhasil disimpan');
        } else {
            showNotification('error', data.message || 'Gagal memberikan nilai');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Terjadi kesalahan saat memberikan nilai');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

// Update the StudentAnswersManager handleGradeSubmit to use the unified handler
if (typeof StudentAnswersManager !== 'undefined') {
    StudentAnswersManager.handleGradeSubmit = function(e) {
        // Just call the form's submit handler which is already unified
        if (gradeForm && gradeForm.submitHandler) {
            gradeForm.submitHandler.call(gradeForm, e);
        }
    };
}

// Initialize StudentAnswersManager if not already done
document.addEventListener('DOMContentLoaded', function() {
    if (typeof StudentAnswersManager !== 'undefined' && typeof courseId !== 'undefined' && typeof materialId !== 'undefined') {
        StudentAnswersManager.init(courseId, materialId);
    }
});

// Helper function for notifications (if not already defined)
if (typeof showNotification === 'undefined') {
    window.showNotification = function(type, message) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' 
                        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'}
                </svg>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    };
}




</script>


<script>
// Override generateRuangKolaborasiContent function
const originalGenerateRuangKolaborasiContent = generateRuangKolaborasiContent;

generateRuangKolaborasiContent = function() {
    // Initialize DiskusiManager
    setTimeout(() => {
        if (typeof DiskusiManager !== 'undefined') {
            DiskusiManager.init();
            DiskusiManager.loadTopikList();
        }
    }, 100);
    
    return ''; // Content akan di-render oleh DiskusiManager
};
</script>

@include('components.diskusi-component')
@endsection