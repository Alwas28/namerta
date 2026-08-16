@extends('layouts.home')

@section('css_tambahan')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 200px;
    }
    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        padding: 1.5rem;
    }
    .completed-section {
        border-left: 4px solid #10b981;
    }
    .locked-section {
        opacity: 0.6;
        background-color: #f8fafc;
    }
</style>
@endsection

@section('konten')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb/Header -->
        <div class="mb-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('modul.show', [$courseId, $modul->id_modul]) }}" 
                           class="text-slate-500 hover:text-slate-700 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            {{ $modul->nama_modul }}
                        </a>
                    </li>
                    <li class="text-slate-400">/</li>
                    <li class="text-slate-900 font-medium">Tes Kompetensi</li>
                </ol>
            </nav>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h1 class="text-3xl font-bold text-slate-900 mb-2">Tes Kompetensi - {{ $modul->nama_modul }}</h1>
                <p class="text-slate-600">
                    @if($canManage)
                        Kelola soal tes kompetensi untuk menguji pemahaman siswa
                    @else
                        Kerjakan tes kompetensi untuk menguji pemahaman Anda terhadap materi
                    @endif
                </p>
            </div>
        </div>

        <!-- Konten Tes Kompetensi -->
        <div class="section-card">
            <div class="section-header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold">
                            {{ $canManage ? 'Manajemen Tes' : 'Tes Kompetensi' }}
                        </h2>
                    </div>
                    @if($jenisTestKompetensi)
                        <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">
                            {{ $jenisTestKompetensi->essay == 'Y' ? 'Essay' : 'Pilihan Ganda' }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if($canManage)
                    <!-- TEACHER VIEW -->
                    <!-- Pilih Jenis Tes -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-4">Pengaturan Jenis Tes</h3>
                        <form action="{{ route('tes-kompetensi.updateJenisTes', [$courseId, $modul->id_modul]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="jenis_tes" value="pilgan" 
                                           {{ !isset($jenisTestKompetensi) || $jenisTestKompetensi->essay == 'N' ? 'checked' : '' }}
                                           onchange="toggleTestType(this.value)" class="mr-2">
                                    <span>Pilihan Ganda</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="jenis_tes" value="essay" 
                                           {{ isset($jenisTestKompetensi) && $jenisTestKompetensi->essay == 'Y' ? 'checked' : '' }}
                                           onchange="toggleTestType(this.value)" class="mr-2">
                                    <span>Essay</span>
                                </label>
                                <button type="submit" class="bg-edu-blue text-white px-4 py-2 rounded-lg hover:bg-edu-blue/90">
                                    Simpan Jenis Tes
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="border-t pt-6">
                        <!-- Pilihan Ganda Section -->
                        <div id="pilganSection" class="{{ isset($jenisTestKompetensi) && $jenisTestKompetensi->essay == 'Y' ? 'hidden' : '' }}">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium">Soal Pilihan Ganda</h3>
                                <button onclick="openAddSoalModal()" class="bg-edu-green text-white px-4 py-2 rounded-lg hover:bg-edu-green/90">
                                    + Tambah Soal
                                </button>
                            </div>

                            @if(isset($soalPilgan) && $soalPilgan->count() > 0)
                                <div class="space-y-4">
                                    @foreach($soalPilgan as $index => $soal)
                                        <div class="border border-slate-200 rounded-lg p-4">
                                            <div class="flex justify-between items-start mb-3">
                                                <h4 class="font-medium">Soal {{ $index + 1 }}</h4>
                                                <div class="flex space-x-2">
                                                    <button onclick="editSoal({{ $soal->id_soal_tes_kompetensi }}, '{{ addslashes($soal->soal) }}', '{{ addslashes($soal->a) }}', '{{ addslashes($soal->b) }}', '{{ addslashes($soal->c) }}', '{{ addslashes($soal->d) }}', '{{ $soal->jawaban_benar }}')" 
                                                            class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">
                                                        Edit
                                                    </button>
                                                    <button onclick="deleteSoal({{ $soal->id_soal_tes_kompetensi }})" 
                                                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                            <p class="mb-2 text-slate-800">{{ $soal->soal }}</p>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <div class="flex items-center">
                                                    <span class="font-medium mr-2 {{ $soal->jawaban_benar == 'a' ? 'text-green-600' : '' }}">A.</span>
                                                    <span class="{{ $soal->jawaban_benar == 'a' ? 'text-green-600 font-medium' : '' }}">{{ $soal->a }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium mr-2 {{ $soal->jawaban_benar == 'b' ? 'text-green-600' : '' }}">B.</span>
                                                    <span class="{{ $soal->jawaban_benar == 'b' ? 'text-green-600 font-medium' : '' }}">{{ $soal->b }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium mr-2 {{ $soal->jawaban_benar == 'c' ? 'text-green-600' : '' }}">C.</span>
                                                    <span class="{{ $soal->jawaban_benar == 'c' ? 'text-green-600 font-medium' : '' }}">{{ $soal->c }}</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="font-medium mr-2 {{ $soal->jawaban_benar == 'd' ? 'text-green-600' : '' }}">D.</span>
                                                    <span class="{{ $soal->jawaban_benar == 'd' ? 'text-green-600 font-medium' : '' }}">{{ $soal->d }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6 pt-6 border-t">
                                    <div class="flex space-x-3">
                                        <button onclick="loadTesKompetensiAnswers('pilgan')" class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90">
                                            Lihat Jawaban Siswa
                                        </button>
                                        <button onclick="gradeAllAnswers()" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                            Nilai Semua Jawaban
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-500">Belum ada soal pilihan ganda. Klik tombol "Tambah Soal" untuk menambah soal baru.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Essay Section -->
                        <div id="essaySection" class="{{ !isset($jenisTestKompetensi) || $jenisTestKompetensi->essay == 'N' ? 'hidden' : '' }}">
                            <h3 class="text-lg font-medium mb-4">Soal Essay</h3>
                            <form action="{{ route('tes-kompetensi.storeUpdateSoalEssay', [$courseId, $modul->id_modul]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Soal Essay</label>
                                    <textarea name="soal_essay" rows="4" 
                                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                                              placeholder="Tulis soal essay di sini...">{{ isset($soalEssay) ? $soalEssay->soal : '' }}</textarea>
                                </div>
                                <div class="border-2 border-dashed border-slate-300 rounded-lg p-6 mb-4">
                                    <input type="file" name="file_soal" accept=".pdf,.doc,.docx" class="w-full">
                                    <p class="text-sm text-slate-500 mt-2">PDF, DOC, DOCX maksimal 10MB (opsional)</p>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="bg-edu-blue text-white px-6 py-2 rounded-lg hover:bg-edu-blue/90">
                                        Simpan Soal Essay
                                    </button>
                                </div>
                            </form>

                            @if(isset($soalEssay))
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="font-medium mb-2">Soal Saat Ini:</h4>
                                    <div class="bg-slate-50 rounded-lg p-4 mb-4">
                                        <p>{{ $soalEssay->soal }}</p>
                                    </div>
                                    
                                    <button onclick="loadTesKompetensiAnswers('essay')" class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90">
                                        Lihat Jawaban Essay
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- STUDENT VIEW -->
                    @if(isset($jenisTestKompetensi))
                        @if($jenisTestKompetensi->essay == 'N')
                            <!-- Pilihan Ganda untuk Siswa -->
                            @if(isset($soalPilgan) && $soalPilgan->count() > 0)
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium mb-4">Tes Kompetensi - Pilihan Ganda</h3>
                                    <p class="text-slate-600 mb-4">Jawab semua pertanyaan berikut ini:</p>
                                    
                                    @php 
                                        $allAnswered = $jawabanTesKompetensi->count() == $soalPilgan->count();
                                        $allGraded = $jawabanTesKompetensi->whereNotNull('benar')->count() == $soalPilgan->count();
                                        $score = $jawabanTesKompetensi->where('benar', 'Y')->count();
                                    @endphp
                                    
                                    <form action="{{ route('tes-kompetensi.submitJawabanPilgan', [$courseId, $modul->id_modul]) }}" method="POST" id="tesKompetensiForm">
                                        @csrf
                                        <div class="space-y-6">
                                            @foreach($soalPilgan as $index => $soal)
                                                <div class="border border-slate-200 rounded-lg p-4">
                                                    <h4 class="font-medium mb-3">{{ $index + 1 }}. {{ $soal->soal }}</h4>
                                                    <div class="space-y-2">
                                                        @php $userAnswer = $jawabanTesKompetensi->where('id_soal_tes_kompetensi', $soal->id_soal_tes_kompetensi)->first(); @endphp
                                                        
                                                        @foreach(['a', 'b', 'c', 'd'] as $option)
                                                            <label class="flex items-center p-2 rounded hover:bg-slate-50 cursor-pointer
                                                                        {{ $allGraded ? 'cursor-not-allowed' : '' }}">
                                                                <input type="radio" name="jawaban_{{ $soal->id_soal_tes_kompetensi }}" 
                                                                       value="{{ $option }}" 
                                                                       {{ $userAnswer && $userAnswer->jawaban == $option ? 'checked' : '' }}
                                                                       {{ $allGraded ? 'disabled' : '' }}
                                                                       class="mr-3">
                                                                <span class="text-sm flex-grow">{{ strtoupper($option) }}. {{ $soal->$option }}</span>
                                                                @if($allGraded && $userAnswer && $userAnswer->jawaban == $option)
                                                                    @if($soal->jawaban_benar == $option)
                                                                        <span class="ml-auto text-green-600 font-bold">✓</span>
                                                                    @else
                                                                        <span class="ml-auto text-red-600 font-bold">✗</span>
                                                                    @endif
                                                                @elseif($allGraded && $soal->jawaban_benar == $option)
                                                                    <span class="ml-auto text-green-600 text-sm">(Benar)</span>
                                                                @endif
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        @if(!$allGraded)
                                            <div class="mt-6 text-center">
                                                <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                                    {{ $allAnswered ? 'Update Jawaban' : 'Simpan Jawaban' }}
                                                </button>
                                            </div>
                                        @else
                                            <div class="mt-6 bg-green-50 rounded-lg p-4 text-center">
                                                <p class="text-green-800 font-medium">Tes telah selesai dinilai</p>
                                                <p class="text-green-600 text-sm mt-1">
                                                    Skor Anda: {{ $score }}/{{ $soalPilgan->count() }} ({{ round(($score/$soalPilgan->count())*100, 1) }}%)
                                                </p>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-500">Soal tes kompetensi belum tersedia.</p>
                                </div>
                            @endif
                        @else
                            <!-- Essay untuk Siswa -->
                            @if(isset($soalEssay))
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium mb-4">Tes Kompetensi - Essay</h3>
                                    <div class="bg-slate-50 rounded-lg p-6 mb-4">
                                        <h4 class="font-medium mb-2">Soal:</h4>
                                        <p>{{ $soalEssay->soal }}</p>
                                    </div>
                                    
                                    @php $jawabanEssayUser = $jawabanEssay->first(); @endphp
                                    
                                    @if(!$jawabanEssayUser)
                                        <form action="{{ route('tes-kompetensi.submitJawabanEssay', [$courseId, $modul->id_modul]) }}" method="POST">
                                            @csrf
                                            <div class="mb-4">
                                                <label class="block text-sm font-medium text-slate-700 mb-2">Jawaban Anda:</label>
                                                <textarea name="jawaban" rows="8" 
                                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                                                        placeholder="Tulis jawaban essay Anda di sini..." required></textarea>
                                            </div>
                                            <div class="text-right">
                                                <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                                    Simpan Jawaban Essay
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="bg-green-50 rounded-lg p-4">
                                            <div class="flex justify-between items-center mb-2">
                                                <p class="text-green-800 font-medium">Jawaban telah disimpan</p>
                                                @if($jawabanEssayUser->nilai !== null)
                                                    <span class="bg-green-600 text-white px-4 py-2 rounded-full text-sm font-bold">
                                                        Nilai: {{ $jawabanEssayUser->nilai }}/100
                                                    </span>
                                                @else
                                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                                        Menunggu penilaian
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="bg-white rounded p-3 text-sm mt-3">
                                                {{ $jawabanEssayUser->jawaban }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <p class="text-slate-500">Soal essay belum tersedia.</p>
                                </div>
                            @endif
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-slate-500">Jenis tes kompetensi belum ditentukan oleh guru.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Lihat Jawaban Siswa (Teacher Only) -->
@if($canManage)
<div id="studentAnswersModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900" id="modalTitle">Jawaban Siswa</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-6 max-h-[calc(90vh-120px)] overflow-y-auto">
            <div id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Soal Pilihan Ganda (Teacher Only) -->
<div id="soalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900" id="soalModalTitle">Tambah Soal Pilihan Ganda</h3>
                <button onclick="closeSoalModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- TRADITIONAL FORM SUBMISSION -->
        <form id="soalForm" method="POST" onsubmit="return handleFormSubmit(event)">
            @csrf
            <input type="hidden" name="_method" id="form_method" value="POST">
            <input type="hidden" id="soal_id" name="soal_id">
            
            <div class="px-6 py-6 max-h-[calc(90vh-160px)] overflow-y-auto">
                <div class="mb-4">
                    <label for="soal_text" class="block text-sm font-medium text-slate-700 mb-2">Soal</label>
                    <textarea id="soal_text" name="soal" rows="3" required
                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                              placeholder="Masukkan pertanyaan..."></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="option_a" class="block text-sm font-medium text-slate-700 mb-2">Pilihan A</label>
                        <input type="text" id="option_a" name="a" required
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                               placeholder="Pilihan A">
                    </div>
                    <div>
                        <label for="option_b" class="block text-sm font-medium text-slate-700 mb-2">Pilihan B</label>
                        <input type="text" id="option_b" name="b" required
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                               placeholder="Pilihan B">
                    </div>
                    <div>
                        <label for="option_c" class="block text-sm font-medium text-slate-700 mb-2">Pilihan C</label>
                        <input type="text" id="option_c" name="c" required
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                               placeholder="Pilihan C">
                    </div>
                    <div>
                        <label for="option_d" class="block text-sm font-medium text-slate-700 mb-2">Pilihan D</label>
                        <input type="text" id="option_d" name="d" required
                               class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                               placeholder="Pilihan D">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jawaban Benar</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="jawaban_benar" value="a" required class="mr-2">
                            <span>A</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="jawaban_benar" value="b" required class="mr-2">
                            <span>B</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="jawaban_benar" value="c" required class="mr-2">
                            <span>C</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="jawaban_benar" value="d" required class="mr-2">
                            <span>D</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end space-x-3">
                <button type="button" onclick="closeSoalModal()" 
                        class="px-4 py-2 text-slate-600 hover:text-slate-800">Batal</button>
                <button type="submit" id="submitBtn"
                        class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                    Simpan Soal
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('js_tambahan')
<script>
@if($canManage)
// TEACHER FUNCTIONS

// Toggle antara pilihan ganda dan essay
function toggleTestType(type) {
    const pilganSection = document.getElementById('pilganSection');
    const essaySection = document.getElementById('essaySection');
    
    if (type === 'pilgan') {
        pilganSection.classList.remove('hidden');
        essaySection.classList.add('hidden');
    } else {
        pilganSection.classList.add('hidden');
        essaySection.classList.remove('hidden');
    }
}

// Modal functions untuk soal pilihan ganda
function openAddSoalModal() {
    document.getElementById('soalModalTitle').textContent = 'Tambah Soal Pilihan Ganda';
    document.getElementById('soal_id').value = '';
    document.getElementById('soalForm').reset();
    document.getElementById('soalModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function editSoal(id, soal, a, b, c, d, jawaban_benar) {
    document.getElementById('soalModalTitle').textContent = 'Edit Soal Pilihan Ganda';
    document.getElementById('soal_id').value = id;
    document.getElementById('soal_text').value = soal;
    document.getElementById('option_a').value = a;
    document.getElementById('option_b').value = b;
    document.getElementById('option_c').value = c;
    document.getElementById('option_d').value = d;
    document.querySelector(`input[name="jawaban_benar"][value="${jawaban_benar}"]`).checked = true;
    document.getElementById('soalModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeSoalModal() {
    document.getElementById('soalModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function submitSoal(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const soalId = document.getElementById('soal_id').value;
    
    const url = soalId ? 
        `{{ route('tes-kompetensi.updateSoalPilgan', [$courseId, $modul->id_modul, 'SOAL_ID']) }}`.replace('SOAL_ID', soalId) :
        `{{ route('tes-kompetensi.storeSoalPilgan', [$courseId, $modul->id_modul]) }}`;
    
    const method = soalId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            soal: formData.get('soal'),
            a: formData.get('a'),
            b: formData.get('b'),
            c: formData.get('c'),
            d: formData.get('d'),
            jawaban_benar: formData.get('jawaban_benar')
        })
    })
    .then(response => {
        // Handle both JSON and non-JSON responses
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error(`Server returned non-JSON response: ${text.substring(0, 100)}...`);
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            showToast(data.message, 'success');
            closeSoalModal();
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menyimpan soal', 'error');
    });
}

function deleteSoal(soalId) {
    if (!confirm('Apakah Anda yakin ingin menghapus soal ini?')) {
        return;
    }
    
    const url = `{{ route('tes-kompetensi.deleteSoalPilgan', [$courseId, $modul->id_modul, 'SOAL_ID']) }}`.replace('SOAL_ID', soalId);
    
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error(`Server returned non-JSON response: ${text.substring(0, 100)}...`);
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showToast(data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menghapus soal', 'error');
    });
}

function loadTesKompetensiAnswers(type) {
    const modal = document.getElementById('studentAnswersModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalContent = document.getElementById('modalContent');
    
    modalTitle.textContent = type === 'pilgan' ? 'Jawaban Tes Pilihan Ganda' : 'Jawaban Tes Essay';
    modalContent.innerHTML = '<div class="text-center py-8">Loading...</div>';
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    const url = `{{ route('tes-kompetensi.getJawabanSiswa', [$courseId, $modul->id_modul, 'TYPE']) }}`.replace('TYPE', type);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTesKompetensiAnswers(data.answers, type);
            } else {
                modalContent.innerHTML = '<div class="text-center py-8">Tidak ada jawaban.</div>';
            }
        })
        .catch(error => {
            modalContent.innerHTML = '<div class="text-center py-8 text-red-500">Error loading data.</div>';
        });
}

function displayTesKompetensiAnswers(answers, type) {
    const modalContent = document.getElementById('modalContent');
    let html = '';
    
    if (answers.length === 0) {
        html = '<div class="text-center py-8">Belum ada jawaban dari siswa.</div>';
    } else {
        if (type === 'pilgan') {
            // Group answers by student
            const studentAnswers = {};
            answers.forEach(answer => {
                if (!studentAnswers[answer.id_user]) {
                    studentAnswers[answer.id_user] = {
                        nama_siswa: answer.nama_siswa,
                        answers: []
                    };
                }
                studentAnswers[answer.id_user].answers.push(answer);
            });
            
            Object.values(studentAnswers).forEach(student => {
                const totalSoal = student.answers.length;
                const benarCount = student.answers.filter(a => a.benar === 'Y').length;
                const salahCount = student.answers.filter(a => a.benar === 'N').length;
                const belumDinilai = student.answers.filter(a => a.benar === null).length;
                
                html += `
                    <div class="border rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="font-semibold">${student.nama_siswa}</h4>
                                <div class="text-sm text-gray-600 space-x-4">
                                    <span>Total: ${totalSoal}</span>
                                    <span class="text-green-600">Benar: ${benarCount}</span>
                                    <span class="text-red-600">Salah: ${salahCount}</span>
                                    <span class="text-yellow-600">Belum dinilai: ${belumDinilai}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                ${belumDinilai > 0 ? 
                                    `<button onclick="gradePilganAnswers(${student.answers[0].id_user})" 
                                            class="bg-edu-purple text-white px-4 py-2 rounded text-sm hover:bg-edu-purple/90">
                                        Nilai Otomatis
                                    </button>` :
                                    `<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                        Skor: ${benarCount}/${totalSoal} (${Math.round((benarCount/totalSoal)*100)}%)
                                    </span>`
                                }
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-2 text-sm max-h-40 overflow-y-auto">
                            ${student.answers.map((answer, index) => `
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <span>Soal ${index + 1}: ${answer.jawaban.toUpperCase()}</span>
                                    <span class="${answer.benar === 'Y' ? 'text-green-600' : answer.benar === 'N' ? 'text-red-600' : 'text-yellow-600'}">
                                        ${answer.benar === 'Y' ? '✓ Benar' : answer.benar === 'N' ? '✗ Salah' : 'Belum dinilai'}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });
        } else {
            // Essay answers
            answers.forEach(answer => {
                html += `
                    <div class="border rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-semibold">${answer.nama_siswa}</h4>
                                <p class="text-sm text-gray-500">Submit: ${formatDate(answer.created_at)}</p>
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <h5 class="font-medium mb-2">Jawaban:</h5>
                            <div class="bg-gray-50 rounded p-3 text-sm max-h-40 overflow-y-auto">
                                ${answer.jawaban}
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    }
    
    modalContent.innerHTML = html;
}

function gradePilganAnswers(userId) {
    if (!confirm('Nilai otomatis jawaban pilihan ganda untuk siswa ini?')) {
        return;
    }
    
    fetch(`{{ route('tes-kompetensi.gradeJawabanPilgan', [$courseId, $modul->id_modul]) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            user_id: userId
        })
    })
    .then(response => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error(`Server returned non-JSON response: ${text.substring(0, 100)}...`);
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            showToast(data.message, 'success');
            loadTesKompetensiAnswers('pilgan');
        } else {
            showToast(data.message || 'Gagal melakukan penilaian otomatis', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat melakukan penilaian', 'error');
    });
}

function gradeAllAnswers() {
    if (!confirm('Nilai otomatis semua jawaban siswa?')) {
        return;
    }
    
    fetch(`{{ route('tes-kompetensi.gradeAllJawabanPilgan', [$courseId, $modul->id_modul]) }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error(`Server returned non-JSON response: ${text.substring(0, 100)}...`);
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showToast(data.message || 'Gagal melakukan penilaian otomatis', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat melakukan penilaian', 'error');
    });
}

function displayTesKompetensiAnswers(answers, type) {
    const modalContent = document.getElementById('modalContent');
    let html = '';
    
    if (answers.length === 0) {
        html = '<div class="text-center py-8">Belum ada jawaban dari siswa.</div>';
    } else {
        if (type === 'pilgan') {
            // Group answers by student
            const studentAnswers = {};
            answers.forEach(answer => {
                if (!studentAnswers[answer.id_user]) {
                    studentAnswers[answer.id_user] = {
                        nama_siswa: answer.nama_siswa,
                        answers: []
                    };
                }
                studentAnswers[answer.id_user].answers.push(answer);
            });
            
            Object.values(studentAnswers).forEach(student => {
                const totalSoal = student.answers.length;
                const benarCount = student.answers.filter(a => a.benar === 'Y').length;
                const salahCount = student.answers.filter(a => a.benar === 'N').length;
                const belumDinilai = student.answers.filter(a => a.benar === null).length;
                
                html += `
                    <div class="border rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="font-semibold">${student.nama_siswa}</h4>
                                <div class="text-sm text-gray-600 space-x-4">
                                    <span>Total: ${totalSoal}</span>
                                    <span class="text-green-600">Benar: ${benarCount}</span>
                                    <span class="text-red-600">Salah: ${salahCount}</span>
                                    <span class="text-yellow-600">Belum dinilai: ${belumDinilai}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                ${belumDinilai > 0 ? 
                                    `<button onclick="gradePilganAnswers(${student.answers[0].id_user})" 
                                            class="bg-edu-purple text-white px-4 py-2 rounded text-sm hover:bg-edu-purple/90">
                                        Nilai Otomatis
                                    </button>` :
                                    `<span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                        Skor: ${benarCount}/${totalSoal} (${Math.round((benarCount/totalSoal)*100)}%)
                                    </span>`
                                }
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-2 text-sm max-h-40 overflow-y-auto">
                            ${student.answers.map((answer, index) => `
                                <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                    <span>Soal ${index + 1}: ${answer.jawaban.toUpperCase()}</span>
                                    <span class="${answer.benar === 'Y' ? 'text-green-600' : answer.benar === 'N' ? 'text-red-600' : 'text-yellow-600'}">
                                        ${answer.benar === 'Y' ? '✓ Benar' : answer.benar === 'N' ? '✗ Salah' : 'Belum dinilai'}
                                    </span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });
        } else {
            // Essay answers - UPDATED SECTION
            answers.forEach(answer => {
                html += `
                    <div class="border rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-semibold">${answer.nama_siswa}</h4>
                                <p class="text-sm text-gray-500">Submit: ${formatDate(answer.created_at)}</p>
                            </div>
                            <div class="text-right">
                                ${answer.nilai !== null ? 
                                    `<div class="flex items-center space-x-2">
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                            Nilai: ${answer.nilai}/100
                                        </span>
                                        <button onclick="openGradeEssayModal(${answer.id_jawaban_essay}, '${answer.nama_siswa}', ${answer.nilai})" 
                                                class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">
                                            Edit Nilai
                                        </button>
                                    </div>` :
                                    `<button onclick="openGradeEssayModal(${answer.id_jawaban_essay}, '${answer.nama_siswa}', null)" 
                                            class="bg-edu-purple text-white px-4 py-2 rounded text-sm hover:bg-edu-purple/90">
                                        Beri Nilai
                                    </button>`
                                }
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            <h5 class="font-medium mb-2">Jawaban:</h5>
                            <div class="bg-gray-50 rounded p-3 text-sm max-h-60 overflow-y-auto whitespace-pre-wrap">
                                ${answer.jawaban}
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    }
    
    modalContent.innerHTML = html;
}

// Tambahkan fungsi baru untuk modal grading essay
function openGradeEssayModal(jawabanId, namaSiswa, currentNilai) {
    const modal = document.createElement('div');
    modal.id = 'gradeEssayModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-slate-900">Beri Nilai Essay</h3>
                    <button onclick="closeGradeEssayModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nama Siswa</label>
                    <p class="text-slate-900 font-medium">${namaSiswa}</p>
                </div>
                
                <div class="mb-6">
                    <label for="nilaiEssay" class="block text-sm font-medium text-slate-700 mb-2">Nilai (0-100)</label>
                    <input type="number" id="nilaiEssay" min="0" max="100" 
                           value="${currentNilai !== null ? currentNilai : ''}"
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                           placeholder="Masukkan nilai 0-100">
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="closeGradeEssayModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                        Batal
                    </button>
                    <button onclick="submitGradeEssay(${jawabanId})" 
                            class="flex-1 px-4 py-2 bg-edu-green text-white rounded-lg hover:bg-edu-green/90">
                        Simpan Nilai
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    // Focus pada input
    setTimeout(() => {
        document.getElementById('nilaiEssay').focus();
    }, 100);
}

function closeGradeEssayModal() {
    const modal = document.getElementById('gradeEssayModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto';
    }
}

function submitGradeEssay(jawabanId) {
    const nilai = document.getElementById('nilaiEssay').value;
    
    if (!nilai || nilai < 0 || nilai > 100) {
        showToast('Nilai harus antara 0-100', 'error');
        return;
    }
    
    fetch(`{{ route('tes-kompetensi.gradeJawabanEssay', [$courseId, $modul->id_modul]) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            jawaban_id: jawabanId,
            nilai: parseInt(nilai)
        })
    })
    .then(response => {
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return response.json();
        } else {
            return response.text().then(text => {
                throw new Error(`Server returned non-JSON response: ${text.substring(0, 100)}...`);
            });
        }
    })
    .then(data => {
        if (data && data.success) {
            showToast(data.message, 'success');
            closeGradeEssayModal();
            // Refresh tampilan jawaban
            loadTesKompetensiAnswers('essay');
        } else {
            showToast(data.message || 'Gagal menyimpan nilai', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menyimpan nilai', 'error');
    });
}

// Tambahkan event listener untuk close modal dengan ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeGradeEssayModal();
    }
});

function closeModal() {
    document.getElementById('studentAnswersModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modals when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    window.addEventListener('click', function(e) {
        const soalModal = document.getElementById('soalModal');
        const studentModal = document.getElementById('studentAnswersModal');
        
        if (soalModal && e.target === soalModal) {
            closeSoalModal();
        }
        if (studentModal && e.target === studentModal) {
            closeModal();
        }
    });
});

@else
// STUDENT FUNCTIONS
// No special JavaScript functions needed for students in this context
@endif

// Common functions
function formatDate(dateString) {
    return new Date(dateString).toLocaleString('id-ID');
}

function showToast(message, type = 'info') {
    // Simple toast implementation
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 transition-opacity duration-300 ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    toast.textContent = message;
    toast.style.opacity = '0';
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
    }, 10);
    
    // Animate out and remove
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast && toast.parentNode) {
                toast.remove();
            }
        }, 300);
    }, 3000);
}
</script>

<script>
// SIMPLIFIED APPROACH USING FORM SUBMISSION
function handleFormSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const soalId = document.getElementById('soal_id').value;
    const submitBtn = document.getElementById('submitBtn');
    
    // Show loading state
    submitBtn.textContent = 'Menyimpan...';
    submitBtn.disabled = true;
    
    // Set correct action and method
    if (soalId) {
        form.action = `{{ route('tes-kompetensi.updateSoalPilgan', [$courseId, $modul->id_modul, 'SOAL_ID']) }}`.replace('SOAL_ID', soalId);
        document.getElementById('form_method').value = 'PUT';
    } else {
        form.action = `{{ route('tes-kompetensi.storeSoalPilgan', [$courseId, $modul->id_modul]) }}`;
        document.getElementById('form_method').value = 'POST';
    }
    
    // Create hidden iframe for form submission
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.name = 'submitFrame';
    document.body.appendChild(iframe);
    
    form.target = 'submitFrame';
    
    // Handle iframe load (form submission complete)
    iframe.onload = function() {
        // Reset button
        submitBtn.textContent = 'Simpan Soal';
        submitBtn.disabled = false;
        
        // Show success message
        showToast(soalId ? 'Soal berhasil diupdate!' : 'Soal berhasil ditambahkan!', 'success');
        
        // Close modal and reload
        closeSoalModal();
        setTimeout(() => {
            location.reload();
        }, 1000);
        
        // Clean up iframe
        setTimeout(() => {
            if (iframe.parentNode) {
                iframe.parentNode.removeChild(iframe);
            }
        }, 2000);
    };
    
    // Submit form
    form.submit();
    
    return false;
}

// Alternative delete function using hidden form
function deleteSoal(soalId) {
    if (!confirm('Apakah Anda yakin ingin menghapus soal ini?')) {
        return;
    }
    
    // Create hidden form
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('tes-kompetensi.deleteSoalPilgan', [$courseId, $modul->id_modul, 'SOAL_ID']) }}`.replace('SOAL_ID', soalId);
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Add DELETE method
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    
    // Create iframe for submission
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.name = 'deleteFrame';
    document.body.appendChild(iframe);
    
    form.target = 'deleteFrame';
    
    iframe.onload = function() {
        showToast('Soal berhasil dihapus!', 'success');
        setTimeout(() => {
            location.reload();
        }, 1000);
        
        // Clean up
        setTimeout(() => {
            if (form.parentNode) form.parentNode.removeChild(form);
            if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
        }, 2000);
    };
    
    form.submit();
}
</script>
@endsection