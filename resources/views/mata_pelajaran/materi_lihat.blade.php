@extends('layouts.home')

@section('css_tambahan')
<style>
    .component-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background: white;
    }
    
    .component-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .component-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
    }
    
    .grade-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .grade-excellent { background: #dcfce7; color: #166534; }
    .grade-good { background: #dbeafe; color: #1e40af; }
    .grade-fair { background: #fef3c7; color: #92400e; }
    .grade-poor { background: #fee2e2; color: #dc2626; }
    .grade-pending { background: #f1f5f9; color: #64748b; }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-secondary {
        background: #64748b;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-export {
        background: #059669;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    
    .btn-export:hover {
        background: #047857;
    }
    
    .btn-export:disabled {
        background: #6ee7b7;
        cursor: not-allowed;
    }
    
    .btn-export svg {
        width: 16px;
        height: 16px;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal.show {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        max-width: 900px;
        max-height: 85vh;
        overflow-y: auto;
        width: 90%;
    }
    
    .student-answer {
        border: 1px solid #e5e7eb;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
    }
    
    .student-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .answer-content {
        background: #f8fafc;
        padding: 10px;
        border-radius: 4px;
        margin-top: 10px;
    }
    
    .grade-input {
        width: 100px;
        padding: 5px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
    }
    
    .nav-tabs {
        display: flex;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 20px;
        gap: 4px;
    }
    
    .nav-tab {
        padding: 12px 24px;
        cursor: pointer;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s;
    }
    
    .nav-tab:hover {
        color: #374151;
    }
    
    .nav-tab.active {
        border-bottom-color: #3b82f6;
        color: #3b82f6;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }

    .metakognisi-container {
        background: white;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 24px;
        margin-bottom: 16px;
    }

    .metakognisi-header {
        margin-bottom: 24px;
    }

    .metakognisi-tabs {
        display: flex;
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 24px;
        gap: 4px;
    }

    .metakognisi-tab {
        padding: 12px 24px;
        cursor: pointer;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        font-size: 14px;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s;
    }

    .metakognisi-tab:hover {
        color: #374151;
    }

    .metakognisi-tab.active {
        border-bottom-color: #3b82f6;
        color: #3b82f6;
    }

    .metakognisi-panel {
        display: none;
    }

    .metakognisi-panel.active {
        display: block;
    }

    .answer-box {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        margin-bottom: 16px;
    }

    .grade-section {
        display: flex;
        align-items: end;
        gap: 12px;
        padding-top: 8px;
    }

    .grade-section input {
        flex: 1;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .grade-section input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .grade-section button {
        padding: 10px 24px;
        font-weight: 500;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>
@endsection

@section('konten')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ $material->nama_materi }}</h1>
            <p class="text-gray-600 mt-2">Modul: {{ $material->nama_modul }}</p>
            
            @if($isStudent && isset($checklist))
            <div class="mt-4">
                <div class="text-sm text-gray-600">Progress Pembelajaran</div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                    @php
                        $completed = 0;
                        if($checklist->mulai_dari_diri == 'Y') $completed++;
                        if($checklist->eksplorasi_konsep == 'Y') $completed++;
                        if($checklist->ruang_kolaborasi == 'Y') $completed++;
                        if($checklist->demonstrasi_konseptual == 'Y') $completed++;
                        if($checklist->elaborasi_pemahaman == 'Y') $completed++;
                        $percentage = ($completed / 5) * 100;
                    @endphp
                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
                <div class="text-sm text-gray-600 mt-1">{{ $completed }}/5 Komponen Selesai</div>
            </div>
            @endif
        </div>

        @if($isTeacher && isset($dashboard))
        @php $d = $dashboard; @endphp
        @php
            $compLabels = [
                'mulai_dari_diri' => 'MDD', 'eksplorasi_konsep' => 'EK', 'ruang_kolaborasi' => 'RK',
                'demonstrasi_konseptual' => 'DK', 'elaborasi_pemahaman' => 'EP',
            ];
        @endphp
        <!-- Dashboard Analisis (Guru) -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">Dashboard Analisis Jawaban &amp; Progres Siswa</h2>
                <button type="button" onclick="toggleDashboard()" id="dashToggleBtn"
                        class="text-sm text-blue-600 hover:underline">Sembunyikan</button>
            </div>

            <div id="dashBody">
                <!-- Kartu ringkasan -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                    <div class="rounded-lg border border-gray-200 p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $d['total_siswa'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Total Siswa</div>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-center">
                        <div class="text-2xl font-bold text-blue-700">{{ $d['avg_progress'] }}%</div>
                        <div class="text-xs text-blue-600 mt-1">Rata-rata Progres</div>
                    </div>
                    <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-center">
                        <div class="text-2xl font-bold text-green-700">{{ $d['fully_done'] }}</div>
                        <div class="text-xs text-green-600 mt-1">Tuntas 5/5</div>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3 text-center">
                        <div class="text-2xl font-bold text-gray-700">{{ $d['not_started'] }}</div>
                        <div class="text-xs text-gray-500 mt-1">Belum Mulai</div>
                    </div>
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-center">
                        <div class="text-2xl font-bold text-amber-700">{{ $d['perlu_dinilai'] }}</div>
                        <div class="text-xs text-amber-600 mt-1">Jawaban Perlu Dinilai</div>
                    </div>
                </div>

                <!-- Ringkasan per komponen -->
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Ringkasan per Komponen</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-sm border border-gray-200">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="text-left px-3 py-2 border-b">Komponen</th>
                                <th class="text-center px-3 py-2 border-b">Sudah Jawab</th>
                                <th class="text-center px-3 py-2 border-b">Belum Jawab</th>
                                <th class="text-center px-3 py-2 border-b">Sudah Dinilai</th>
                                <th class="text-center px-3 py-2 border-b">Belum Dinilai</th>
                                <th class="text-center px-3 py-2 border-b">Rata-rata Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($d['components'] as $c)
                            <tr class="border-b last:border-0">
                                <td class="px-3 py-2 font-medium text-gray-800">{{ $c['label'] }}</td>
                                <td class="text-center px-3 py-2">
                                    <span class="text-green-700 font-semibold">{{ $c['answered'] }}</span>
                                    <span class="text-gray-400">/ {{ $d['total_siswa'] }}</span>
                                </td>
                                <td class="text-center px-3 py-2 {{ $c['not_answered'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                    {{ $c['not_answered'] }}
                                </td>
                                <td class="text-center px-3 py-2">{{ is_null($c['graded']) ? '—' : $c['graded'] }}</td>
                                <td class="text-center px-3 py-2 {{ !is_null($c['not_graded']) && $c['not_graded'] > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                                    {{ is_null($c['not_graded']) ? '—' : $c['not_graded'] }}
                                </td>
                                <td class="text-center px-3 py-2 font-semibold text-gray-800">
                                    {{ is_null($c['avg']) ? '—' : $c['avg'] }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Progres per siswa -->
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-700">Progres per Siswa</h3>
                    <input type="text" id="dashStudentFilter" onkeyup="filterDashStudents()"
                           placeholder="Cari nama siswa..."
                           class="text-sm border border-gray-300 rounded px-2 py-1 w-56">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-200" id="dashStudentTable">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="text-left px-3 py-2 border-b w-8">#</th>
                                <th class="text-left px-3 py-2 border-b">Nama Siswa</th>
                                @foreach($d['checklist_keys'] as $ck)
                                    <th class="text-center px-2 py-2 border-b" title="{{ $ck }}">{{ $compLabels[$ck] ?? $ck }}</th>
                                @endforeach
                                <th class="text-center px-3 py-2 border-b w-40">Progres</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($d['progress'] as $i => $row)
                            <tr class="border-b last:border-0 dash-student-row" data-nama="{{ strtolower($row['nama']) }}">
                                <td class="px-3 py-2 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-gray-800">{{ $row['nama'] }}</td>
                                @foreach($d['checklist_keys'] as $ck)
                                    @php $cell = $row['cells'][$ck]; @endphp
                                    <td class="text-center px-2 py-2">
                                        @if(!is_null($cell['nilai']))
                                            <span class="inline-block min-w-[2rem] px-1.5 py-0.5 rounded bg-green-100 text-green-800 font-semibold">{{ $cell['nilai'] }}</span>
                                        @elseif($cell['done'])
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-green-100 text-green-700" title="Selesai">&#10003;</span>
                                        @elseif($cell['answered'])
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-amber-100 text-amber-700" title="Sudah jawab, belum dinilai">&#9679;</span>
                                        @else
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 text-gray-400" title="Belum">&ndash;</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $row['percent'] == 100 ? 'bg-green-600' : 'bg-blue-600' }}"
                                                 style="width: {{ $row['percent'] }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600 w-12 text-right">{{ $row['done'] }}/{{ count($d['checklist_keys']) }}</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if(count($d['progress']) === 0)
                            <tr><td colspan="{{ count($d['checklist_keys']) + 3 }}" class="text-center px-3 py-4 text-gray-500">Belum ada siswa terdaftar di kelas ini.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    Keterangan: angka = nilai &nbsp;·&nbsp; &#10003; = selesai &nbsp;·&nbsp; &#9679; = sudah jawab belum dinilai &nbsp;·&nbsp; &ndash; = belum.
                    MDD=Mulai Dari Diri, EK=Eksplorasi Konsep, RK=Ruang Kolaborasi, DK=Demonstrasi Konseptual, EP=Elaborasi Pemahaman.
                </p>
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <div class="space-y-6">
            <!-- Component 1: Mulai Dari Diri -->
            <div class="component-card">
                <div class="component-header">
                    <h3 class="component-title">1. Mulai Dari Diri</h3>
                    @if($isTeacher)
                        <div style="display: flex; gap: 8px;">
                            <button onclick="exportNilai('mulai_dari_diri')" class="btn-export">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <button onclick="showGradingModal('mulai_dari_diri')" class="btn-primary">
                                Lihat & Nilai Jawaban
                            </button>
                        </div>
                    @elseif($isStudent)
                        @if(isset($studentAnswers['mulai_dari_diri']) && $studentAnswers['mulai_dari_diri'])
                            @if($studentAnswers['mulai_dari_diri']->nilai !== null)
                                <span class="grade-badge grade-{{ getGradeClass($studentAnswers['mulai_dari_diri']->nilai) }}">
                                    Nilai: {{ $studentAnswers['mulai_dari_diri']->nilai }}
                                </span>
                            @else
                                <span class="grade-badge grade-pending">Menunggu Penilaian</span>
                            @endif
                        @endif
                    @endif
                </div>
                
                @if($isTeacher && $material->mulai_dari_diri)
                    <div class="mb-4">
                        <a href="{{ asset('storage/'.$material->mulai_dari_diri) }}" target="_blank" class="text-blue-600 hover:underline">
                            Download Soal
                        </a>
                    </div>
                @elseif($isStudent)
                    @if($material->mulai_dari_diri)
                        <div class="mb-4">
                            <a href="{{ asset('storage/'.$material->mulai_dari_diri) }}" target="_blank" class="text-blue-600 hover:underline">
                                Download Soal
                            </a>
                        </div>
                        <form action="{{ route('materi.uploadJawaban', [$courseId, $material->id_materi]) }}" 
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="component" value="mulai_dari_diri">
                            <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx" class="mb-2">
                            <button type="submit" class="btn-success">Upload Jawaban</button>
                        </form>
                    @else
                        <p class="text-gray-500">Soal belum tersedia</p>
                    @endif
                @endif
            </div>

            <!-- Component 2: Eksplorasi Konsep -->
            <div class="component-card">
                <div class="component-header">
                    <h3 class="component-title">2. Eksplorasi Konsep</h3>
                    @if($isTeacher && isset($soalMetakognisi))
                        <div style="display: flex; gap: 8px;">
                            <button onclick="exportNilai('metakognisi')" class="btn-export">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <button onclick="showMetakognisiGradingModal()" class="btn-primary">
                                Lihat & Nilai Metakognisi
                            </button>
                        </div>
                    @endif
                </div>
                
                @if(isset($eksplorasiKonsep))
                    <div class="mb-4">
                        @if($eksplorasiKonsep->pdf)
                            <a href="{{ asset('storage/'.$eksplorasiKonsep->pdf) }}" target="_blank" class="text-blue-600 hover:underline">
                                Download PDF Materi
                            </a>
                        @endif
                        @if($eksplorasiKonsep->video)
                            <div class="mt-2">
                                <a href="{{ $eksplorasiKonsep->video }}" target="_blank" class="text-blue-600 hover:underline">
                                    Lihat Video
                                </a>
                            </div>
                        @endif
                        @if($eksplorasiKonsep->isi_materi)
                            <div class="mt-4 p-4 bg-gray-50 rounded">
                                {!! $eksplorasiKonsep->isi_materi !!}
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500">Materi eksplorasi konsep belum tersedia</p>
                @endif
            </div>

            <!-- Component 3: Ruang Kolaborasi -->
            <div class="component-card">
                <div class="component-header">
                    <h3 class="component-title">3. Ruang Kolaborasi</h3>
                    @if($isTeacher)
                        <div style="display: flex; gap: 8px;">
                            <button onclick="exportNilai('ruang_kolaborasi')" class="btn-export">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <button onclick="showGradingModal('ruang_kolaborasi')" class="btn-primary">
                                Nilai Jawaban
                            </button>
                            <button onclick="showPREGradingModal('ruang_kolaborasi')" class="btn-secondary ml-2">
                                Nilai PRE
                            </button>
                        </div>
                    @endif
                </div>
                
                @if($isTeacher && $material->ruang_kolaborasi)
                    <div class="mb-4">
                        <a href="{{ asset('storage/'.$material->ruang_kolaborasi) }}" target="_blank" class="text-blue-600 hover:underline">
                            Download Soal
                        </a>
                    </div>
                @elseif($isStudent)
                    @if($material->ruang_kolaborasi)
                        <div class="mb-4">
                            <a href="{{ asset('storage/'.$material->ruang_kolaborasi) }}" target="_blank" class="text-blue-600 hover:underline">
                                Download Soal
                            </a>
                        </div>
                        <form action="{{ route('materi.uploadJawaban', [$courseId, $material->id_materi]) }}" 
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="component" value="ruang_kolaborasi">
                            <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx" class="mb-2">
                            <button type="submit" class="btn-success">Upload Jawaban</button>
                        </form>
                    @endif
                    
                    @if(isset($soalPRE['ruang_kolaborasi_perencanaan']) || isset($soalPRE['ruang_kolaborasi_refleksi']) || isset($soalPRE['ruang_kolaborasi_evaluasi']))
                        <div class="mt-4 p-4 bg-blue-50 rounded">
                            <h4 class="font-semibold mb-2">Soal Essay PRE:</h4>
                            @if(isset($soalPRE['ruang_kolaborasi_perencanaan']))
                                <div class="mb-3">
                                    <strong>Perencanaan:</strong>
                                    <div class="bg-white p-2 rounded mt-1">{!! $soalPRE['ruang_kolaborasi_perencanaan'] !!}</div>
                                </div>
                            @endif
                            @if(isset($soalPRE['ruang_kolaborasi_refleksi']))
                                <div class="mb-3">
                                    <strong>Refleksi:</strong>
                                    <div class="bg-white p-2 rounded mt-1">{!! $soalPRE['ruang_kolaborasi_refleksi'] !!}</div>
                                </div>
                            @endif
                            @if(isset($soalPRE['ruang_kolaborasi_evaluasi']))
                                <div class="mb-3">
                                    <strong>Evaluasi:</strong>
                                    <div class="bg-white p-2 rounded mt-1">{!! $soalPRE['ruang_kolaborasi_evaluasi'] !!}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>

            <!-- Component 4: Demonstrasi Konseptual -->
            <div class="component-card">
                <div class="component-header">
                    <h3 class="component-title">4. Demonstrasi Konseptual</h3>
                    @if($isTeacher)
                        <div style="display: flex; gap: 8px;">
                            <button onclick="exportNilai('demonstrasi_konseptual')" class="btn-export">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <button onclick="showGradingModal('demonstrasi_konseptual')" class="btn-primary">
                                Nilai Jawaban
                            </button>
                            <button onclick="showPREGradingModal('demonstrasi_konseptual')" class="btn-secondary ml-2">
                                Nilai PRE
                            </button>
                        </div>
                    @endif
                </div>
                
                @if($isTeacher && $material->demonstrasi_konseptual)
                    <div class="mb-4">
                        <a href="{{ asset('storage/'.$material->demonstrasi_konseptual) }}" target="_blank" class="text-blue-600 hover:underline">
                            Download Soal
                        </a>
                    </div>
                @elseif($isStudent)
                    @if($material->demonstrasi_konseptual)
                        <div class="mb-4">
                            <a href="{{ asset('storage/'.$material->demonstrasi_konseptual) }}" target="_blank" class="text-blue-600 hover:underline">
                                Download Soal
                            </a>
                        </div>
                        <form action="{{ route('materi.uploadJawaban', [$courseId, $material->id_materi]) }}" 
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="component" value="demonstrasi_konseptual">
                            <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx" class="mb-2">
                            <button type="submit" class="btn-success">Upload Jawaban</button>
                        </form>
                    @endif
                @endif
            </div>

            <!-- Component 5: Elaborasi Pemahaman -->
            <div class="component-card">
                <div class="component-header">
                    <h3 class="component-title">5. Elaborasi Pemahaman</h3>
                    @if($isTeacher)
                        <div style="display: flex; gap: 8px;">
                            <button onclick="exportNilai('elaborasi_pemahaman')" class="btn-export">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <button onclick="showGradingModal('elaborasi_pemahaman')" class="btn-primary">
                                Nilai Jawaban
                            </button>
                            <button onclick="showPREGradingModal('elaborasi_pemahaman')" class="btn-secondary ml-2">
                                Nilai PRE
                            </button>
                        </div>
                    @endif
                </div>
                
                @if($isTeacher && $material->elaborasi_pemahaman)
                    <div class="mb-4">
                        <a href="{{ asset('storage/'.$material->elaborasi_pemahaman) }}" target="_blank" class="text-blue-600 hover:underline">
                            Download Soal
                        </a>
                    </div>
                @elseif($isStudent)
                    @if($material->elaborasi_pemahaman)
                        <div class="mb-4">
                            <a href="{{ asset('storage/'.$material->elaborasi_pemahaman) }}" target="_blank" class="text-blue-600 hover:underline">
                                Download Soal
                            </a>
                        </div>
                        <form action="{{ route('materi.uploadJawaban', [$courseId, $material->id_materi]) }}" 
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="component" value="elaborasi_pemahaman">
                            <input type="file" name="file_jawaban" accept=".pdf,.doc,.docx" class="mb-2">
                            <button type="submit" class="btn-success">Upload Jawaban</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal for Regular Grading -->
<div id="gradingModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold" id="modalTitle">Penilaian</h2>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div id="modalContent"></div>
    </div>
</div>

<!-- Modal for Metakognisi Grading -->
<div id="metakognisiModal" class="modal">
    <div class="modal-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Penilaian Pengetahuan Metakognisi</h2>
            <button onclick="closeMetakognisiModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div id="metakognisiContent"></div>
    </div>
</div>

@endsection

@section('js_tambahan')
<script>
function getGradeClass(nilai) {
    if (nilai >= 85) return 'excellent';
    if (nilai >= 75) return 'good';
    if (nilai >= 65) return 'fair';
    return 'poor';
}

function toggleDashboard() {
    var body = document.getElementById('dashBody');
    var btn = document.getElementById('dashToggleBtn');
    if (!body) return;
    var hidden = body.hasAttribute('hidden');
    if (hidden) { body.removeAttribute('hidden'); btn.textContent = 'Sembunyikan'; }
    else { body.setAttribute('hidden', ''); btn.textContent = 'Tampilkan'; }
}

function filterDashStudents() {
    var q = (document.getElementById('dashStudentFilter').value || '').toLowerCase().trim();
    document.querySelectorAll('#dashStudentTable .dash-student-row').forEach(function (row) {
        var nama = row.getAttribute('data-nama') || '';
        row.style.display = (q === '' || nama.indexOf(q) !== -1) ? '' : 'none';
    });
}

function exportNilai(component) {
    const courseId = {{ $courseId }};
    const materialId = {{ $material->id_materi }};
    
    // Show loading state
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Mengunduh...
    `;
    
    // Open export URL
    window.location.href = `/courses/${courseId}/materials/${materialId}/export/${component}`;
    
    // Reset button after delay
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }, 2000);
}

function showGradingModal(component) {
    document.getElementById('modalTitle').innerText = 'Penilaian ' + formatComponentName(component);
    document.getElementById('gradingModal').classList.add('show');
    loadAnswers(component, null);
}

function showPREGradingModal(component) {
    document.getElementById('modalTitle').innerText = 'Penilaian PRE ' + formatComponentName(component);
    document.getElementById('gradingModal').classList.add('show');
    
    let content = `
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="switchPRETab('${component}', 'perencanaan', this)">Perencanaan</button>
            <button class="nav-tab" onclick="switchPRETab('${component}', 'refleksi', this)">Refleksi</button>
            <button class="nav-tab" onclick="switchPRETab('${component}', 'evaluasi', this)">Evaluasi</button>
        </div>
        <div id="preTabContent"></div>
    `;
    document.getElementById('modalContent').innerHTML = content;
    loadAnswers(component, 'perencanaan');
}

function showMetakognisiGradingModal() {
    document.getElementById('metakognisiModal').classList.add('show');
    loadMetakognisiAnswers();
}

function switchPRETab(component, type, button) {
    document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('active'));
    button.classList.add('active');
    loadAnswers(component, type);
}

function switchMetakognisiTab(answerId, type) {
    const container = document.getElementById('student_' + answerId);
    
    container.querySelectorAll('.metakognisi-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    container.querySelectorAll('.metakognisi-panel').forEach(panel => {
        panel.classList.remove('active');
    });
    
    const activeTab = container.querySelector(`[data-tab="${type}"]`);
    const activePanel = container.querySelector(`#${type}_${answerId}`);
    
    if (activeTab) activeTab.classList.add('active');
    if (activePanel) activePanel.classList.add('active');
}

function loadAnswers(component, type) {
    const courseId = {{ $courseId }};
    const materialId = {{ $material->id_materi }};
    
    let url = `/courses/${courseId}/materials/${materialId}/grading/answers?component=${component}`;
    if (type) {
        url += `&type=${type}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAnswers(data.answers, component, type);
            } else {
                alert('Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
}

function loadMetakognisiAnswers() {
    const courseId = {{ $courseId }};
    const materialId = {{ $material->id_materi }};
    
    fetch(`/courses/${courseId}/materials/${materialId}/grading/answers?component=metakognisi`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMetakognisiAnswers(data.answers);
            } else {
                alert('Gagal memuat data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
}

function displayMetakognisiAnswers(answers) {
    const container = document.getElementById('metakognisiContent');
    
    if (answers.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">Belum ada jawaban dari siswa</p>';
        return;
    }
    
    let html = '<div class="space-y-6">';
    
    answers.forEach(answer => {
        html += `
            <div class="metakognisi-container" id="student_${answer.id}">
                <div class="metakognisi-header">
                    <h3 class="text-lg font-semibold">${answer.nama_siswa}</h3>
                    <p class="text-sm text-gray-500 mt-1">${formatDate(answer.created_at)}</p>
                </div>
                
                <div class="metakognisi-tabs">
                    <button class="metakognisi-tab active" 
                            data-tab="deklaratif"
                            onclick="switchMetakognisiTab(${answer.id}, 'deklaratif')">
                        Deklaratif
                    </button>
                    <button class="metakognisi-tab" 
                            data-tab="prosedural"
                            onclick="switchMetakognisiTab(${answer.id}, 'prosedural')">
                        Prosedural
                    </button>
                    <button class="metakognisi-tab" 
                            data-tab="kondisional"
                            onclick="switchMetakognisiTab(${answer.id}, 'kondisional')">
                        Kondisional
                    </button>
                </div>
                
                <div class="metakognisi-content">
                    ${renderMetakognisiPanel(answer, 'deklaratif', true)}
                    ${renderMetakognisiPanel(answer, 'prosedural', false)}
                    ${renderMetakognisiPanel(answer, 'kondisional', false)}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function renderMetakognisiPanel(answer, type, isActive) {
    const jawaban = answer[type] || '';
    const nilai = answer[`nilai_${type}`] || '';
    const isPdf = jawaban.includes('.pdf');
    
    return `
        <div id="${type}_${answer.id}" class="metakognisi-panel ${isActive ? 'active' : ''}">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jawaban Siswa</label>
                <div class="answer-box">
                    ${jawaban ? 
                        (isPdf ? 
                            `<a href="/storage/${jawaban}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Lihat File PDF
                            </a>` :
                            `<p class="text-gray-700 whitespace-pre-wrap">${jawaban}</p>`
                        ) :
                        '<p class="text-gray-400">Belum ada jawaban</p>'
                    }
                </div>
            </div>
            
            ${jawaban ? `
                <div class="grade-section">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nilai (0-100)</label>
                        <input type="number" 
                               id="grade_${answer.id}_${type}" 
                               min="0" 
                               max="100" 
                               value="${nilai}"
                               placeholder="Masukkan nilai">
                    </div>
                    <button onclick="submitMetakognisiGrade(${answer.id}, '${type}')" 
                            class="btn-primary">
                        Simpan Nilai
                    </button>
                </div>
            ` : ''}
        </div>
    `;
}

function displayAnswers(answers, component, type) {
    let contentId = type ? 'preTabContent' : 'modalContent';
    let container = document.getElementById(contentId);
    
    if (answers.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">Belum ada jawaban dari siswa</p>';
        return;
    }
    
    let html = '<div class="space-y-4">';
    
    answers.forEach(answer => {
        const nilaiField = type ? `nilai_${type}` : 'nilai';
        const nilai = answer[nilaiField] || answer.nilai;
        
        html += `
            <div class="student-answer">
                <div class="student-info">
                    <strong>${answer.nama_siswa}</strong>
                    <span class="text-sm text-gray-500">${formatDate(answer.created_at)}</span>
                </div>
                
                <div class="answer-content">
                    ${answer.jawaban ? 
                        (answer.jawaban.includes('.pdf') || answer.jawaban.includes('.doc') ? 
                            `<a href="/storage/${answer.jawaban}" target="_blank" class="text-blue-600 hover:underline">Lihat File</a>` :
                            `<p class="whitespace-pre-wrap">${answer.jawaban}</p>`
                        ) : 
                        '<p class="text-gray-400">Tidak ada jawaban</p>'
                    }
                </div>
                
                <div class="mt-3 flex items-center gap-3">
                    <label class="text-sm font-medium">Nilai:</label>
                    <input type="number" 
                           id="grade_${answer.id}" 
                           class="grade-input" 
                           min="0" max="100" 
                           value="${nilai || ''}"
                           placeholder="0-100">
                    <button onclick="submitGrade(${answer.id}, '${component}', '${type || ''}')" 
                            class="btn-primary">
                        Simpan
                    </button>
                    ${nilai ? `<span class="grade-badge grade-${getGradeClass(nilai)}">Nilai: ${nilai}</span>` : ''}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

function submitGrade(answerId, component, type) {
    const gradeInput = document.getElementById(`grade_${answerId}`);
    const nilai = gradeInput.value;
    
    if (!nilai || nilai < 0 || nilai > 100) {
        alert('Nilai harus antara 0-100');
        return;
    }
    
    const courseId = {{ $courseId }};
    const materialId = {{ $material->id_materi }};
    
    fetch(`/courses/${courseId}/materials/${materialId}/grading/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            answer_id: answerId,
            component: component,
            type: type,
            nilai: parseInt(nilai)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Penilaian berhasil disimpan');
            if (type) {
                loadAnswers(component, type);
            } else {
                loadAnswers(component, null);
            }
        } else {
            alert('Gagal menyimpan penilaian: ' + (data.message || ''));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

function submitMetakognisiGrade(answerId, type) {
    const gradeInput = document.getElementById(`grade_${answerId}_${type}`);
    const nilai = gradeInput.value;
    
    if (!nilai || nilai < 0 || nilai > 100) {
        alert('Nilai harus antara 0-100');
        return;
    }
    
    const courseId = {{ $courseId }};
    const materialId = {{ $material->id_materi }};
    
    fetch(`/courses/${courseId}/materials/${materialId}/grading/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            answer_id: answerId,
            component: 'metakognisi',
            type: type,
            nilai: parseInt(nilai)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Penilaian berhasil disimpan');
            loadMetakognisiAnswers();
        } else {
            alert('Gagal menyimpan penilaian: ' + (data.message || ''));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

function closeModal() {
    document.getElementById('gradingModal').classList.remove('show');
}

function closeMetakognisiModal() {
    document.getElementById('metakognisiModal').classList.remove('show');
}

function formatComponentName(component) {
    const names = {
        'mulai_dari_diri': 'Mulai Dari Diri',
        'ruang_kolaborasi': 'Ruang Kolaborasi',
        'demonstrasi_konseptual': 'Demonstrasi Konseptual',
        'elaborasi_pemahaman': 'Elaborasi Pemahaman',
        'metakognisi': 'Pengetahuan Metakognisi'
    };
    return names[component] || component;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Add CSRF token to header if not exists
if (!document.querySelector('meta[name="csrf-token"]')) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = '{{ csrf_token() }}';
    document.head.appendChild(meta);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const gradingModal = document.getElementById('gradingModal');
    const metakognisiModal = document.getElementById('metakognisiModal');
    
    if (event.target === gradingModal) {
        closeModal();
    }
    if (event.target === metakognisiModal) {
        closeMetakognisiModal();
    }
});
</script>
@endsection

@php
function getGradeClass($nilai) {
    if ($nilai >= 85) return 'excellent';
    if ($nilai >= 75) return 'good';
    if ($nilai >= 65) return 'fair';
    return 'poor';
}
@endphp