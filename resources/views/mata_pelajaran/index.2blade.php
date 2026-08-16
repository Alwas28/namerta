@extends('layouts.home')

@section('css_tambahan')
<style>
    .subject-card {
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        border: 1px solid rgba(148, 163, 184, 0.1);
        backdrop-filter: blur(10px);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        position: relative;
        overflow: hidden;
    }
    
    .subject-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #3b82f6, transparent);
        transition: left 0.5s;
    }
    
    .subject-card:hover::before {
        left: 100%;
    }
    
    .subject-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
    }
    
    .progress-wave {
        background: linear-gradient(45deg, #3b82f6, #8b5cf6, #06b6d4);
        background-size: 400% 400%;
        animation: gradient-shift 3s ease infinite;
    }
    
    @keyframes gradient-shift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .floating-orb {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(45deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
        animation: float 6s ease-in-out infinite;
    }
    
    .floating-orb:nth-child(1) {
        width: 100px;
        height: 100px;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }
    
    .floating-orb:nth-child(2) {
        width: 150px;
        height: 150px;
        top: 50%;
        right: 10%;
        animation-delay: 2s;
    }
    
    .floating-orb:nth-child(3) {
        width: 80px;
        height: 80px;
        bottom: 20%;
        left: 50%;
        animation-delay: 4s;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        33% { transform: translateY(-20px) rotate(120deg); }
        66% { transform: translateY(10px) rotate(240deg); }
    }
    
    .module-preview {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
    }
    
    .module-preview:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: translateX(8px);
    }
    
    .interactive-stats {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
        border: 1px solid rgba(59, 130, 246, 0.2);
        transition: all 0.3s ease;
    }
    
    .interactive-stats:hover {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
        transform: scale(1.05);
    }
    
    .morphing-button {
        background: linear-gradient(45deg, #3b82f6, #8b5cf6);
        background-size: 200% 200%;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .morphing-button::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .morphing-button:hover {
        background-position: 100% 0;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.4);
    }
    
    .morphing-button:hover::after {
        width: 300px;
        height: 300px;
    }
    
    .glassmorphism {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    
    .role-indicator {
        background: linear-gradient(45deg, #10b981, #059669);
        animation: pulse-glow 2s infinite;
    }
    
    .role-indicator.student {
        background: linear-gradient(45deg, #3b82f6, #2563eb);
    }
    
    @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 30px rgba(16, 185, 129, 0.8); }
    }
    
    .interactive-search {
        background: linear-gradient(145deg, #ffffff, #f1f5f9);
        border: 2px solid transparent;
        background-clip: padding-box;
        transition: all 0.3s ease;
    }
    
    .interactive-search:focus {
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), 0 10px 25px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .filter-chip {
        background: linear-gradient(45deg, #f1f5f9, #e2e8f0);
        border: 1px solid rgba(148, 163, 184, 0.3);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .filter-chip:hover, .filter-chip.active {
        background: linear-gradient(45deg, #3b82f6, #2563eb);
        color: white;
        transform: scale(1.05);
    }
    
    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    
    .parallax-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }
    
    .parallax-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        animation: slide 20s linear infinite;
    }
    
    @keyframes slide {
        0% { transform: translateX(0); }
        100% { transform: translateX(-60px); }
    }
</style>
@endsection

@section('konten')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-purple-50 relative overflow-hidden">
    <!-- Floating Background Orbs -->
    <div class="floating-orb"></div>
    <div class="floating-orb"></div>
    <div class="floating-orb"></div>
    
    <!-- Hero Header with Parallax -->
    <div class="parallax-bg text-white relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 relative z-10">
            <div class="text-center">
                <div class="inline-flex items-center glassmorphism rounded-full px-6 py-2 mb-6">
                    <div class="role-indicator {{ $userRole === 'guru' ? '' : 'student' }} w-3 h-3 rounded-full mr-3"></div>
                    <span class="text-white font-medium">
                        {{ $userRole === 'guru' ? 'Dashboard Guru' : 'Portal Pembelajaran Siswa' }}
                    </span>
                </div>
                
                <h1 class="text-4xl md:text-6xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-white to-blue-100">
                    Mata Pelajaran
                </h1>
                <p class="text-xl text-blue-100 max-w-2xl mx-auto leading-relaxed">
                    @if($userRole === 'guru')
                        Kelola dan pantau pembelajaran dengan tools interaktif yang canggih
                    @else
                        Jelajahi dunia pengetahuan dengan pengalaman belajar yang menyenangkan
                    @endif
                </p>
                
                @if($currentTA)
                <div class="mt-8 glassmorphism rounded-2xl p-6 max-w-md mx-auto">
                    <div class="text-blue-100 text-sm">Tahun Ajaran Aktif</div>
                    <div class="text-2xl font-bold">{{ $currentTA->nama_ta }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 relative z-10">
        <!-- Interactive Search & Filter Section -->
        <div class="glassmorphism rounded-3xl p-8 mb-12 backdrop-blur-xl">
            <div class="flex flex-col lg:flex-row items-center justify-between space-y-6 lg:space-y-0 lg:space-x-8">
                <!-- Search Bar -->
                <div class="relative flex-1 max-w-2xl">
                    <input type="text" id="subjectSearch" placeholder="Cari mata pelajaran atau modul..." 
                           class="interactive-search w-full pl-12 pr-6 py-4 rounded-2xl text-lg focus:outline-none">
                    <svg class="w-6 h-6 text-slate-400 absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    
                    <!-- Search suggestions -->
                    <div id="searchSuggestions" class="hidden absolute top-full left-0 right-0 mt-2 glassmorphism rounded-xl shadow-lg max-h-64 overflow-y-auto z-20">
                        <!-- Dynamic suggestions will be inserted here -->
                    </div>
                </div>
                
                <!-- Filter Chips -->
                <div class="flex flex-wrap gap-3">
                    <div class="filter-chip active rounded-full px-6 py-2 text-sm font-medium" data-filter="all">
                        Semua
                    </div>
                    <div class="filter-chip rounded-full px-6 py-2 text-sm font-medium" data-filter="in-progress">
                        Sedang Dipelajari
                    </div>
                    <div class="filter-chip rounded-full px-6 py-2 text-sm font-medium" data-filter="completed">
                        Selesai
                    </div>
                    @if($userRole === 'guru')
                    <div class="filter-chip rounded-full px-6 py-2 text-sm font-medium" data-filter="needs-attention">
                        Perlu Perhatian
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($message))
            <!-- Empty State with Animation -->
            <div class="text-center py-20">
                <div class="relative">
                    <svg class="w-32 h-32 text-slate-300 mx-auto mb-8 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 w-32 h-32 bg-gradient-to-r from-blue-400 to-purple-400 rounded-full opacity-20 animate-ping"></div>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-4">{{ $message }}</h3>
                <p class="text-slate-600 text-lg mb-8 max-w-md mx-auto">
                    @if($userRole === 'guru')
                        Hubungi administrator untuk mendapatkan akses mengajar dan mulai berbagi ilmu.
                    @else
                        Hubungi administrator untuk mendaftar ke kelas dan memulai perjalanan belajar.
                    @endif
                </p>
                <button class="morphing-button text-white px-8 py-4 rounded-2xl font-bold text-lg relative z-10">
                    Hubungi Admin
                </button>
            </div>
        @else
            <!-- Interactive Subjects Grid -->
            <div id="subjectsGrid" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                @foreach($subjects as $index => $subject)
                <div class="subject-card rounded-3xl p-8 group" 
                     data-aos="fade-up" 
                     data-aos-delay="{{ $index * 100 }}"
                     data-filter="{{ $userRole === 'siswa' ? ($subject->progress >= 100 ? 'completed' : ($subject->progress > 0 ? 'in-progress' : 'not-started')) : 'all' }}">
                    
                    <!-- Card Header with Interactive Elements -->
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-slate-800 mb-2 group-hover:text-blue-600 transition-colors">
                                {{ $subject->nama_mata_pelajaran }}
                            </h3>
                            <p class="text-slate-600 text-sm">
                                @if($userRole === 'guru')
                                    {{ $subject->classes }} • {{ $subject->student_count }} siswa
                                @else
                                    {{ $subject->teacher_name ?? 'Guru belum ditentukan' }}
                                @endif
                            </p>
                        </div>
                        
                        <!-- Animated Progress Circle -->
                        <div class="relative">
                            <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-slate-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                <path class="progress-wave" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none" 
                                      stroke-dasharray="{{ $userRole === 'guru' ? $subject->avg_progress : $subject->progress ?? 0 }}, 100" 
                                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-lg font-bold text-slate-700">
                                    {{ $userRole === 'guru' ? $subject->avg_progress : $subject->progress ?? 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Interactive Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="interactive-stats rounded-2xl p-4 text-center">
                            <div class="text-2xl font-bold text-slate-800">{{ $subject->module_count }}</div>
                            <div class="text-sm text-slate-600">Modul</div>
                        </div>
                        <div class="interactive-stats rounded-2xl p-4 text-center">
                            @if($userRole === 'guru')
                                <div class="text-2xl font-bold text-slate-800">{{ $subject->student_count }}</div>
                                <div class="text-sm text-slate-600">Siswa Aktif</div>
                            @else
                                <div class="text-2xl font-bold text-slate-800">{{ $subject->progress ?? 0 }}%</div>
                                <div class="text-sm text-slate-600">Progress</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Mini Module Preview -->
                    @if($subject->module_count > 0)
                    <div class="space-y-2 mb-6">
                        <div class="text-sm font-medium text-slate-700 mb-3">Preview Modul:</div>
                        @for($i = 0; $i < min(3, $subject->module_count); $i++)
                        <div class="module-preview rounded-xl p-3 flex items-center">
                            <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                            <span class="text-sm text-slate-600">Modul {{ $i + 1 }}</span>
                            @if($userRole === 'siswa')
                                <div class="ml-auto text-xs text-slate-500">{{ rand(60, 100) }}%</div>
                            @endif
                        </div>
                        @endfor
                        
                        @if($subject->module_count > 3)
                        <div class="text-xs text-slate-500 text-center py-2">
                            +{{ $subject->module_count - 3 }} modul lainnya
                        </div>
                        @endif
                    </div>
                    @endif
                    
                    <!-- Last Activity with Real-time Feel -->
                    <div class="flex items-center text-sm text-slate-500 mb-6">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                        <span>Aktivitas terakhir: {{ $subject->last_activity }}</span>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button onclick="openSubject('{{ $subject->id_mata_pelajaran }}')" 
                                class="morphing-button flex-1 text-white py-3 px-6 rounded-xl font-semibold relative z-10 group-hover:scale-105 transition-transform">
                            @if($userRole === 'guru')
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Kelola
                            @else
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Belajar
                            @endif
                        </button>
                        
                        <button onclick="quickPreview('{{ $subject->id_mata_pelajaran }}')" 
                                class="glassmorphism p-3 rounded-xl hover:scale-110 transition-all group">
                            <svg class="w-5 h-5 text-slate-600 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
        
        <!-- Floating Quick Actions for Teachers -->
        @if($userRole === 'guru' && count($subjects) > 0)
        <div class="fixed bottom-8 right-8 z-50">
            <div class="relative">
                <!-- Main FAB -->
                <button id="fabMain" onclick="toggleFAB()" class="w-16 h-16 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full shadow-2xl hover:scale-110 transition-all flex items-center justify-center">
                    <svg id="fabIcon" class="w-6 h-6 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </button>
                
                <!-- Sub FABs -->
                <div id="fabMenu" class="absolute bottom-20 right-0 space-y-3 opacity-0 transform scale-0 transition-all">
                    <button onclick="createModule()" class="w-12 h-12 bg-blue-500 text-white rounded-full shadow-lg hover:scale-110 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </button>
                    
                    <button onclick="viewAssignments()" class="w-12 h-12 bg-orange-500 text-white rounded-full shadow-lg hover:scale-110 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </button>
                    
                    <button onclick="viewReports()" class="w-12 h-12 bg-purple-500 text-white rounded-full shadow-lg hover:scale-110 transition-all flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Quick Preview Modal -->
    <div id="quickPreviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="glassmorphism rounded-3xl max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h3 id="previewTitle" class="text-2xl font-bold text-slate-800">Preview Mata Pelajaran</h3>
                    <button onclick="closeQuickPreview()" class="p-2 hover:bg-slate-200 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div id="previewContent" class="space-y-6">
                    <!-- Content will be loaded dynamically -->
                    <div class="loading-skeleton h-32 rounded-2xl"></div>
                    <div class="loading-skeleton h-24 rounded-2xl"></div>
                    <div class="loading-skeleton h-40 rounded-2xl"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_tambahan')
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<script>
// Initialize AOS animations
AOS.init({
    duration: 800,
    easing: 'ease-out-cubic',
    once: true
});

// Enhanced subject cards interactions
document.addEventListener('DOMContentLoaded', function() {
    initializeInteractivity();
    setupSearch();
    setupFilters();
    animateProgressCircles();
});

function initializeInteractivity() {
    // Add hover sound effects (optional)
    const cards = document.querySelectorAll('.subject-card');
    cards.forEach((card, index) => {
        card.addEventListener('mouseenter', function() {
            this.style.setProperty('--hover-delay', `${index * 0.1}s`);
        });
        
        // Add ripple effect on click
        card.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;
            
            const ripple = document.createElement('div');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Add ripple CSS
    if (!document.querySelector('#ripple-styles')) {
        const style = document.createElement('style');
        style.id = 'ripple-styles';
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(59, 130, 246, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

function setupSearch() {
    const searchInput = document.getElementById('subjectSearch');
    const searchSuggestions = document.getElementById('searchSuggestions');
    let searchTimeout;
    
    // Mock suggestions data
    const suggestions = [
        'Matematika', 'Fisika', 'Kimia', 'Biologi', 'Bahasa Indonesia',
        'Bahasa Inggris', 'Sejarah', 'Geografi', 'Ekonomi', 'Sosiologi'
    ];
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        const query = e.target.value.toLowerCase().trim();
        
        if (query.length === 0) {
            searchSuggestions.classList.add('hidden');
            filterSubjects('');
            return;
        }
        
        // Show suggestions after typing
        searchTimeout = setTimeout(() => {
            const filtered = suggestions.filter(s => s.toLowerCase().includes(query));
            showSuggestions(filtered, query);
            filterSubjects(query);
        }, 300);
    });
    
    searchInput.addEventListener('focus', function() {
        if (this.value.trim()) {
            const query = this.value.toLowerCase().trim();
            const filtered = suggestions.filter(s => s.toLowerCase().includes(query));
            showSuggestions(filtered, query);
        }
    });
    
    // Close suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.classList.add('hidden');
        }
    });
}

function showSuggestions(suggestions, query) {
    const container = document.getElementById('searchSuggestions');
    
    if (suggestions.length === 0) {
        container.classList.add('hidden');
        return;
    }
    
    container.innerHTML = suggestions.map(suggestion => {
        const highlighted = suggestion.replace(new RegExp(query, 'gi'), `<mark class="bg-blue-100 text-blue-800">            <div class="p-8">
                <div class="flex justify-between items</mark>`);
        return `
            <div class="suggestion-item px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-slate-100 last:border-b-0" 
                 onclick="selectSuggestion('${suggestion}')">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-slate-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>${highlighted}</span>
                </div>
            </div>
        `;
    }).join('');
    
    container.classList.remove('hidden');
}

function selectSuggestion(suggestion) {
    document.getElementById('subjectSearch').value = suggestion;
    document.getElementById('searchSuggestions').classList.add('hidden');
    filterSubjects(suggestion.toLowerCase());
}

function filterSubjects(query) {
    const cards = document.querySelectorAll('.subject-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const teacher = card.querySelector('p')?.textContent.toLowerCase() || '';
        
        if (!query || title.includes(query) || teacher.includes(query)) {
            card.parentElement.style.display = 'block';
            card.style.animation = 'fadeInScale 0.5s ease-out forwards';
            visibleCount++;
        } else {
            card.parentElement.style.display = 'none';
        }
    });
    
    // Show no results message
    showNoResults(visibleCount === 0 && query.length > 0);
}

function showNoResults(show) {
    let noResultsElement = document.getElementById('noResults');
    
    if (show && !noResultsElement) {
        const grid = document.getElementById('subjectsGrid');
        noResultsElement = document.createElement('div');
        noResultsElement.id = 'noResults';
        noResultsElement.className = 'col-span-full text-center py-16';
        noResultsElement.innerHTML = `
            <svg class="w-24 h-24 text-slate-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-xl font-semibold text-slate-700 mb-2">Tidak ada hasil ditemukan</h3>
            <p class="text-slate-500">Coba gunakan kata kunci yang berbeda</p>
        `;
        grid.appendChild(noResultsElement);
    } else if (!show && noResultsElement) {
        noResultsElement.remove();
    }
}

function setupFilters() {
    const filterChips = document.querySelectorAll('.filter-chip');
    
    filterChips.forEach(chip => {
        chip.addEventListener('click', function() {
            // Remove active from all chips
            filterChips.forEach(c => c.classList.remove('active'));
            // Add active to clicked chip
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            applyFilter(filter);
        });
    });
}

function applyFilter(filter) {
    const cards = document.querySelectorAll('.subject-card');
    let visibleCount = 0;
    
    cards.forEach((card, index) => {
        const shouldShow = filter === 'all' || card.dataset.filter === filter;
        
        if (shouldShow) {
            card.parentElement.style.display = 'block';
            card.style.animation = `fadeInScale 0.5s ease-out ${index * 0.1}s forwards`;
            visibleCount++;
        } else {
            card.parentElement.style.display = 'none';
        }
    });
    
    showNoResults(visibleCount === 0);
}

function animateProgressCircles() {
    const progressPaths = document.querySelectorAll('.progress-wave');
    
    progressPaths.forEach((path, index) => {
        const dashArray = path.getAttribute('stroke-dasharray');
        path.setAttribute('stroke-dasharray', '0, 100');
        
        setTimeout(() => {
            path.style.transition = 'stroke-dasharray 2s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            path.setAttribute('stroke-dasharray', dashArray);
        }, index * 200);
    });
}

// FAB Functions
let fabOpen = false;

function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    const fabIcon = document.getElementById('fabIcon');
    
    fabOpen = !fabOpen;
    
    if (fabOpen) {
        fabMenu.classList.remove('opacity-0', 'scale-0');
        fabMenu.classList.add('opacity-100', 'scale-100');
        fabIcon.style.transform = 'rotate(45deg)';
    } else {
        fabMenu.classList.add('opacity-0', 'scale-0');
        fabMenu.classList.remove('opacity-100', 'scale-100');
        fabIcon.style.transform = 'rotate(0deg)';
    }
}

// Subject interaction functions
function openSubject(subjectId) {
    // Add loading state
    showLoadingState(true);
    
    setTimeout(() => {
        showToast({
            success: true,
            message: 'Membuka mata pelajaran...'
        });
        // window.location.href = `/subjects/${subjectId}`;
        showLoadingState(false);
    }, 1000);
}

function quickPreview(subjectId) {
    const modal = document.getElementById('quickPreviewModal');
    const content = document.getElementById('previewContent');
    
    modal.classList.remove('hidden');
    
    // Simulate loading preview data
    setTimeout(() => {
        content.innerHTML = `
            <div class="space-y-6">
                <div class="glassmorphism rounded-2xl p-6">
                    <h4 class="font-bold text-lg mb-3">Overview</h4>
                    <p class="text-slate-600">Mata pelajaran ini mencakup konsep-konsep fundamental yang akan membantu pemahaman Anda.</p>
                </div>
                
                <div class="glassmorphism rounded-2xl p-6">
                    <h4 class="font-bold text-lg mb-3">Modul Tersedia</h4>
                    <div class="space-y-2">
                        <div class="flex items-center p-3 bg-white/50 rounded-xl">
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-3"></div>
                            <span>Modul 1: Pengenalan Dasar</span>
                            <span class="ml-auto text-sm text-green-600">Selesai</span>
                        </div>
                        <div class="flex items-center p-3 bg-white/50 rounded-xl">
                            <div class="w-3 h-3 bg-blue-400 rounded-full mr-3"></div>
                            <span>Modul 2: Konsep Lanjutan</span>
                            <span class="ml-auto text-sm text-blue-600">Sedang Progress</span>
                        </div>
                        <div class="flex items-center p-3 bg-white/50 rounded-xl">
                            <div class="w-3 h-3 bg-slate-400 rounded-full mr-3"></div>
                            <span>Modul 3: Aplikasi Praktis</span>
                            <span class="ml-auto text-sm text-slate-600">Belum Mulai</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="openSubject('${subjectId}')" class="morphing-button flex-1 text-white py-3 px-6 rounded-xl font-semibold">
                        Mulai Belajar
                    </button>
                    <button onclick="closeQuickPreview()" class="glassmorphism px-6 py-3 rounded-xl">
                        Tutup
                    </button>
                </div>
            </div>
        `;
    }, 800);
}

function closeQuickPreview() {
    document.getElementById('quickPreviewModal').classList.add('hidden');
}

// Teacher functions
function createModule() {
    showToast({
        success: true,
        message: 'Membuka wizard pembuatan modul...'
    });
}

function viewAssignments() {
    showToast({
        success: true,
        message: 'Membuka daftar tugas pending...'
    });
}

function viewReports() {
    showToast({
        success: true,
        message: 'Membuka dashboard analytics...'
    });
}

// Loading state management
function showLoadingState(show) {
    const cards = document.querySelectorAll('.subject-card');
    
    cards.forEach(card => {
        if (show) {
            card.style.opacity = '0.7';
            card.style.pointerEvents = 'none';
        } else {
            card.style.opacity = '1';
            card.style.pointerEvents = 'auto';
        }
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // ESC to close modal
    if (e.key === 'Escape') {
        closeQuickPreview();
        document.getElementById('searchSuggestions').classList.add('hidden');
    }
    
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('subjectSearch').focus();
    }
});

// Add CSS animations
const additionalStyles = document.createElement('style');
additionalStyles.textContent = `
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.8) translateY(20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    .suggestion-item {
        animation: slideInRight 0.3s ease-out forwards;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    mark {
        border-radius: 4px;
        padding: 2px 4px;
    }
`;
document.head.appendChild(additionalStyles);

// Initialize real-time updates (for teacher dashboard)
@if($userRole === 'guru')
setInterval(() => {
    // Simulate real-time updates
    const activityIndicators = document.querySelectorAll('.animate-pulse');
    activityIndicators.forEach(indicator => {
        indicator.style.opacity = '0.5';
        setTimeout(() => {
            indicator.style.opacity = '1';
        }, 200);
    });
}, 30000);
@endif
</script>
@endsection