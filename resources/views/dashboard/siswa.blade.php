@extends('layouts.home')

@section('css_tambahan')
<style>
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring circle {
        transition: stroke-dasharray 0.5s ease-in-out;
    }
    
    .subject-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.3s ease;
    }
    
    .subject-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .achievement-badge {
        background: linear-gradient(45deg, #FFD700, #FFA500);
        animation: shine 2s infinite;
    }
    
    @keyframes shine {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .learning-path {
        position: relative;
    }
    
    .learning-path::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, #3B82F6, #10B981);
    }
    
    .path-node {
        position: relative;
        z-index: 1;
    }
</style>
@endsection

@section('konten')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-green-50">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-edu-blue to-edu-green text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold">Selamat Datang, {{ $profile->nama ?? 'Siswa' }}!</h1>
                    <p class="mt-2 text-blue-100">
                        Kelas: <span class="font-semibold">{{ $kelasInfo->nama_kelas ?? '-' }}</span> • 
                        Tahun Ajaran: <span class="font-semibold">{{ $kelasInfo->nama_ta ?? '-' }}</span>
                    </p>
                </div>
                <div class="mt-6 lg:mt-0">
                    <div class="bg-white/20 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold">{{ $overallProgress }}%</div>
                        <div class="text-sm text-blue-100">Progress Keseluruhan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Learning Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Mata Pelajaran -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $totalMataPelajaran }}</div>
                        <div class="text-sm text-slate-600">Mata Pelajaran</div>
                    </div>
                </div>
            </div>

            <!-- Modul Selesai -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $modulSelesai }}</div>
                        <div class="text-sm text-slate-600">Modul Selesai</div>
                    </div>
                </div>
            </div>

            <!-- Tugas Pending -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $tugasPending }}</div>
                        <div class="text-sm text-slate-600">Tugas Pending</div>
                    </div>
                </div>
            </div>

            <!-- Prestasi -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center achievement-badge">
                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-2xl font-bold text-slate-900">{{ $badgeCount }}</div>
                        <div class="text-sm text-slate-600">Badge Earned</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Subject Progress -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Mata Pelajaran Aktif -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Mata Pelajaran Aktif</h3>
                        <p class="text-sm text-slate-600">Progress pembelajaran Anda</p>
                    </div>
                    <div class="p-6">
                        @if(count($mataPelajaran) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($mataPelajaran as $mp)
                                    <div class="subject-card bg-gradient-to-br from-{{ $mp['color'] }}-500 to-{{ $mp['color'] }}-600 rounded-xl p-6 text-white">
                                        <div class="flex justify-between items-start mb-4">
                                            <div>
                                                <h4 class="font-semibold text-lg">{{ $mp['nama'] }}</h4>
                                                <p class="text-{{ $mp['color'] }}-100 text-sm">{{ $mp['guru'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold">{{ $mp['progress'] }}%</div>
                                                <div class="text-{{ $mp['color'] }}-100 text-xs">Progress</div>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between text-sm">
                                                <span>Modul Selesai</span>
                                                <span>{{ $mp['modul_selesai'] }}/{{ $mp['total_modul'] }}</span>
                                            </div>
                                            <div class="w-full bg-{{ $mp['color'] }}-400 rounded-full h-2">
                                                <div class="bg-white rounded-full h-2" style="width: {{ $mp['progress'] }}%"></div>
                                            </div>
                                        </div>
                                        <a href="{{ route('modul.index', $mp['id']) }}" class="mt-4 block w-full bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-4 rounded-lg transition-colors text-center">
                                            Lanjut Belajar
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-slate-500">
                                <p>Belum ada mata pelajaran yang tersedia</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Learning Path Current Module -->
                @if($currentModule)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Modul Sedang Dipelajari</h3>
                        <p class="text-sm text-slate-600">{{ $currentModule->nama_mata_pelajaran }} - {{ $currentModule->nama_materi }}</p>
                    </div>
                    <div class="p-6">
                        <div class="learning-path space-y-6">
                            @foreach($learningPath as $step)
                                <div class="path-node flex items-start {{ !$step['completed'] && !$step['current'] ? 'opacity-50' : '' }}">
                                    <div class="w-10 h-10 {{ $step['completed'] ? 'bg-green-500' : ($step['current'] ? 'bg-blue-500' : 'bg-slate-300') }} rounded-full flex items-center justify-center text-white font-bold mr-4 mt-1">
                                        @if($step['completed'])
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            {{ $step['number'] }}
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-slate-900">{{ $step['name'] }}</h4>
                                        <div class="mt-2 {{ $step['completed'] ? 'bg-green-50 border-green-200' : ($step['current'] ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-slate-200') }} border rounded-lg p-3">
                                            @if($step['completed'])
                                                <div class="text-sm text-green-800">Selesai</div>
                                            @elseif($step['current'])
                                                <div class="text-sm text-blue-800">Sedang Progress</div>
                                            @else
                                                <div class="text-sm text-slate-600">Belum tersedia</div>
                                            @endif
                                        </div>
                                        @if($step['current'])
                                            <a href="{{ route('materi.show', [$currentModule->id_modul ?? 0, $currentModule->id_materi]) }}" class="mt-3 inline-block bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
                                                Lanjutkan
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column - Activities & Quick Access -->
            <div class="space-y-8">
                <!-- Tugas Mendatang -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Tugas Mendatang</h3>
                        <p class="text-sm text-slate-600">Deadline terdekat</p>
                    </div>
                    <div class="p-6">
                        @if(count($tugasMendatang) > 0)
                            <div class="space-y-4">
                                @foreach($tugasMendatang as $tugas)
                                    <div class="border {{ $tugas['priority'] == 'urgent' ? 'border-red-200 bg-red-50' : ($tugas['priority'] == 'soon' ? 'border-yellow-200 bg-yellow-50' : 'border-green-200 bg-green-50') }} rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-medium text-slate-900 text-sm">{{ $tugas['nama'] }}</h4>
                                            <span class="text-xs {{ $tugas['priority'] == 'urgent' ? 'bg-red-100 text-red-800' : ($tugas['priority'] == 'soon' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }} px-2 py-1 rounded-full">
                                                {{ $tugas['priority'] == 'urgent' ? 'Urgent' : ($tugas['priority'] == 'soon' ? 'Soon' : 'Normal') }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-600 mb-2">{{ $tugas['mata_pelajaran'] }} - {{ $tugas['guru'] }}</p>
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs {{ $tugas['priority'] == 'urgent' ? 'text-red-600' : ($tugas['priority'] == 'soon' ? 'text-yellow-600' : 'text-green-600') }} font-medium">{{ $tugas['deadline'] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-slate-500">
                                <p class="text-sm">Tidak ada tugas mendatang</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Aksi Cepat</h3>
                        <p class="text-sm text-slate-600">Shortcut pembelajaran</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <button onclick="alert('Silakan pilih mata pelajaran untuk melihat semua modul')" class="w-full flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium text-slate-900">Semua Mata Pelajaran</div>
                                    <div class="text-xs text-slate-600">Lihat daftar lengkap</div>
                                </div>
                            </button>

                            <button onclick="alert('Fitur Riwayat Nilai segera hadir!')" class="w-full flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium text-slate-900">Riwayat Nilai</div>
                                    <div class="text-xs text-slate-600">Cek progress akademik</div>
                                </div>
                            </button>

                            <button onclick="alert('Fitur Forum Diskusi segera hadir!')" class="w-full flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <div class="text-sm font-medium text-slate-900">Forum Diskusi</div>
                                    <div class="text-xs text-slate-600">Tanya jawab dengan teman</div>
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
// Progress animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate progress bars
    const progressBars = document.querySelectorAll('[style*="width:"]');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.transition = 'width 1s ease-out';
            bar.style.width = width;
        }, 500);
    });
});

// Subject card interactions
document.querySelectorAll('.subject-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(-4px) scale(1)';
    });
});
</script>
@endsection