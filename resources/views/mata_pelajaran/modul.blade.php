@extends('layouts.home')

@section('konten')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-2">
                        <a href="{{ route('modul.index', $courseId) }}" class="text-slate-600 hover:text-slate-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <h1 class="text-3xl font-bold text-slate-900">
                            {{ $canManage ? 'Kelola Modul' : 'Daftar Modul' }}
                        </h1>
                    </div>
                    <p class="text-slate-600">{{ $course->nama_mata_pelajaran }}</p>
                </div>
                
                @if($canManage)
                    <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-edu-blue text-white rounded-lg hover:bg-edu-blue/90 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Modul
                    </button>
                @endif
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Modules Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($modules as $index => $modul)
                @php
                    $materiCount = DB::table('materi')->where('id_modul', $modul->id_modul)->count();
                @endphp
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer"
                     onclick="window.location.href='{{ route('modul.show', [$courseId, $modul->id_modul]) }}'">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center flex-1">
                                <div class="w-10 h-10 bg-edu-blue/10 rounded-lg flex items-center justify-center text-edu-blue font-bold mr-3 flex-shrink-0">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <a href="{{ route('modul.show', [$courseId, $modul->id_modul]) }}" 
                                       onclick="event.stopPropagation()"
                                       class="font-semibold text-lg text-slate-900 hover:text-edu-blue transition-colors">
                                        {{ $modul->nama_modul }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        @if($modul->desk)
                            <p class="text-sm text-slate-600 mb-4 line-clamp-2">{{ $modul->desk }}</p>
                        @endif
                        
                        <!-- Content Badges -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            @if($modul->pengalaman_belajar)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    Pengalaman Belajar
                                </span>
                            @endif
                            @if($modul->koneksi_materi)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    Koneksi Materi
                                </span>
                            @endif
                            @if($modul->tes_kompetensi)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    Tes Kompetensi
                                </span>
                            @endif
                            @if($modul->aksi_nyata)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
                                    Aksi Nyata
                                </span>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                            <span class="text-sm text-slate-500">
                                <i class="fas fa-book mr-1"></i> {{ $materiCount }} Materi
                            </span>
                            <div class="flex items-center space-x-2">
                                @if($canManage)
                                    <button onclick="event.stopPropagation(); openEditModal({{ json_encode($modul) }})" 
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    
                                    <form action="{{ route('modul.destroy', [$courseId, $modul->id_modul]) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="event.stopPropagation()"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('modul.show', [$courseId, $modul->id_modul]) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-edu-blue text-white text-sm rounded-lg hover:bg-edu-blue/90 transition-colors">
                                        Buka Modul
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-slate-500 text-lg">Belum ada modul untuk mata pelajaran ini</p>
                        @if($canManage)
                            <button onclick="openCreateModal()" class="mt-4 inline-flex items-center px-4 py-2 bg-edu-blue text-white rounded-lg hover:bg-edu-blue/90 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Tambah Modul Pertama
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@if($canManage)
<!-- Create/Edit Modal - Only show for users with manage access -->
<div id="modulModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-200">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-xl font-bold text-slate-900">Tambah Modul Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 180px)">
            <form id="modulForm" method="POST">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="POST">
                
                <!-- Nama Modul -->
                <div class="mb-4">
                    <label for="nama_modul" class="block text-sm font-medium text-slate-700 mb-2">
                        Nama Modul <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama_modul" 
                           id="nama_modul" 
                           class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                           placeholder="Masukkan nama modul"
                           required>
                </div>

                <!-- Deskripsi -->
                <div class="mb-4">
                    <label for="desk" class="block text-sm font-medium text-slate-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="desk" 
                              id="desk" 
                              rows="2"
                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                              placeholder="Masukkan deskripsi modul"></textarea>
                </div>

                <!-- Pengalaman Belajar -->
                <div class="mb-4">
                    <label for="pengalaman_belajar" class="block text-sm font-medium text-slate-700 mb-2">
                        Pengalaman Belajar
                    </label>
                    <textarea name="pengalaman_belajar" 
                              id="pengalaman_belajar" 
                              rows="3"
                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                              placeholder="Deskripsikan pengalaman belajar yang akan didapat"></textarea>
                </div>

                <!-- Koneksi Materi -->
                <div class="mb-4">
                    <label for="koneksi_materi" class="block text-sm font-medium text-slate-700 mb-2">
                        Koneksi Materi
                    </label>
                    <textarea name="koneksi_materi" 
                              id="koneksi_materi" 
                              rows="3"
                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                              placeholder="Jelaskan koneksi dengan materi lain"></textarea>
                </div>

                <!-- Tes Kompetensi -->
                <div class="mb-4">
                    <label for="tes_kompetensi" class="block text-sm font-medium text-slate-700 mb-2">
                        Tes Kompetensi
                    </label>
                    <textarea name="tes_kompetensi" 
                              id="tes_kompetensi" 
                              rows="3"
                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                              placeholder="Deskripsikan bentuk tes kompetensi"></textarea>
                </div>

                <!-- Aksi Nyata -->
                <div class="mb-4">
                    <label for="aksi_nyata" class="block text-sm font-medium text-slate-700 mb-2">
                        Aksi Nyata
                    </label>
                    <textarea name="aksi_nyata" 
                              id="aksi_nyata" 
                              rows="3"
                              class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-blue focus:border-transparent"
                              placeholder="Deskripsikan aksi nyata yang akan dilakukan"></textarea>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-end space-x-3">
            <button onclick="closeModal()" 
                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                Batal
            </button>
            <button onclick="submitForm()" 
                    class="px-4 py-2 bg-edu-blue text-white rounded-lg hover:bg-edu-blue/90 transition-colors">
                Simpan Modul
            </button>
        </div>
    </div>
</div>
@endif
@endsection

@section('js_tambahan')
<script>
    const courseId = "{{ $courseId }}";
    const canManage = {{ $canManage ? 'true' : 'false' }};
    
    @if($canManage)
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Modul Baru';
        document.getElementById('modulForm').action = "{{ route('modul.store', $courseId) }}";
        document.getElementById('methodField').value = 'POST';
        
        // Clear form
        document.getElementById('modulForm').reset();
        
        // Show modal
        document.getElementById('modulModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function openEditModal(modul) {
        document.getElementById('modalTitle').textContent = 'Edit Modul';
        document.getElementById('modulForm').action = `/courses/${courseId}/modul/${modul.id_modul}`;
        document.getElementById('methodField').value = 'PUT';
        
        // Fill form with modul data
        document.getElementById('nama_modul').value = modul.nama_modul || '';
        document.getElementById('desk').value = modul.desk || '';
        document.getElementById('pengalaman_belajar').value = modul.pengalaman_belajar || '';
        document.getElementById('koneksi_materi').value = modul.koneksi_materi || '';
        document.getElementById('tes_kompetensi').value = modul.tes_kompetensi || '';
        document.getElementById('aksi_nyata').value = modul.aksi_nyata || '';
        
        // Show modal
        document.getElementById('modulModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        document.getElementById('modulModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    
    function submitForm() {
        document.getElementById('modulForm').submit();
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modulModal');
        if (e.target === modal) {
            closeModal();
        }
    });
    @endif
</script>
@endsection