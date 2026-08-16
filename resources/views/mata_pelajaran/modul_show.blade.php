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
        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h1 class="text-3xl font-bold text-slate-900 mb-2">{{ $modul->nama_modul }}</h1>
                <p class="text-slate-600">{{ $modul->desk ?? 'Tidak ada deskripsi tersedia' }}</p>
                
                @if(!$canManage && $checklist)
                <!-- Progress Bar for Students -->
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-slate-700">Progress Pembelajaran</span>
                        <span class="text-sm text-slate-500" id="progressText">
                            {{ ($checklist->pengalaman_belajar == 'Y' ? 1 : 0) + 
                               ($checklist->materi == 'Y' ? 1 : 0) + 
                               ($checklist->koneksi_materi == 'Y' ? 1 : 0) + 
                               ($checklist->aksi_nyata == 'Y' ? 1 : 0) }}/4 Selesai
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-edu-blue to-edu-green h-2 rounded-full" 
                             style="width: {{ (($checklist->pengalaman_belajar == 'Y' ? 1 : 0) + 
                                              ($checklist->materi == 'Y' ? 1 : 0) + 
                                              ($checklist->koneksi_materi == 'Y' ? 1 : 0) + 
                                              ($checklist->aksi_nyata == 'Y' ? 1 : 0)) * 25 }}%"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Section 1: Pengalaman Belajar -->
        <div class="section-card {{ !$canManage && $checklist && $checklist->pengalaman_belajar == 'Y' ? 'completed-section' : '' }}">
            <div class="section-header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">1</span>
                        </div>
                        <h2 class="text-xl font-semibold">Pengalaman Belajar</h2>
                    </div>
                    @if(!$canManage && $checklist && $checklist->pengalaman_belajar == 'Y')
                        <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">✓ Selesai</span>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if($canManage)
                    <!-- Teacher View -->
                    <form action="{{ route('modul.updatePengalamanBelajar', [$courseId, $modul->id_modul]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <div id="pengalamanBelajarEditor" style="height: 300px;"></div>
                            <textarea name="pengalaman_belajar" id="pengalamanBelajarContent" class="hidden">{{ $modul->pengalaman_belajar ?? '' }}</textarea>
                        </div>
                        <div class="text-right">
                            <button type="submit" class="bg-edu-blue text-white px-6 py-2 rounded-lg hover:bg-edu-blue/90">
                                Update Pengalaman Belajar
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Student View -->
                    <div class="prose max-w-none">
                        @if($modul->pengalaman_belajar)
                            {!! $modul->pengalaman_belajar !!}
                        @else
                            <p class="text-slate-500 italic">Pengalaman belajar belum tersedia.</p>
                        @endif
                    </div>
                    
                    @if($checklist && $checklist->pengalaman_belajar == 'N')
                        <div class="mt-6 text-center">
                            <form action="{{ route('modul.completePengalamanBelajar', [$courseId, $modul->id_modul]) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                    Tandai Sebagai Selesai
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Section 2: Daftar Materi -->
        <div class="section-card {{ !$canManage && $checklist && $checklist->materi == 'Y' ? 'completed-section' : (!$canManage && $checklist && $checklist->pengalaman_belajar == 'N' ? 'locked-section' : '') }}">
            <div class="section-header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">2</span>
                        </div>
                        <h2 class="text-xl font-semibold">Daftar Materi</h2>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($canManage)
                            <button onclick="openAddMaterialModal()" class="bg-edu-green text-white px-4 py-2 rounded-lg hover:bg-edu-green/90 text-sm">
                                + Tambah Materi
                            </button>
                        @endif
                        @if(!$canManage && $checklist && $checklist->materi == 'Y')
                            <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">✓ Selesai</span>
                        @elseif(!$canManage && $checklist && $checklist->pengalaman_belajar == 'N')
                            <span class="bg-red-500 bg-opacity-20 px-3 py-1 rounded-full text-sm">🔒 Terkunci</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if(!$canManage && $checklist && $checklist->pengalaman_belajar == 'N')
                    <div class="text-center py-8">
                        <p class="text-slate-500">Selesaikan Pengalaman Belajar terlebih dahulu.</p>
                    </div>
                @else
                    @if($materials->count() > 0)
                        <div class="space-y-4">
                            @foreach($materials as $material)
                                <div class="border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-slate-900 mb-1">{{ $material->nama_materi }}</h4>
                                            <p class="text-sm text-slate-500">Materi pembelajaran dengan 6 komponen</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if($canManage)
                                                <button onclick="openEditMaterialModal({{ $material->id_materi }}, '{{ $material->nama_materi }}')" 
                                                        class="bg-yellow-500 text-white px-3 py-1 rounded text-sm hover:bg-yellow-600">
                                                    Edit
                                                </button>
                                                <button onclick="deleteMaterial({{ $material->id_materi }}, '{{ $material->nama_materi }}')" 
                                                        class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                                    Hapus
                                                </button>
                                                <a href="{{ route('materi.show', [$courseId, $material->id_materi]) }}" 
                                                   class="bg-edu-blue text-white px-4 py-2 rounded-lg hover:bg-edu-blue/90 transition-colors text-sm">
                                                    Kelola Materi
                                                </a>
                                            @else
                                                <a href="{{ route('materi.show', [$courseId, $material->id_materi]) }}" 
                                                   class="bg-edu-blue text-white px-4 py-2 rounded-lg hover:bg-edu-blue/90 transition-colors text-sm">
                                                    Buka Materi
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if(!$canManage && $checklist && $checklist->materi == 'N')
                            <div class="mt-6 text-center">
                                <form action="{{ route('modul.completeMaterial', [$courseId, $modul->id_modul]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                        Tandai Materi Sebagai Selesai
                                    </button>
                                </form>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <p class="text-slate-500">
                                @if($canManage)
                                    Belum ada materi. Klik tombol "Tambah Materi" untuk menambah materi baru.
                                @else
                                    Materi belum tersedia.
                                @endif
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Section 3: Koneksi Materi -->
        <div class="section-card {{ !$canManage && $checklist && $checklist->koneksi_materi == 'Y' ? 'completed-section' : (!$canManage && $checklist && $checklist->materi == 'N' ? 'locked-section' : '') }}">
            <div class="section-header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">3</span>
                        </div>
                        <h2 class="text-xl font-semibold">Koneksi Materi</h2>
                    </div>
                    @if(!$canManage && $checklist && $checklist->koneksi_materi == 'Y')
                        <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">✓ Selesai</span>
                    @elseif(!$canManage && $checklist && $checklist->materi == 'N')
                        <span class="bg-red-500 bg-opacity-20 px-3 py-1 rounded-full text-sm">🔒 Terkunci</span>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if(!$canManage && $checklist && $checklist->materi == 'N')
                    <div class="text-center py-8">
                        <p class="text-slate-500">Selesaikan materi terlebih dahulu.</p>
                    </div>
                @else
                    @if($canManage)
                        <!-- Teacher View -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium mb-4">Upload Soal Koneksi Materi</h3>
                            <form action="{{ route('modul.uploadKoneksiMateriSoal', [$courseId, $modul->id_modul]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="border-2 border-dashed border-slate-300 rounded-lg p-6">
                                    <input type="file" name="file_soal" accept=".pdf,.doc,.docx" class="w-full">
                                    <p class="text-sm text-slate-500 mt-2">PDF, DOC, DOCX maksimal 10MB</p>
                                </div>
                                <div class="mt-4 text-right">
                                    <button type="submit" class="bg-edu-blue text-white px-6 py-2 rounded-lg hover:bg-edu-blue/90">
                                        Upload Soal
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        @if($modul->koneksi_materi)
                            <div class="bg-slate-50 rounded-lg p-4 mb-6">
                                <h4 class="font-medium mb-2">File Soal:</h4>
                                <a href="{{ asset('storage/' . $modul->koneksi_materi) }}" target="_blank" class="text-edu-blue">
                                    {{ basename($modul->koneksi_materi) }}
                                </a>
                            </div>
                            
                            <!-- Tombol Lihat Jawaban Siswa -->
                            <div class="border-t pt-6">
                                <button onclick="loadStudentAnswers('koneksi_materi')" class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90">
                                    Lihat & Nilai Jawaban Siswa
                                </button>
                                <div id="koneksiMateriAnswers" class="mt-4"></div>
                            </div>
                        @endif
                    @else
                        <!-- Student View -->
                        @if($modul->koneksi_materi)
                            <div class="mb-6">
                                <h3 class="text-lg font-medium mb-4">Download Soal</h3>
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <a href="{{ asset('storage/' . $modul->koneksi_materi) }}" target="_blank" 
                                       class="bg-edu-blue text-white px-4 py-2 rounded-lg hover:bg-edu-blue/90">
                                        Download Soal
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Upload Jawaban -->
                            @if(!$koneksiMateriFile || $koneksiMateriFile->benar === null)
                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-medium mb-4">Upload Jawaban</h3>
                                    <form action="{{ route('modul.uploadKoneksiMateriJawaban', [$courseId, $modul->id_modul]) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="border-2 border-dashed border-slate-300 rounded-lg p-6">
                                            <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx" class="w-full">
                                        </div>
                                        <div class="mt-4 text-right">
                                            <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                                {{ $koneksiMateriFile ? 'Update Jawaban' : 'Upload Jawaban' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="bg-slate-100 rounded-lg p-6 text-center">
                                    <p class="font-medium mb-2">Jawaban sudah dinilai</p>
                                    @if($koneksiMateriFile->benar === 'Y')
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">✓ Benar</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full">✗ Salah</span>
                                    @endif
                                </div>
                            @endif
                            
                            @if($koneksiMateriFile)
                                <div class="mt-4 bg-green-50 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-800">Jawaban telah diupload</span>
                                        @if($koneksiMateriFile->benar === 'Y')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">✓ Benar</span>
                                        @elseif($koneksiMateriFile->benar === 'N')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">✗ Salah</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Belum dinilai</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-slate-500">Soal belum tersedia.</p>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        <!-- Section 4: Aksi Nyata -->
        <div class="section-card {{ !$canManage && $checklist && $checklist->aksi_nyata == 'Y' ? 'completed-section' : (!$canManage && $checklist && $checklist->koneksi_materi == 'N' ? 'locked-section' : '') }}">
            <div class="section-header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">4</span>
                        </div>
                        <h2 class="text-xl font-semibold">Aksi Nyata</h2>
                    </div>
                    @if(!$canManage && $checklist && $checklist->aksi_nyata == 'Y')
                        <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">✓ Selesai</span>
                    @elseif(!$canManage && $checklist && $checklist->koneksi_materi == 'N')
                        <span class="bg-red-500 bg-opacity-20 px-3 py-1 rounded-full text-sm">🔒 Terkunci</span>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if(!$canManage && $checklist && $checklist->koneksi_materi == 'N')
                    <div class="text-center py-8">
                        <p class="text-slate-500">Selesaikan koneksi materi terlebih dahulu.</p>
                    </div>
                @else
                    @if($canManage)
                        <!-- Teacher View -->
                        <form action="{{ route('modul.updateAksiNyataSoal', [$courseId, $modul->id_modul]) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <div id="aksiNyataSoalEditor" style="height: 300px;"></div>
                                <textarea name="aksi_nyata" id="aksiNyataSoalContent" class="hidden">{{ $modul->aksi_nyata ?? '' }}</textarea>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-edu-blue text-white px-6 py-2 rounded-lg hover:bg-edu-blue/90">
                                    Update Soal Aksi Nyata
                                </button>
                            </div>
                        </form>
                        
                        @if($modul->aksi_nyata)
                            <div class="border-t pt-6 mt-6">
                                <button onclick="loadStudentAnswers('aksi_nyata')" class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90">
                                    Lihat & Nilai Jawaban Siswa
                                </button>
                                <div id="aksiNyataAnswers" class="mt-4"></div>
                            </div>
                        @endif
                    @else
                        <!-- Student View -->
                        @if($modul->aksi_nyata)
                            <div class="mb-6">
                                <h3 class="text-lg font-medium mb-4">Soal Aksi Nyata</h3>
                                <div class="bg-slate-50 rounded-lg p-6 prose max-w-none">
                                    {!! $modul->aksi_nyata !!}
                                </div>
                            </div>
                            
                            @if(!$aksiNyataAnswer || $aksiNyataAnswer->benar === null)
                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-medium mb-4">Jawaban Anda</h3>
                                    <form action="{{ route('modul.submitAksiNyataJawaban', [$courseId, $modul->id_modul]) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <div id="aksiNyataJawabanEditor" style="height: 300px;"></div>
                                            <textarea name="jawaban" id="aksiNyataJawabanContent" class="hidden">{{ $aksiNyataAnswer->jawaban ?? '' }}</textarea>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                                                {{ $aksiNyataAnswer ? 'Update Jawaban' : 'Simpan Jawaban' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="bg-slate-100 rounded-lg p-6 text-center">
                                    <p class="font-medium mb-2">Jawaban sudah dinilai</p>
                                    @if($aksiNyataAnswer->benar === 'Y')
                                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">✓ Benar</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full">✗ Salah</span>
                                    @endif
                                    
                                    <div class="mt-4 bg-white rounded-lg p-4 text-left">
                                        <h4 class="font-medium mb-2">Jawaban Anda:</h4>
                                        <div class="prose max-w-none">
                                            {!! $aksiNyataAnswer->jawaban !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($aksiNyataAnswer)
                                <div class="mt-4 bg-green-50 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-800">Jawaban telah disimpan</span>
                                        @if($aksiNyataAnswer->benar === 'Y')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">✓ Benar</span>
                                        @elseif($aksiNyataAnswer->benar === 'N')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">✗ Salah</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Belum dinilai</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <p class="text-slate-500">Soal belum tersedia.</p>
                        @endif
                    @endif
                @endif
            </div>
        </div>

        <!-- Section 5: Tes Kompetensi -->
        <div class="section-card {{ !$canManage && $checklist && $checklist->aksi_nyata == 'N' ? 'locked-section' : '' }}">
            <div class="section-header">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-bold">5</span>
                        </div>
                        <h2 class="text-xl font-semibold">Tes Kompetensi</h2>
                    </div>
                    @if(!$canManage && $checklist && $checklist->aksi_nyata == 'N')
                        <span class="bg-red-500 bg-opacity-20 px-3 py-1 rounded-full text-sm">🔒 Terkunci</span>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if(!$canManage && $checklist && $checklist->aksi_nyata == 'N')
                    <div class="text-center py-8">
                        <p class="text-slate-500">Selesaikan aksi nyata terlebih dahulu.</p>
                    </div>
                @else
                    @if($canManage)
                        <!-- Teacher View -->
                        <div class="text-center py-8">
                            <h3 class="text-lg font-medium mb-4">Kelola Tes Kompetensi</h3>
                            <p class="text-slate-600 mb-6">Buat dan kelola soal tes kompetensi untuk modul ini</p>
                            
                            <div class="flex justify-center space-x-4 mb-6">
                                <button onclick="openJenisTesModal()" 
                                        class="bg-edu-green text-white px-6 py-3 rounded-lg hover:bg-edu-green/90 inline-flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                                    </svg>
                                    <span>Atur Jenis Tes</span>
                                </button>
                                
                                <a href="{{ route('tes-kompetensi.index', [$courseId, $modul->id_modul]) }}" 
                                class="bg-edu-blue text-white px-6 py-3 rounded-lg hover:bg-edu-blue/90 inline-flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Kelola Soal Tes</span>
                                </a>
                            </div>
                            
                            @if($jenisTestKompetensi)
                                <div class="bg-slate-50 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-slate-600">Jenis tes saat ini:</p>
                                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium
                                                {{ $jenisTestKompetensi->essay == 'Y' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $jenisTestKompetensi->essay == 'Y' ? 'Essay' : 'Pilihan Ganda' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Student View -->
                        <div class="text-center py-8">
                            <h3 class="text-lg font-medium mb-4">Tes Kompetensi</h3>
                            <p class="text-slate-600 mb-6">Kerjakan tes kompetensi untuk menguji pemahaman Anda</p>
                            
                            @if($jenisTestKompetensi)
                                <div class="mb-6">
                                    <span class="inline-block px-4 py-2 rounded-full text-sm font-medium
                                                {{ $jenisTestKompetensi->essay == 'Y' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        Jenis Tes: {{ $jenisTestKompetensi->essay == 'Y' ? 'Essay' : 'Pilihan Ganda' }}
                                    </span>
                                </div>
                                
                                <a href="{{ route('tes-kompetensi.index', [$courseId, $modul->id_modul]) }}" 
                                class="bg-edu-blue text-white px-8 py-3 rounded-lg hover:bg-edu-blue/90 inline-flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Mulai Tes Kompetensi</span>
                                </a>
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-yellow-800">Tes kompetensi belum tersedia. Guru belum mengatur jenis tes.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>


        <!-- Modal Tambah/Edit Soal Pilihan Ganda -->
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
                <form id="soalForm" onsubmit="submitSoal(event)">
                    <div class="px-6 py-6 max-h-[calc(90vh-160px)] overflow-y-auto">
                        <input type="hidden" id="soal_id" name="soal_id">
                        
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
                        <button type="submit" 
                                class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                            Simpan Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Lihat Jawaban Siswa -->
<div id="studentAnswersModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
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


<!-- Modal Tambah Materi -->
<div id="addMaterialModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900">Tambah Materi Baru</h3>
                <button onclick="closeAddMaterialModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="addMaterialForm" onsubmit="submitAddMaterial(event)">
            <div class="px-6 py-6">
                <div class="mb-4">
                    <label for="add_nama_materi" class="block text-sm font-medium text-slate-700 mb-2">Nama Materi</label>
                    <input type="text" id="add_nama_materi" name="nama_materi" 
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent" 
                           placeholder="Masukkan nama materi..." required>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end space-x-3">
                <button type="button" onclick="closeAddMaterialModal()" 
                        class="px-4 py-2 text-slate-600 hover:text-slate-800">Batal</button>
                <button type="submit" 
                        class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                    Tambah Materi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Materi -->
<div id="editMaterialModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900">Edit Materi</h3>
                <button onclick="closeEditMaterialModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="editMaterialForm" onsubmit="submitEditMaterial(event)">
            <div class="px-6 py-6">
                <div class="mb-4">
                    <label for="edit_nama_materi" class="block text-sm font-medium text-slate-700 mb-2">Nama Materi</label>
                    <input type="text" id="edit_nama_materi" name="nama_materi" 
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent" 
                           placeholder="Masukkan nama materi..." required>
                    <input type="hidden" id="edit_material_id" name="material_id">
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end space-x-3">
                <button type="button" onclick="closeEditMaterialModal()" 
                        class="px-4 py-2 text-slate-600 hover:text-slate-800">Batal</button>
                <button type="submit" 
                        class="bg-edu-blue text-white px-6 py-2 rounded-lg hover:bg-edu-blue/90">
                    Update Materi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah/Edit Soal Pilihan Ganda -->
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
        <form id="soalForm" onsubmit="submitSoal(event)">
            <div class="px-6 py-6 max-h-[calc(90vh-160px)] overflow-y-auto">
                <input type="hidden" id="soal_id" name="soal_id">
                
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
                <button type="submit" 
                        class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                    Simpan Soal
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Pilih Jenis Tes Kompetensi (Teacher Only) -->
<div id="jenisTesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900">Pilih Jenis Tes Kompetensi</h3>
                <button onclick="closeJenisTesModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <form id="jenisTesForm" onsubmit="submitJenisTes(event)">
            <div class="px-6 py-6">
                <p class="text-slate-600 mb-6">Pilih jenis tes kompetensi yang akan digunakan untuk modul ini:</p>
                
                <div class="space-y-4">
                    <label class="flex items-start space-x-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="jenis_tes" value="pilihan_ganda" 
                               class="mt-1 text-edu-blue focus:ring-edu-blue" 
                               {{ !$jenisTestKompetensi || $jenisTestKompetensi->essay == 'N' ? 'checked' : '' }}>
                        <div>
                            <div class="font-medium text-slate-900">Pilihan Ganda</div>
                            <div class="text-sm text-slate-600">Tes dengan multiple choice (A, B, C, D)</div>
                        </div>
                    </label>
                    
                    <label class="flex items-start space-x-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="jenis_tes" value="essay" 
                               class="mt-1 text-edu-blue focus:ring-edu-blue"
                               {{ $jenisTestKompetensi && $jenisTestKompetensi->essay == 'Y' ? 'checked' : '' }}>
                        <div>
                            <div class="font-medium text-slate-900">Essay</div>
                            <div class="text-sm text-slate-600">Tes dengan jawaban berupa uraian/essay</div>
                        </div>
                    </label>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 rounded-b-2xl flex justify-end space-x-3">
                <button type="button" onclick="closeJenisTesModal()" 
                        class="px-4 py-2 text-slate-600 hover:text-slate-800">Batal</button>
                <button type="submit" 
                        class="bg-edu-green text-white px-6 py-2 rounded-lg hover:bg-edu-green/90">
                    Simpan Jenis Tes
                </button>
            </div>
        </form>
    </div>
</div>



@endsection

@section('js_tambahan')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    let pengalamanBelajarQuill, aksiNyataSoalQuill, aksiNyataJawabanQuill;
    
    document.addEventListener('DOMContentLoaded', function() {
        @if($canManage)
            // Teacher editors
            if (document.getElementById('pengalamanBelajarEditor')) {
                pengalamanBelajarQuill = new Quill('#pengalamanBelajarEditor', {
                    theme: 'snow'
                });
                
                const pengalamanContent = document.getElementById('pengalamanBelajarContent').value;
                if (pengalamanContent) {
                    pengalamanBelajarQuill.root.innerHTML = pengalamanContent;
                }
                
                pengalamanBelajarQuill.on('text-change', function() {
                    document.getElementById('pengalamanBelajarContent').value = pengalamanBelajarQuill.root.innerHTML;
                });
            }
            
            if (document.getElementById('aksiNyataSoalEditor')) {
                aksiNyataSoalQuill = new Quill('#aksiNyataSoalEditor', {
                    theme: 'snow'
                });
                
                const aksiNyataContent = document.getElementById('aksiNyataSoalContent').value;
                if (aksiNyataContent) {
                    aksiNyataSoalQuill.root.innerHTML = aksiNyataContent;
                }
                
                aksiNyataSoalQuill.on('text-change', function() {
                    document.getElementById('aksiNyataSoalContent').value = aksiNyataSoalQuill.root.innerHTML;
                });
            }
        @else
            // Student editor
            if (document.getElementById('aksiNyataJawabanEditor')) {
                aksiNyataJawabanQuill = new Quill('#aksiNyataJawabanEditor', {
                    theme: 'snow'
                });
                
                const jawabanContent = document.getElementById('aksiNyataJawabanContent').value;
                if (jawabanContent) {
                    aksiNyataJawabanQuill.root.innerHTML = jawabanContent;
                }
                
                aksiNyataJawabanQuill.on('text-change', function() {
                    document.getElementById('aksiNyataJawabanContent').value = aksiNyataJawabanQuill.root.innerHTML;
                });
            }
        @endif
    });
    
    function loadStudentAnswers(type) {
        const modal = document.getElementById('studentAnswersModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        
        modalTitle.textContent = type === 'koneksi_materi' ? 'Jawaban Koneksi Materi' : 'Jawaban Aksi Nyata';
        modalContent.innerHTML = '<div class="text-center py-8">Loading...</div>';
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        fetch(`/courses/{{ $courseId }}/modules/{{ $modul->id_modul }}/answers/${type}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayAnswers(data.answers, type);
                } else {
                    modalContent.innerHTML = '<div class="text-center py-8">Tidak ada jawaban.</div>';
                }
            })
            .catch(error => {
                modalContent.innerHTML = '<div class="text-center py-8 text-red-500">Error loading data.</div>';
            });
    }
    
    function displayAnswers(answers, type) {
        const modalContent = document.getElementById('modalContent');
        let html = '';
        
        if (answers.length === 0) {
            html = '<div class="text-center py-8">Belum ada jawaban dari siswa.</div>';
        } else {
            answers.forEach(answer => {
                const statusBadge = answer.benar === null ? 
                    '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Belum dinilai</span>' :
                    answer.benar === 'Y' ? 
                    '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">✓ Benar</span>' :
                    '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">✗ Salah</span>';
                
                const canGrade = answer.benar === null;
                
                html += `
                    <div class="border rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-semibold">${answer.nama_siswa}</h4>
                                <p class="text-sm text-gray-500">Submit: ${formatDate(answer.created_at)}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                ${statusBadge}
                                ${canGrade ? `
                                    <button onclick="gradeAnswer(${answer.id}, 'Y', '${type}')" 
                                            class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                        Benar
                                    </button>
                                    <button onclick="gradeAnswer(${answer.id}, 'N', '${type}')" 
                                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                        Salah
                                    </button>
                                ` : `
                                    <button onclick="changeGrade(${answer.id}, '${answer.benar}', '${type}')" 
                                            class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
                                        Ubah
                                    </button>
                                `}
                            </div>
                        </div>
                        
                        <div class="border-t pt-4">
                            ${type === 'koneksi_materi' ? 
                                `<a href="${getFileUrl(answer.jawaban)}" target="_blank" class="text-blue-600 hover:underline">
                                    📄 ${getFileName(answer.jawaban)}
                                </a>` :
                                `<div class="bg-gray-50 rounded p-3">
                                    ${answer.jawaban}
                                </div>`
                            }
                        </div>
                    </div>
                `;
            });
        }
        
        modalContent.innerHTML = html;
    }
    
    function gradeAnswer(answerId, grade, type) {
        if (!confirm(`Nilai jawaban sebagai ${grade === 'Y' ? 'BENAR' : 'SALAH'}?`)) {
            return;
        }
        
        fetch(`/courses/{{ $courseId }}/modules/{{ $modul->id_modul }}/grade-answer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                answer_id: answerId,
                grade: grade,
                type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast({
                    success: true,
                    message: 'Penilaian berhasil disimpan'
                });
                loadStudentAnswers(type);
            } else {
                showToast({
                    success: false,
                    message: 'Gagal menyimpan penilaian'
                });
            }
        });
    }
    
    function changeGrade(answerId, currentGrade, type) {
        const newGrade = currentGrade === 'Y' ? 'N' : 'Y';
        gradeAnswer(answerId, newGrade, type);
    }
    
    function closeModal() {
        document.getElementById('studentAnswersModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function formatDate(dateString) {
        return new Date(dateString).toLocaleString('id-ID');
    }
    
    function getFileUrl(filePath) {
        return `{{ asset('storage/') }}/${filePath}`;
    }
    
    function getFileName(filePath) {
        return filePath.split('/').pop();
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('studentAnswersModal');
        if (e.target === modal) {
            closeModal();
        }
    });





    // materi modal
    // Functions for Add Material Modal
    function openAddMaterialModal() {
        document.getElementById('addMaterialModal').classList.remove('hidden');
        document.getElementById('add_nama_materi').value = '';
        document.body.style.overflow = 'hidden';
    }
    
    function closeAddMaterialModal() {
        document.getElementById('addMaterialModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function submitAddMaterial(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        
        fetch('{{ route("materi.store", [$courseId, $modul->id_modul]) }}', {
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
                showToast({
                    success: true,
                    message: data.message
                });
                closeAddMaterialModal();
                location.reload(); // Reload to show new material
            } else {
                showToast({
                    success: false,
                    message: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            showToast({
                success: false,
                message: 'Terjadi kesalahan saat menambah materi'
            });
        });
    }
    
    // Functions for Edit Material Modal
    function openEditMaterialModal(materialId, currentName) {
        document.getElementById('editMaterialModal').classList.remove('hidden');
        document.getElementById('edit_material_id').value = materialId;
        document.getElementById('edit_nama_materi').value = currentName;
        document.body.style.overflow = 'hidden';
    }
    
    function closeEditMaterialModal() {
        document.getElementById('editMaterialModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function submitEditMaterial(event) {
        event.preventDefault();
        
        const materialId = document.getElementById('edit_material_id').value;
        const formData = new FormData(event.target);
        
        fetch(`/courses/{{ $courseId }}/materials/${materialId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nama_materi: formData.get('nama_materi')
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast({
                    success: true,
                    message: data.message
                });
                closeEditMaterialModal();
                location.reload(); // Reload to show updated material
            } else {
                showToast({
                    success: false,
                    message: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            showToast({
                success: false,
                message: 'Terjadi kesalahan saat mengupdate materi'
            });
        });
    }
    
    // Function for Delete Material
    function deleteMaterial(materialId, materialName) {
        if (!confirm(`Apakah Anda yakin ingin menghapus materi "${materialName}"?`)) {
            return;
        }
        
        fetch(`/courses/{{ $courseId }}/materials/${materialId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast({
                    success: true,
                    message: data.message
                });
                location.reload(); // Reload to remove deleted material
            } else {
                showToast({
                    success: false,
                    message: data.message || 'Terjadi kesalahan'
                });
            }
        })
        .catch(error => {
            showToast({
                success: false,
                message: 'Terjadi kesalahan saat menghapus materi'
            });
        });
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        const addModal = document.getElementById('addMaterialModal');
        const editModal = document.getElementById('editMaterialModal');
        
        if (e.target === addModal) {
            closeAddMaterialModal();
        }
        if (e.target === editModal) {
            closeEditMaterialModal();
        }
    });
</script>


<script>
// Functions for Jenis Tes Modal (Teacher Only)
function openJenisTesModal() {
    document.getElementById('jenisTesModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeJenisTesModal() {
    document.getElementById('jenisTesModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function submitJenisTes(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const jenisTesPilihan = formData.get('jenis_tes');
    const isEssay = jenisTesPilihan === 'essay' ? 'Y' : 'N';
    
    // Buat endpoint baru di ModulController atau gunakan form submit langsung
    fetch(`/courses/{{ $courseId }}/modules/{{ $modul->id_modul }}/set-jenis-tes`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            essay: isEssay
        })
    })
    .then(response => {
        if (!response.ok) {
            // Jika endpoint tidak ada, gunakan cara alternatif dengan form submission
            submitJenisTesDenganForm(isEssay);
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            showToast({
                success: true,
                message: `Jenis tes berhasil diatur sebagai ${jenisTesPilihan === 'essay' ? 'Essay' : 'Pilihan Ganda'}`
            });
            closeJenisTesModal();
            location.reload();
        }
    })
    .catch(error => {
        // Fallback: gunakan form submission langsung
        submitJenisTesDenganForm(isEssay);
    });
}

// Fungsi fallback menggunakan form submission
function submitJenisTesDenganForm(isEssay) {
    // Buat form tersembunyi untuk submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `{{ route('tes-kompetensi.index', [$courseId, $modul->id_modul]) }}`;
    form.style.display = 'none';
    
    // Tambahkan CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Tambahkan data essay
    const essayInput = document.createElement('input');
    essayInput.type = 'hidden';
    essayInput.name = 'essay';
    essayInput.value = isEssay;
    form.appendChild(essayInput);
    
    // Tambahkan action identifier
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'set_jenis_tes';
    form.appendChild(actionInput);
    
    document.body.appendChild(form);
    form.submit();
}

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const jenisTesModal = document.getElementById('jenisTesModal');
    
    if (e.target === jenisTesModal) {
        closeJenisTesModal();
    }
});
</script>
@endsection