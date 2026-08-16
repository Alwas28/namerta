@extends('layouts.home')

@section('konten')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">My Courses</h1>
            <p class="text-slate-600">Daftar mata pelajaran yang Anda ikuti</p>
        </div>

        <!-- Course Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $gradients = [
                    'from-blue-500 to-blue-600',
                    'from-green-500 to-green-600',
                    'from-purple-500 to-purple-600',
                    'from-orange-500 to-orange-600',
                    'from-pink-500 to-pink-600',
                    'from-indigo-500 to-indigo-600',
                    'from-red-500 to-red-600',
                    'from-teal-500 to-teal-600',
                ];
            @endphp

            @forelse($courses as $index => $course)
                @php
                    $gradient = $gradients[$index % count($gradients)];
                @endphp
                
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
                    <!-- Card Header with Gradient -->
                    <div class="h-48 bg-gradient-to-br {{ $gradient }} relative overflow-hidden">
                        <div class="absolute inset-0 bg-black/20"></div>
                        <div class="absolute bottom-4 left-4 right-4">
                            <h3 class="text-xl font-bold text-white mb-1">{{ $course->nama_mata_pelajaran }}</h3>
                            <p class="text-white/90 text-sm">{{ $course->nama_kelas }}</p>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6">
                        <!-- Teacher Name -->
                        <div class="flex items-center text-sm text-slate-500 mb-4">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>{{ $course->nama_guru ?? 'Belum ditentukan' }}</span>
                        </div>

                        <!-- Course Description -->
                        @if($course->deskripsi)
                            <p class="text-sm text-slate-600 mb-4 line-clamp-2">
                                {{ $course->deskripsi }}
                            </p>
                        @endif

                        <!-- Course Stats -->
                        <div class="flex justify-between text-sm text-slate-600 mb-4">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>{{ $course->total_modul }} Modul</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ $course->nama_ta }}</span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('modul.index', $course->id_kelas_mp) }}" 
                           class="block w-full text-center bg-gradient-to-r {{ $gradient }} text-white py-2 rounded-lg font-medium hover:shadow-md transition-all">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                        <svg class="w-24 h-24 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-slate-900 mb-2">Belum Ada Mata Pelajaran</h3>
                        <p class="text-slate-500">Anda belum terdaftar di mata pelajaran manapun.</p>
                        <p class="text-slate-500">Silakan hubungi administrator untuk mendaftarkan Anda ke kelas.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection