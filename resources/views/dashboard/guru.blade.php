@extends('layouts.home')

@section('css_tambahan')
<style>
    .class-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.3s ease;
    }
    
    .class-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .progress-circle {
        background: conic-gradient(
            from 0deg,
            #3B82F6 0deg,
            #3B82F6 calc(var(--progress, 0) * 3.6deg),
            #E2E8F0 calc(var(--progress, 0) * 3.6deg)
        );
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .progress-circle::before {
        content: '';
        position: absolute;
        width: 80%;
        height: 80%;
        background: white;
        border-radius: 50%;
    }
    
    .progress-text {
        position: relative;
        z-index: 1;
        font-weight: bold;
        color: #1f2937;
    }
    
    .assignment-priority-high {
        border-left: 4px solid #EF4444;
        background: linear-gradient(to right, #FEF2F2, #FFFFFF);
    }
    
    .assignment-priority-medium {
        border-left: 4px solid #F59E0B;
        background: linear-gradient(to right, #FFFBEB, #FFFFFF);
    }
    
    .assignment-priority-low {
        border-left: 4px solid #10B981;
        background: linear-gradient(to right, #F0FDF4, #FFFFFF);
    }
    
    .notification-badge {
        animation: bounce 1s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }
    
    .teaching-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>
@endsection

@section('konten')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
    <!-- Welcome Header -->
    <div class="teaching-stats text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Selamat Datang, {{ $profile->nama ?? 'Guru' }}!</h1>
                    <p class="mt-2 text-indigo-100">
                        NIP: <span class="font-semibold">{{ $profile->nip_nis ?? '-' }}</span> • 
                        Status: <span class="font-semibold">{{ ucfirst($profile->status ?? 'Guru') }}</span>
                    </p>
                </div>
                
                <div class="mt-6 lg:mt-0 flex space-x-4">
                    <div class="bg-white/20 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold">{{ $totalKelas }}</div>
                        <div class="text-sm text-indigo-100">Kelas Diampu</div>
                    </div>
                    <div class="bg-white/20 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold">{{ $totalSiswa }}</div>
                        <div class="text-sm text-indigo-100">Total Siswa</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Modul Aktif -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $totalModul }}</div>
                        <div class="text-sm text-slate-600">Modul Aktif</div>
                    </div>
                </div>
            </div>

            <!-- Tugas Belum Dinilai -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">
                            {{ $tugasBelumDinilai }}
                            @if($tugasBelumDinilai > 0)
                            <span class="notification-badge inline-block w-2 h-2 bg-red-500 rounded-full ml-1"></span>
                            @endif
                        </div>
                        <div class="text-sm text-slate-600">Perlu Dinilai</div>
                    </div>
                </div>
            </div>

            <!-- Siswa Aktif Hari Ini -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $siswaAktifHariIni }}</div>
                        <div class="text-sm text-slate-600">Siswa Aktif</div>
                    </div>
                </div>
            </div>

            <!-- Rata-rata Progress -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $avgProgress }}%</div>
                        <div class="text-sm text-slate-600">Avg Progress</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Classes & Modules -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Kelas yang Diampu -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Kelas yang Diampu</h3>
                                <p class="text-sm text-slate-600">Tahun Ajaran {{ date('Y') }}/{{ date('Y') + 1 }}</p>
                            </div>
                            <a href="{{ route('courses.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                Lihat Semua Kelas
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        @if(count($kelasList) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($kelasList as $kelas)
                            <div class="class-card bg-gradient-to-br from-{{ $kelas['color'] }}-500 to-{{ $kelas['color'] }}-600 rounded-xl p-6 text-white">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="font-semibold text-lg">{{ $kelas['nama'] }}</h4>
                                        <p class="text-{{ $kelas['color'] }}-100 text-sm">{{ $kelas['jumlah_siswa'] }} siswa</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-2xl font-bold">{{ $kelas['progress'] }}%</div>
                                        <div class="text-{{ $kelas['color'] }}-100 text-xs">Avg Progress</div>
                                    </div>
                                </div>
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between text-sm">
                                        <span>Modul Aktif</span>
                                        <span>{{ $kelas['modul_aktif'] }} modul</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span>Tugas Pending</span>
                                        <span>{{ $kelas['tugas_pending'] }} tugas</span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('modul.index', $kelas['id']) }}" class="flex-1 bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-3 rounded-lg transition-colors text-sm text-center">
                                        Kelola
                                    </a>
                                    <button onclick="showClassStatistics('{{ $kelas['nama'] }}')" class="flex-1 bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-3 rounded-lg transition-colors text-sm">
                                        Statistik
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p class="text-slate-600">Belum ada kelas yang diampu</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Modul Progress Overview -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Progress Modul Pembelajaran</h3>
                        <p class="text-sm text-slate-600">Status pembelajaran per modul</p>
                    </div>
                    <div class="p-6">
                        @if(count($modulList) > 0)
                        <div class="space-y-6">
                            @foreach($modulList as $modul)
                            <div class="border border-slate-200 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="font-semibold text-slate-900">{{ $modul['nama'] }}</h4>
                                        <p class="text-sm text-slate-600">{{ $modul['siswa_aktif'] }} siswa sedang mengerjakan</p>
                                    </div>
                                    <span class="bg-{{ $modul['status'] == 'Aktif' ? 'blue' : 'gray' }}-100 text-{{ $modul['status'] == 'Aktif' ? 'blue' : 'gray' }}-800 text-xs px-2 py-1 rounded-full">{{ $modul['status'] }}</span>
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                                    <div class="text-center">
                                        <div class="w-12 h-12 progress-circle mx-auto mb-2" style="--progress: {{ $modul['progress']['mulai_dari_diri'] }}">
                                            <span class="progress-text text-xs">{{ $modul['progress']['mulai_dari_diri'] }}%</span>
                                        </div>
                                        <p class="text-xs text-slate-600">Mulai dari Diri</p>
                                    </div>
                                    <div class="text-center">
                                        <div class="w-12 h-12 progress-circle mx-auto mb-2" style="--progress: {{ $modul['progress']['eksplorasi_konsep'] }}">
                                            <span class="progress-text text-xs">{{ $modul['progress']['eksplorasi_konsep'] }}%</span>
                                        </div>
                                        <p class="text-xs text-slate-600">Eksplorasi</p>
                                    </div>
                                    <div class="text-center">
                                        <div class="w-12 h-12 progress-circle mx-auto mb-2" style="--progress: {{ $modul['progress']['ruang_kolaborasi'] }}">
                                            <span class="progress-text text-xs">{{ $modul['progress']['ruang_kolaborasi'] }}%</span>
                                        </div>
                                        <p class="text-xs text-slate-600">Kolaborasi</p>
                                    </div>
                                    <div class="text-center">
                                        <div class="w-12 h-12 progress-circle mx-auto mb-2" style="--progress: {{ $modul['progress']['demonstrasi_konseptual'] }}">
                                            <span class="progress-text text-xs">{{ $modul['progress']['demonstrasi_konseptual'] }}%</span>
                                        </div>
                                        <p class="text-xs text-slate-600">Demonstrasi</p>
                                    </div>
                                    <div class="text-center">
                                        <div class="w-12 h-12 progress-circle mx-auto mb-2" style="--progress: {{ $modul['progress']['elaborasi_pemahaman'] }}">
                                            <span class="progress-text text-xs">{{ $modul['progress']['elaborasi_pemahaman'] }}%</span>
                                        </div>
                                        <p class="text-xs text-slate-600">Elaborasi</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-slate-600">Belum ada modul aktif</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Tasks & Activities -->
            <div class="space-y-8">
                <!-- Tugas yang Perlu Dinilai -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Perlu Dinilai</h3>
                        <p class="text-sm text-slate-600">Tugas menunggu penilaian</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4 max-h-80 overflow-y-auto">
                            @forelse($tugasPerluDinilai as $tugas)
                            <div class="assignment-priority-{{ $tugas['priority'] }} rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium text-slate-900 text-sm">{{ $tugas['nama'] }}</h4>
                                    <span class="text-xs bg-{{ $tugas['priority'] == 'high' ? 'red' : ($tugas['priority'] == 'medium' ? 'yellow' : 'green') }}-100 text-{{ $tugas['priority'] == 'high' ? 'red' : ($tugas['priority'] == 'medium' ? 'yellow' : 'green') }}-800 px-2 py-1 rounded-full">
                                        {{ $tugas['priority'] == 'high' ? 'Urgent' : ($tugas['priority'] == 'medium' ? 'Medium' : 'Normal') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mb-2">{{ $tugas['kelas'] }} • {{ $tugas['jumlah_siswa'] }} siswa</p>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-{{ $tugas['priority'] == 'high' ? 'red' : ($tugas['priority'] == 'medium' ? 'yellow' : 'green') }}-600 font-medium">{{ $tugas['deadline'] }}</span>
                                    <button class="text-xs bg-{{ $tugas['priority'] == 'high' ? 'red' : ($tugas['priority'] == 'medium' ? 'yellow' : 'green') }}-600 text-white px-3 py-1 rounded-lg hover:bg-{{ $tugas['priority'] == 'high' ? 'red' : ($tugas['priority'] == 'medium' ? 'yellow' : 'green') }}-700 transition-colors">
                                        Nilai Sekarang
                                    </button>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-green-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-slate-600 text-sm">Semua tugas sudah dinilai!</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Siswa Terbaru -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Aktivitas Terbaru</h3>
                        <p class="text-sm text-slate-600">Update dari siswa</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4 max-h-80 overflow-y-auto">
                            @forelse($aktivitasTerbaru as $aktivitas)
                            @php
                                $colors = ['blue', 'green', 'purple', 'orange', 'teal', 'red', 'indigo', 'pink'];
                                $color = $colors[array_rand($colors)];
                                $initials = collect(explode(' ', $aktivitas->nama))->map(fn($word) => strtoupper(substr($word, 0, 1)))->take(2)->join('');
                                $timeAgo = \Carbon\Carbon::parse($aktivitas->updated_at)->diffForHumans();
                            @endphp
                            <div class="flex items-start space-x-3 p-3 bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-lg">
                                <div class="w-8 h-8 bg-{{ $color }}-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                    {{ $initials }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-900 font-medium">{{ $aktivitas->nama }}</p>
                                    <p class="text-xs text-slate-600">{{ $aktivitas->aktivitas }} - {{ $aktivitas->nama_materi }}</p>
                                    <p class="text-xs text-{{ $color }}-600 mt-1">{{ $timeAgo }}</p>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-slate-600 text-sm">Belum ada aktivitas</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Aksi Cepat</h3>
                        <p class="text-sm text-slate-600">Tools untuk mengajar</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('courses.index') }}" class="w-full flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium text-slate-900">Kelola Kelas</div>
                                    <div class="text-xs text-slate-600">Lihat semua kelas</div>
                                </div>
                            </a>

                            <button class="w-full flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium text-slate-900">Laporan Progress</div>
                                    <div class="text-xs text-slate-600">Analisis pembelajaran</div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_tambahan')
<script>
// Progress circle animations
document.addEventListener('DOMContentLoaded', function() {
    const progressCircles = document.querySelectorAll('.progress-circle');
    progressCircles.forEach((circle, index) => {
        setTimeout(() => {
            const progress = circle.style.getPropertyValue('--progress');
            circle.style.setProperty('--progress', 0);
            setTimeout(() => {
                circle.style.transition = 'all 1s ease-out';
                circle.style.setProperty('--progress', progress);
            }, 100);
        }, index * 100);
    });
});

// Class card interactions
document.querySelectorAll('.class-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(-4px) scale(1)';
    });
});

// Show class statistics
function showClassStatistics(className) {
    alert('Fitur statistik untuk ' + className + ' sedang dalam pengembangan');
}
</script>
@endsection