@extends('layouts.home')

@section('css_tambahan')
<style>
    .stat-card {
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .activity-item {
        transition: all 0.3s ease;
    }
    
    .activity-item:hover {
        transform: translateX(4px);
        background-color: #f8fafc;
    }
    
    .status-indicator {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .progress-circle {
        transform: rotate(-90deg);
    }
</style>
@endsection

@section('konten')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Dashboard Admin</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        @if($currentTA)
                            Tahun Ajaran: <span class="font-semibold text-edu-blue">{{ $currentTA->nama_ta }}</span>
                        @else
                            <span class="text-amber-600">⚠ Belum ada tahun ajaran aktif</span>
                        @endif
                    </p>
                </div>
                
                <div class="mt-4 lg:mt-0 flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <div class="text-xs text-slate-500">
                        Last updated: {{ now()->format('d M Y, H:i') }}
                    </div>
                    <button onclick="location.reload()" class="inline-flex items-center px-3 py-1.5 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Statistics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="stat-card bg-gradient-to-r from-edu-blue to-blue-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Users</p>
                        <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_users']) }}</p>
                        <p class="text-blue-100 text-xs mt-2">{{ $stats['active_users'] }} aktif</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="stat-card bg-gradient-to-r from-edu-green to-green-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Siswa</p>
                        <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_siswa']) }}</p>
                        <p class="text-green-100 text-xs mt-2">{{ $stats['enrolled_students'] }} terdaftar</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Teachers -->
            <div class="stat-card bg-gradient-to-r from-edu-orange to-orange-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Total Guru</p>
                        <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_guru']) }}</p>
                        <p class="text-orange-100 text-xs mt-2">Pengajar aktif</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Classes -->
            <div class="stat-card bg-gradient-to-r from-edu-purple to-purple-600 rounded-xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Total Kelas</p>
                        <p class="text-3xl font-bold mt-2">{{ number_format($stats['total_kelas']) }}</p>
                        <p class="text-purple-100 text-xs mt-2">{{ $stats['current_year_classes'] }} tahun ini</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Charts & Analytics -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Academic Progress -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Progress Akademik</h3>
                        <p class="text-sm text-slate-600">Status kelas dan mata pelajaran</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Completion Rate -->
                            <div class="text-center">
                                <div class="relative w-24 h-24 mx-auto mb-4">
                                    <svg class="progress-circle w-24 h-24" viewBox="0 0 36 36">
                                        <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                        <path class="text-edu-green" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none" stroke-dasharray="{{ $academicStats['completion_rate'] }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-lg font-bold text-slate-900">{{ $academicStats['completion_rate'] }}%</span>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-slate-900">Kelas Lengkap</p>
                                <p class="text-xs text-slate-600">Memiliki mata pelajaran</p>
                            </div>

                            <!-- Classes with Subjects -->
                            <div class="text-center">
                                <div class="bg-edu-blue/10 rounded-lg p-4 mb-4">
                                    <div class="text-2xl font-bold text-edu-blue">{{ $academicStats['kelas_with_mapel'] }}</div>
                                </div>
                                <p class="text-sm font-medium text-slate-900">Kelas dengan Mapel</p>
                                <p class="text-xs text-slate-600">Sudah dikonfigurasi</p>
                            </div>

                            <!-- Classes without Subjects -->
                            <div class="text-center">
                                <div class="bg-amber-100 rounded-lg p-4 mb-4">
                                    <div class="text-2xl font-bold text-amber-600">{{ $academicStats['kelas_without_mapel'] }}</div>
                                </div>
                                <p class="text-sm font-medium text-slate-900">Kelas Tanpa Mapel</p>
                                <p class="text-xs text-slate-600">Perlu konfigurasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Distribution -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Distribusi Pengguna</h3>
                        <p class="text-sm text-slate-600">Berdasarkan status dan peran</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            @foreach($userStats['by_status'] ?? [] as $status => $count)
                            <div class="text-center p-4 bg-slate-50 rounded-lg">
                                <div class="text-2xl font-bold text-slate-900">{{ $count }}</div>
                                <div class="text-sm text-slate-600 capitalize">{{ ucfirst($status) }}</div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                    <div>
                                        <div class="font-semibold text-green-800">{{ $userStats['active_count'] }}</div>
                                        <div class="text-sm text-green-600">Pengguna Aktif</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                    <div>
                                        <div class="font-semibold text-red-800">{{ $userStats['inactive_count'] }}</div>
                                        <div class="text-sm text-red-600">Pengguna Nonaktif</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Activities & System Status -->
            <div class="space-y-8">
                <!-- Recent Activities -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Aktivitas Terbaru</h3>
                        <p class="text-sm text-slate-600">Update sistem terkini</p>
                    </div>
                    <div class="p-6">
                        @if(empty($recentActivities))
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-slate-500 text-sm">Belum ada aktivitas</p>
                        </div>
                        @else
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach($recentActivities as $activity)
                            <div class="activity-item flex items-start space-x-3 p-3 rounded-lg border border-slate-100">
                                <div class="flex-shrink-0">
                                    @if($activity['icon'] === 'user-plus')
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                                        </svg>
                                    </div>
                                    @elseif($activity['icon'] === 'book')
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    @else
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-slate-900">{{ $activity['message'] }}</p>
                                    <p class="text-xs text-slate-500 mt-1">{{ $activity['time'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <!-- System Status -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Status Sistem</h3>
                        <p class="text-sm text-slate-600">Kesehatan sistem</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($systemStatus as $component => $status)
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                <div class="flex items-center">
                                    @if($status['status'] === 'healthy')
                                    <div class="w-3 h-3 bg-green-500 rounded-full status-indicator mr-3"></div>
                                    @elseif($status['status'] === 'warning')
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full status-indicator mr-3"></div>
                                    @else
                                    <div class="w-3 h-3 bg-red-500 rounded-full status-indicator mr-3"></div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-slate-900 capitalize">{{ str_replace('_', ' ', $component) }}</div>
                                        <div class="text-xs text-slate-600">{{ $status['message'] }}</div>
                                    </div>
                                </div>
                                <div class="text-xs px-2 py-1 rounded-full 
                                    @if($status['status'] === 'healthy') bg-green-100 text-green-800
                                    @elseif($status['status'] === 'warning') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($status['status']) }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200">
                    <div class="p-6 border-b border-slate-200">
                        <h3 class="text-lg font-semibold text-slate-900">Aksi Cepat</h3>
                        <p class="text-sm text-slate-600">Shortcut untuk tugas umum</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('users.index') }}" class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-slate-900">Kelola Users</div>
                                    <div class="text-xs text-slate-600">{{ $quickActionsData['inactive_users'] }} perlu aktivasi</div>
                                </div>
                            </a>

                            <a href="{{ route('kelas-mapel.index') }}" class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-slate-900">Setup Kelas</div>
                                    <div class="text-xs text-slate-600">{{ $quickActionsData['kelas_without_subjects'] }} perlu mata pelajaran</div>
                                </div>
                            </a>

                            <button onclick="alert('Fitur Tahun Ajaran akan segera tersedia')" class="w-full flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 text-left">
                                    <div class="text-sm font-medium text-slate-900">Tahun Ajaran</div>
                                    <div class="text-xs text-slate-600">Kelola periode akademik</div>
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
// Enhanced card hover effects
document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

document.querySelectorAll('.activity-item').forEach(item => {
    item.addEventListener('mouseenter', function() {
        this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
    });
    
    item.addEventListener('mouseleave', function() {
        this.style.boxShadow = '';
    });
});
</script>
@endsection