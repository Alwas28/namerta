<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    

    <title>Namerta - Platform Pembelajaran Online Terdepan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'edu-blue': '#2563eb',
                        'edu-green': '#10b981',
                        'edu-orange': '#f59e0b',
                        'edu-purple': '#8b5cf6',
                        'edu-teal': '#14b8a6',
                        'edu-red': '#ef4444'
                    },
                    fontFamily: {
                        'display': ['Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    @yield('css_tambahan')
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 border-b border-slate-200">
        <!-- Full width container on mobile, centered on desktop -->
        <div class="w-full lg:max-w-7xl lg:mx-auto px-3 sm:px-4 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20">
                <!-- Left Side - Logo & Menu -->
                <div class="flex items-center space-x-4 lg:space-x-8 min-w-0 flex-1">
                    <!-- Logo -->
                    <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-edu-blue to-edu-purple rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                            </svg>
                        </div>
                        <span class="text-lg sm:text-xl font-bold text-slate-900">NAMERTA</span>
                    </div>

                    <!-- Desktop Menu - Hidden on mobile -->
                    <div class="hidden lg:flex items-center space-x-1">
                        
                        @if(Auth::check())
                           <a href="{{ route('home') }}" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">
                                Home
                            </a>

                            

                            @if (Auth::user()->hasRole('siswa'))
                                 <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 text-edu-blue bg-edu-blue/10 rounded-lg font-medium transition-colors">
                                    Dashboard
                                </a>
                            @elseif (Auth::user()->hasRole('guru'))
                                <a href="{{ route('guru.dashboard') }}" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">
                                    Dashboard
                                </a>
                            @elseif (Auth::user()->hasRole('tendik'))
                                <a href="{{ route('tendik.dashboard') }}" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 text-edu-blue bg-edu-blue/10 rounded-lg font-medium transition-colors">
                                    Dashboard
                                </a>
                            
                            @endif
                            <a href="{{ route('courses.index') }}" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">
                                My Courses
                            </a>
                            
                            <!-- Administrator Dropdown - Only on desktop -->
                            @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))
                                <div class="relative">
                                    <button onclick="toggleAdminDropdown()" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors flex items-center">
                                        My Access
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                    
                                    <!-- Admin Dropdown Menu -->
                                    <div id="adminDropdown" class="hidden absolute left-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50">
                                        <!-- User Management -->
                                        <div class="relative">
                                            <button onclick="toggleSubmenu('userManagement')" class="w-full flex items-center justify-between px-4 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                                    </svg>
                                                    User Management
                                                </span>
                                                <svg class="w-3 h-3 transform transition-transform" id="arrow-userManagement" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- User Management Submenu -->
                                            <div id="userManagement" class="hidden ml-8 border-l-2 border-slate-200">
                                                <a href="{{ route('users.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">User</a>
                                                <a href="/roles" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Roles</a>
                                            </div>
                                        </div>

                                        <!-- Course Management -->
                                        <div class="relative">
                                            <button onclick="toggleSubmenu('courseManagement')" class="w-full flex items-center justify-between px-4 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                    </svg>
                                                    Master Data
                                                </span>
                                                <svg class="w-3 h-3 transform transition-transform" id="arrow-courseManagement" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Course Management Submenu -->
                                            <div id="courseManagement" class="hidden ml-8 border-l-2 border-slate-200">
                                                {{-- <div class="relative">
                                                    <button onclick="toggleSubmenu('courses')" class="w-full flex items-center justify-between px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                                                        <span>Courses</span>
                                                        <svg class="w-3 h-3 transform transition-transform" id="arrow-courses" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                    <!-- Courses Level 3 -->
                                                    <div id="courses" class="hidden ml-4 border-l border-slate-200">
                                                        <a href="#" class="block px-4 py-2 text-xs text-slate-500 hover:bg-slate-50 transition-colors">All Courses</a>
                                                        <a href="#" class="block px-4 py-2 text-xs text-slate-500 hover:bg-slate-50 transition-colors">Create Course</a>
                                                        <a href="#" class="block px-4 py-2 text-xs text-slate-500 hover:bg-slate-50 transition-colors">Course Categories</a>
                                                        <a href="#" class="block px-4 py-2 text-xs text-slate-500 hover:bg-slate-50 transition-colors">Course Analytics</a>
                                                    </div>
                                                </div> --}}
                                                
                                                <a href="/tahun-ajaran" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Tahun Pelajaran</a>
                                                <a href="/mata-pelajaran" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Mata Pelajaran</a>
                                                <a href="/kelas" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Kelas</a>
                                            </div>
                                        </div>

                                        <!-- System Settings -->
                                        <div class="relative">
                                            <button onclick="toggleSubmenu('systemSettings')" class="w-full flex items-center justify-between px-4 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    System Settings
                                                </span>
                                                <svg class="w-3 h-3 transform transition-transform" id="arrow-systemSettings" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- System Settings Submenu -->
                                            <div id="systemSettings" class="hidden ml-8 border-l-2 border-slate-200">
                                                <a href="{{ route('kelas-ta.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Kelas di Tahun Akademik</a>
                                                <a href="{{ route('siswa-kelas.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Enroll Student di Kelas</a>
                                                <a href="{{ route('kelas-mapel.index') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Enroll Mata Pelajaran di Kelas</a>
                                            </div>
                                        </div>

                                        <div class="border-t border-slate-100 mt-2 pt-2">
                                            <a href="#" class="block px-4 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                                    </svg>
                                                    Reports & Analytics
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                             <a href="{{ route('home') }}" class="px-4 py-2 text-edu-blue bg-edu-blue/10 rounded-lg font-medium transition-colors">
                                Home
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Right Side - Search & Auth -->
                <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
                    <!-- Search Bar - Hidden on small mobile -->
                    <div class="hidden sm:block relative">
                        <input type="text" placeholder="Cari kursus..." class="w-48 lg:w-64 pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-edu-blue/20 focus:border-edu-blue transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    @if (Auth::check())
                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <button onclick="toggleProfileDropdown()" class="flex items-center space-x-2 sm:space-x-3 p-1 sm:p-2 rounded-lg hover:bg-slate-100 transition-colors">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 bg-gradient-to-br from-edu-green to-edu-teal rounded-full flex items-center justify-center text-white font-semibold text-xs sm:text-sm">
                                    @php
                                        $nama = Auth::user()->profile->nama ?? 'Pengguna';
                                        $inisial = collect(explode(' ', $nama))
                                                    ->filter()
                                                    ->take(2)
                                                    ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                                                    ->join('');
                                    @endphp

                                    {{ $inisial }}

                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="text-sm font-semibold text-slate-900">{{ Auth::user()->profile->nama ?? 'Pengguna' }}</div>
                                    <div class="text-xs text-slate-500">{{ Auth::user()->profile->status ?? 'Administrator' }}</div>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Profile Dropdown Menu -->
                            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50">
                                <!-- Profile Info -->
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-edu-green to-edu-teal rounded-full flex items-center justify-center text-white font-semibold">
                                            @php
                                                $nama = Auth::user()->profile->nama ?? 'Pengguna';
                                                $inisial = collect(explode(' ', $nama))
                                                            ->filter()
                                                            ->take(2)
                                                            ->map(fn($n) => strtoupper(substr($n, 0, 1)))
                                                            ->join('');
                                            @endphp

                                            {{ $inisial }}

                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 whitespace-nowrap">{{ Auth::user()->profile->nama ?? 'Pengguna' }}</div>
                                            <div class="text-sm text-slate-500 whitespace-nowrap">{{ Auth::user()->username ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Menu Items -->
                                <div class="py-2">
                                    <a href="{{ route('profile.index') }}" class="w-full flex items-center px-4 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Profile
                                    </a>
                                    {{-- <a href="#" class="w-full flex items-center px-4 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        Profile Sekolah
                                    </a>
                                    <a href="#" class="w-full flex items-center px-4 py-2 text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="w-5 h-5 mr-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Pengaturan
                                    </a> --}}
                                </div>

                                <div class="border-t border-slate-100 pt-2">
                                    <button onclick="logout()" class="w-full flex items-center px-4 py-2 text-edu-red hover:bg-red-50 transition-colors">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- Auth Buttons -->
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <!-- Login Button -->
                            <button onclick="openLoginModal()" class="flex items-center space-x-1 sm:space-x-2 px-3 sm:px-4 py-2 text-slate-700 hover:text-edu-blue hover:bg-slate-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="text-sm sm:text-base font-medium">Masuk</span>
                            </button>

                            <!-- Register Button - Hidden on mobile -->
                            <button onclick="openRegisterModal()" class="hidden sm:flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-edu-blue to-edu-green text-white rounded-lg hover:shadow-lg transform hover:scale-105 transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="font-medium">Daftar</span>
                            </button>
                        </div>

                    @endif

                    <!-- Mobile Menu Button -->
                    <button class="lg:hidden p-1.5 sm:p-2 rounded-lg hover:bg-slate-100 transition-colors" onclick="toggleMobileMenu()">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu - Full width with dropdowns -->
            <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-200 py-4 -mx-3 sm:-mx-4 px-3 sm:px-4">
                <!-- Mobile Search -->
                <div class="mb-4 sm:hidden">
                    <div class="relative">
                        <input type="text" placeholder="Cari kursus..." class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-edu-blue/20 focus:border-edu-blue transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="flex flex-col space-y-1">
                    <a href="#home" class="px-4 py-2 text-edu-blue bg-edu-blue/10 rounded-lg font-medium">Home</a>
                    @if(Auth::check())
                        @if (Auth::user()->hasRole('siswa'))
                            <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 text-edu-blue bg-edu-blue/10 rounded-lg font-medium transition-colors">
                                Dashboard
                            </a>
                        @elseif (Auth::user()->hasRole('guru'))
                            <a href="{{ route('guru.dashboard') }}" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">
                                Dashboard
                            </a>
                        @elseif (Auth::user()->hasRole('tendik'))
                            <a href="{{ route('tendik.dashboard') }}" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('siswa.dashboard') }}" class="px-4 py-2 text-edu-blue bg-edu-blue/10 rounded-lg font-medium transition-colors">
                                Dashboard
                            </a>
                        
                        @endif
                    @endif


                    {{-- <a href="#dashboard" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">Dashboard</a> --}}
                    <a href="{{ route('courses.index') }}" class="px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors">My Courses</a>
                    
                    <!-- Mobile Administrator Dropdown -->

                    @if(Auth::user())
                    @if (Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))

                        <div class="space-y-1">
                            <button onclick="toggleMobileAdminDropdown()" class="w-full px-4 py-2 text-slate-600 hover:text-edu-blue hover:bg-slate-100 rounded-lg font-medium transition-colors flex items-center justify-between">
                                My Access
                                <svg class="w-4 h-4 transform transition-transform duration-200" id="mobileAdminArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <!-- Mobile Admin Submenu -->
                            <div id="mobileAdminDropdown" class="hidden ml-4 space-y-1 pl-4 border-l-2 border-slate-200">
                                <!-- User Management -->
                                <div class="space-y-1">
                                    <button onclick="toggleMobileSubmenu('mobileUserManagement')" class="w-full px-3 py-2 text-sm text-slate-600 hover:text-edu-blue hover:bg-slate-50 rounded-lg transition-colors flex items-center justify-between">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                            </svg>
                                            User Management
                                        </span>
                                        <svg class="w-3 h-3 transform transition-transform duration-200" id="arrow-mobileUserManagement" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                    
                                    <div id="mobileUserManagement" class="hidden ml-6 space-y-1 pl-3 border-l border-slate-200">
                                        <a href="{{ route('users.index') }}" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">User</a>
                                        <a href="/roles" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">Role</a>
                                    </div>
                                </div>

                                <!-- Course Management -->
                                <div class="space-y-1">
                                    <button onclick="toggleMobileSubmenu('mobileCourseManagement')" class="w-full px-3 py-2 text-sm text-slate-600 hover:text-edu-blue hover:bg-slate-50 rounded-lg transition-colors flex items-center justify-between">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                            Master Data
                                        </span>
                                        <svg class="w-3 h-3 transform transition-transform duration-200" id="arrow-mobileCourseManagement" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                    
                                    <div id="mobileCourseManagement" class="hidden ml-6 space-y-1 pl-3 border-l border-slate-200"> 
                                        <a href="/tahun-ajaran" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">Tahun Pelajaran</a>
                                        <a href="/mata-pelajaran" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">Mata Pelajaran</a>
                                        <a href="/kelas" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">Kelas</a>
                                    </div>
                                </div>

                                <!-- System Settings -->
                                <div class="space-y-1">
                                    <button onclick="toggleMobileSubmenu('mobileSystemSettings')" class="w-full px-3 py-2 text-sm text-slate-600 hover:text-edu-blue hover:bg-slate-50 rounded-lg transition-colors flex items-center justify-between">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            System Settings
                                        </span>
                                        <svg class="w-3 h-3 transform transition-transform duration-200" id="arrow-mobileSystemSettings" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                    
                                    <div id="mobileSystemSettings" class="hidden ml-6 space-y-1 pl-3 border-l border-slate-200">
                                        <a href="{{ route('kelas-ta.index') }}" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">Kelas di Tahun Akademik</a>
                                        <a href="{{ route('siswa-kelas.index') }}" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">Enroll Student di Kelas</a>
                                        <a href="{{ route('kelas-mapel.index') }}" class="block px-3 py-2 text-xs text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded transition-colors">Enroll Mata Pelajaran di Kelass</a>
                                    </div>
                                </div>

                                <!-- Reports & Analytics -->
                                <a href="#" class="block px-3 py-2 text-sm text-slate-600 hover:text-edu-blue hover:bg-slate-50 rounded-lg transition-colors">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        Reports & Analytics
                                    </span>
                                </a>
                            </div>
                        </div>
                    @endif
                    @endif
                    @if (!Auth::check())
                        <!-- Register button for mobile - shown in menu -->
                        <div class="pt-4 border-t border-slate-200 mt-4">
                            <button onclick="openRegisterModal()" class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-gradient-to-r from-edu-blue to-edu-green text-white rounded-lg hover:shadow-lg transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span class="font-medium">Daftar Sekarang</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Login Modal -->
        <div id="loginModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Masuk ke Namerta</h3>
                        <button onclick="closeLoginModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6">
                    <!-- Error Display -->
                    <div id="loginErrors" class="hidden mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <div class="font-medium text-sm">Login Gagal</div>
                                <ul id="errorList" class="text-sm mt-1 list-disc list-inside">
                                    <!-- Error messages will be inserted here -->
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Update bagian form login di home.blade.php -->
                    <!-- Cari form dengan id="loginForm" dan ganti dengan: -->

                    <form id="loginForm" action="{{ route('login') }}" method="POST">
                        @csrf
                        <!-- Error Display -->
                        <div id="loginErrors" class="hidden mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <p class="font-semibold mb-2">Login gagal:</p>
                            <ul id="errorList" class="list-disc list-inside text-sm">
                            </ul>
                        </div>
                        
                        <!-- Email Input -->
                        <div class="mb-4">
                            <label for="loginEmail" class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" 
                                    id="loginEmail" 
                                    name="email"
                                    class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="nama@email.com"
                                    required>
                            </div>
                        </div>
                        
                        <!-- Password Input -->
                        <div class="mb-4">
                            <label for="loginPassword" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                                <input type="password" 
                                    id="loginPassword"
                                    name="password" 
                                    class="pl-10 pr-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Masukkan password"
                                    required>
                                <button type="button" 
                                        onclick="togglePassword('loginPassword')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                            </label>
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-700">Lupa password?</a>
                        </div>
                        
                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            Masuk
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-slate-500">atau</span>
                        </div>
                    </div>

                    <!-- Social Login -->
                    <div class="space-y-3">
                        <button class="w-full flex items-center justify-center px-4 py-3 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" class="w-5 h-5 mr-3">
                            Masuk dengan Google
                        </button>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center mt-6">
                        <span class="text-slate-600">Belum punya akun? </span>
                        <button onclick="switchToRegister()" class="text-edu-blue hover:text-edu-green font-medium transition-colors">
                            Daftar sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Register Modal -->
        <div id="registerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-slate-900">Daftar di Namerta</h3>
                        <button onclick="closeRegisterModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6">
                    <form id="registerForm" class="space-y-4">
                        <!-- Name Input -->
                        <div>
                            <label for="registerName" class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                            <div class="relative">
                                <input type="text" id="registerName" name="name" required
                                    class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                                    placeholder="Masukkan nama lengkap">
                                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Email Input -->
                        <div>
                            <label for="registerEmail" class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                            <div class="relative">
                                <input type="email" id="registerEmail" name="email" required
                                    class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                                    placeholder="Masukkan email Anda">
                                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="registerPassword" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                            <div class="relative">
                                <input type="password" id="registerPassword" name="password" required
                                    class="w-full pl-10 pr-12 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                                    placeholder="Buat password">
                                <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 0h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <button type="button" onclick="togglePassword('registerPassword')" class="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="flex items-start">
                            <input type="checkbox" id="terms" required class="w-4 h-4 text-edu-blue border-slate-300 rounded focus:ring-edu-blue mt-1">
                            <label for="terms" class="ml-2 text-sm text-slate-600">
                                Saya setuju dengan <a href="#" class="text-edu-blue hover:text-edu-green">Syarat dan Ketentuan</a> 
                                serta <a href="#" class="text-edu-blue hover:text-edu-green">Kebijakan Privasi</a>
                            </label>
                        </div>

                        <!-- Register Button -->
                        <button type="submit" class="w-full bg-gradient-to-r from-edu-blue to-edu-green text-white py-3 rounded-lg font-semibold hover:shadow-lg transform hover:scale-105 transition-all">
                            Daftar Sekarang
                        </button>
                    </form>

                    <!-- Login Link -->
                    <div class="text-center mt-6">
                        <span class="text-slate-600">Sudah punya akun? </span>
                        <button onclick="switchToLogin()" class="text-edu-blue hover:text-edu-green font-medium transition-colors">
                            Masuk di sini
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>



    @yield('konten')

    
    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Logo & Description -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white"></i>
                        </div>
                        <span class="ml-3 text-2xl font-bold">Namerta</span>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Platform pembelajaran online terdepan yang menghubungkan mahasiswa dan dosen dengan teknologi modern untuk pengalaman belajar yang optimal.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary-500 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary-500 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary-500 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-primary-500 transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Kursus</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Instruktur</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Kontak</a></li>
                    </ul>
                </div>
                
                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Dukungan</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Pusat Bantuan</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">
                    © 2025 Namerta. Hak cipta dilindungi undang-undang.
                </p>
            </div>
        </div>
    </footer>


    @yield('js_tambahan')


        <!-- Toast Container - Tambahkan di layouts/home.blade.php sebelum closing </body> -->
        <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

        <!-- Toast JavaScript - Tambahkan di @section('js_tambahan') -->
        <script>
            // Simple Toast Notification System
            function showToast(response) {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                const container = document.getElementById('toastContainer');
                const toastId = 'toast-' + Date.now();
                
                // Create toast element
                const toast = document.createElement('div');
                toast.id = toastId;
                toast.className = `transform transition-all duration-300 ease-out translate-x-0 opacity-100`;
                
                // Determine colors based on success
                const isSuccess = data.success === true;
                const colors = isSuccess 
                    ? 'bg-blue-500 border-blue-600' 
                    : 'bg-red-500 border-red-600';
                
                const icon = isSuccess 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
                
                toast.innerHTML = `
                    <div class="${colors} text-white px-4 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-[300px] max-w-md">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${icon}
                        </svg>
                        <p class="flex-1 text-sm font-medium">${data.message}</p>
                        <button onclick="removeToast('${toastId}')" class="flex-shrink-0 hover:opacity-75 transition-opacity">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                `;
                
                // Add to container
                container.appendChild(toast);
                
                // Animate in
                setTimeout(() => {
                    toast.classList.add('translate-x-0');
                }, 10);
                
                // Auto remove after 4 seconds
                setTimeout(() => {
                    removeToast(toastId);
                }, 4000);
            }

            function removeToast(toastId) {
                const toast = document.getElementById(toastId);
                if (toast) {
                    // Animate out
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => {
                        toast.remove();
                    }, 300);
                }
            }

            // Integration with AJAX responses
            function handleResponse(response) {
                if (response.success !== undefined && response.message) {
                    showToast(response);
                }
            }
        </script>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast({
                        success: true,
                        message: "{{ session('success') }}"
                    });
                });
            </script>

        @endif

        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast({
                        success: false,
                        message: "{{ session('error') }}"
                    });
                });
            </script>
        @endif


<script>
    // {{-- logout --}}
    // Logout Handler dengan pengecekan CSRF token
function logout() {
    // Konfirmasi logout
    if (!confirm('Apakah Anda yakin ingin keluar?')) {
        return;
    }
    
    // Check if CSRF token exists
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (!csrfToken) {
        // Jika tidak ada CSRF token, gunakan form submit biasa
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        
        // Add CSRF token from Laravel blade
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '{{ csrf_token() }}'; // Ini akan di-render oleh Blade
        
        form.appendChild(token);
        document.body.appendChild(form);
        form.submit();
        return;
    }
    
    // Jika ada CSRF token di meta tag, gunakan AJAX
    fetch('/logout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Show toast notification jika fungsi tersedia
        if (typeof showToast === 'function') {
            showToast(data);
        }
        
        setTimeout(() => {
            window.location.href = data.redirect || '/';
        }, 1000);
    })
    .catch(error => {
        console.error('Logout error:', error);
        // Fallback ke redirect biasa
        window.location.href = '/logout';
    });
}

</script>


    
<script>
        // Desktop-only dropdown functions
        function toggleAdminDropdown() {
            const dropdown = document.getElementById('adminDropdown');
            if (window.innerWidth >= 1024) { // Only work on desktop
                dropdown.classList.toggle('hidden');
            }
        }

        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById(`arrow-${submenuId}`);
            
            if (window.innerWidth >= 1024) { // Only work on desktop
                submenu.classList.toggle('hidden');
                if (arrow) {
                    arrow.classList.toggle('rotate-90');
                }
            }
        }

        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Mobile menu functions
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        function toggleMobileAdminDropdown() {
            const dropdown = document.getElementById('mobileAdminDropdown');
            const arrow = document.getElementById('mobileAdminArrow');
            
            dropdown.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        function toggleMobileSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById(`arrow-${submenuId}`);
            
            submenu.classList.toggle('hidden');
            if (arrow) {
                arrow.classList.toggle('rotate-90');
            }
        }

        function editProfile() {
            alert('Fitur edit profile akan ditampilkan dalam modal atau halaman terpisah');
            toggleProfileDropdown();
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (window.innerWidth >= 1024) {
                const adminDropdown = document.getElementById('adminDropdown');
                const adminButton = event.target.closest('button[onclick*="toggleAdminDropdown"]');
                
                
                const profileDropdown = document.getElementById('profileDropdown');
                const profileButton = event.target.closest('button[onclick*="toggleProfileDropdown"]');

                if (!adminButton && !adminDropdown.contains(event.target)) {
                    adminDropdown.classList.add('hidden');
                }
            }
            
            // Close mobile menu when clicking outside
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileButton = event.target.closest('button[onclick*="toggleMobileMenu"]');
            
            if (!mobileButton && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                // Also close all mobile submenus
                const mobileAdminDropdown = document.getElementById('mobileAdminDropdown');
                const mobileAdminArrow = document.getElementById('mobileAdminArrow');
                mobileAdminDropdown.classList.add('hidden');
                mobileAdminArrow.classList.remove('rotate-180');
                
                // Close all mobile submenus
                const mobileSubmenus = ['mobileUserManagement', 'mobileStudents', 'mobileInstructors', 'mobileCourseManagement', 'mobileCourses', 'mobileSystemSettings'];
                mobileSubmenus.forEach(submenuId => {
                    const submenu = document.getElementById(submenuId);
                    const arrow = document.getElementById(`arrow-${submenuId}`);
                    if (submenu) submenu.classList.add('hidden');
                    if (arrow) arrow.classList.remove('rotate-90');
                });
            }
        });

        // Modal Functions
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openRegisterModal() {
            document.getElementById('registerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function switchToRegister() {
            closeLoginModal();
            openRegisterModal();
        }

        function switchToLogin() {
            closeRegisterModal();
            openLoginModal();
        }

        // Toggle Password Visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }

        // Form Handlers
        document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const errorContainer = document.getElementById('loginErrors');
    const errorList = document.getElementById('errorList');
    const submitButton = document.querySelector('#loginForm button[type="submit"]');
    const buttonText = submitButton.textContent;
    
    // Clear previous errors
    errorContainer.classList.add('hidden');
    errorList.innerHTML = '';
    
    // Client-side validation
    const errors = [];
    
    if (!email.trim()) {
        errors.push('Email harus diisi');
    } else if (!email.includes('@') || !email.includes('.')) {
        errors.push('Format email tidak valid');
    }
    
    if (!password.trim()) {
        errors.push('Password harus diisi');
    } else if (password.length < 6) {
        errors.push('Password minimal 6 karakter');
    }
    
    // Show validation errors
    if (errors.length > 0) {
        showLoginErrors(errors);
        return;
    }
    
    // Show loading
    submitButton.disabled = true;
    submitButton.textContent = 'Memproses...';
    
    // Send to route
    const form = document.getElementById('loginForm');
    const formData = new FormData(form);
    
    

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })

    .then(response => response.json())
    .then(data => {
        submitButton.disabled = false;
        submitButton.textContent = buttonText;
        
        if (data.success === true) {
            closeLoginModal();
            window.location.href = data.redirect || '/dashboard';
        } else {
            // Login failed - show error in modal
            const errorMessage = data.message || data.error || 'Login gagal';
            showLoginErrors([errorMessage]);
        }
    })
    .catch(error => {
        submitButton.disabled = false;
        submitButton.textContent = buttonText;
        showLoginErrors(['Terjadi kesalahan server']);
    });
});
        function showLoginErrors(errors) {
            const errorContainer = document.getElementById('loginErrors');
            const errorList = document.getElementById('errorList');
            
            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            
            errorContainer.classList.remove('hidden');
            
            // Scroll to top of modal to show error
            const modalBody = errorContainer.closest('.px-6');
            modalBody.scrollTop = 0;
        }
        
        function simulateLoginResponse(email, password) {
            const submitButton = document.querySelector('#loginForm button[type="submit"]');
            const buttonText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            `;
            
            // Simulate server delay
            setTimeout(() => {
                // Restore button state
                submitButton.disabled = false;
                submitButton.textContent = buttonText;
                
                // Simulate different login scenarios for demo
                if (email === 'admin@eduverse.com' && password === 'password123') {
                    // Success - close modal and redirect
                    alert('Login berhasil! Akan diarahkan ke dashboard.');
                    closeLoginModal();
                    // In real app: window.location.href = '/dashboard';
                } else if (email === 'test@eduverse.com' && password === 'test123') {
                    // Success case 2
                    alert('Login berhasil! Selamat datang kembali.');
                    closeLoginModal();
                } else {
                    // Error cases - stay in modal and show errors
                    const errors = [];
                    
                    if (password === '123456') {
                        errors.push('Password terlalu lemah, gunakan kombinasi huruf dan angka');
                    } else if (email.includes('banned')) {
                        errors.push('Akun Anda telah dinonaktifkan, hubungi administrator');
                    } else if (!email.includes('@eduverse.com')) {
                        errors.push('Email tidak terdaftar dalam sistem');
                    } else {
                        errors.push('Email atau password salah');
                        errors.push('Pastikan email dan password yang Anda masukkan benar');
                    }
                    
                    showLoginErrors(errors);
                }
            }, 1500); // 1.5 second delay to simulate server response
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const terms = document.getElementById('terms').checked;
            
            if (!terms) {
                alert('Harap setujui syarat dan ketentuan');
                return;
            }
            
            console.log('Registration attempt:', { name, email, password });
            alert('Registration functionality would be implemented here');
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const loginModal = document.getElementById('loginModal');
            const registerModal = document.getElementById('registerModal');
            
            if (e.target === loginModal) {
                closeLoginModal();
            }
            if (e.target === registerModal) {
                closeRegisterModal();
            }
        });

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInputs = document.querySelectorAll('input[placeholder*="Cari kursus"]');
            searchInputs.forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const query = this.value;
                        if (query) {
                            alert(`Mencari kursus: "${query}"`);
                        }
                    }
                });
            });
        });

        // Close mobile menu when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const mobileMenu = document.getElementById('mobileMenu');
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
</body>
</html>