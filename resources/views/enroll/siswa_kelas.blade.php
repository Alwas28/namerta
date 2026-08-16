@extends('layouts.home')

@section('css_tambahan')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-1 ml-1 text-gray-500 border border-gray-300 rounded hover:bg-gray-50;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-edu-blue text-white border-edu-blue;
    }
    .dataTables_wrapper .dataTables_filter input {
        @apply border border-gray-300 rounded px-3 py-1 ml-2;
    }
    .dataTables_wrapper .dataTables_length select {
        @apply border border-gray-300 rounded px-2 py-1 ml-2 mr-2;
    }
    .modal-overlay {
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }
</style>
@endsection

@section('konten')
<!-- CSRF Token for AJAX requests -->
<input type="hidden" id="csrf_token" value="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Enroll Siswa ke Kelas</h1>
                <p class="text-slate-600 mt-1">Kelola pendaftaran siswa di setiap kelas untuk tahun ajaran tertentu</p>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <!-- Filter Tahun Ajaran -->
            <div>
                <label for="tahunAjaranFilter" class="block text-sm font-medium text-slate-700 mb-2">Tahun Ajaran</label>
                <select id="tahunAjaranFilter" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white">
                    <option value="">Pilih Tahun Ajaran</option>
                    @foreach($tahunAjaran as $ta)
                        <option value="{{ $ta->id_ta }}" {{ $ta->id_ta == $selectedTA ? 'selected' : '' }}>
                            {{ $ta->nama_ta }}
                            @if($ta->aktif === 'Y') (Aktif) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Filter Kelas -->
            <div>
                <label for="kelasFilter" class="block text-sm font-medium text-slate-700 mb-2">Kelas</label>
                <select id="kelasFilter" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white" {{ !$selectedTA ? 'disabled' : '' }}>
                    <option value="">Pilih Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id_kelas_ta }}" {{ $kelas->id_kelas_ta == $selectedKelas ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        @if($currentTA && $currentKelas)
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-blue-800 font-medium">
                    {{ $currentTA->nama_ta }} - {{ $currentKelas->nama_kelas }}
                </span>
                @if($currentTA->aktif === 'Y')
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    @if($selectedTA && $selectedKelas)
    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-edu-blue hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Siswa
            </button>
            
            <button onclick="openCopyModal()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Salin dari Kelas Lain
            </button>
            
            <button onclick="refreshTable()" class="inline-flex items-center px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6" id="statsCards">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Siswa</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalSiswa">-</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Siswa Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900" id="siswaAktif">-</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Siswa Non-Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900" id="siswaNonAktif">-</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <table id="siswaTable" class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">No</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Nama Siswa</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">NISN</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Jenis Kelamin</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Tanggal Daftar</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @else
    <!-- No Selection -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
        <h3 class="text-lg font-medium text-slate-900 mb-2">Pilih Tahun Ajaran dan Kelas</h3>
        <p class="text-slate-600">Pilih tahun ajaran dan kelas untuk mengelola pendaftaran siswa</p>
    </div>
    @endif
</div>

<!-- Modal Add Siswa -->
<div id="addModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Tambah Siswa ke Kelas</h3>
                    <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="addForm">
                <div class="px-6 py-4">
                    <input type="hidden" name="id_kelas_ta" value="{{ $selectedKelas }}">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Siswa</label>
                        @if(count($availableSiswa) > 0)
                        <div class="mb-3">
                            <input type="text" id="searchSiswa" placeholder="Cari nama siswa..." 
                                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="space-y-2 max-h-64 overflow-y-auto border border-slate-200 rounded-lg p-3" id="siswaList">
                            @foreach($availableSiswa as $siswa)
                            <label class="flex items-center space-x-3 cursor-pointer hover:bg-slate-50 p-2 rounded siswa-item">
                                <input type="checkbox" name="id_user[]" value="{{ $siswa->id_user }}" 
                                       class="w-4 h-4 text-edu-blue border-slate-300 rounded focus:ring-edu-blue">
                                <div class="flex-1">
                                    <span class="font-medium text-slate-900 siswa-nama">{{ $siswa->nama }}</span>
                                    @if($siswa->nisn)
                                    <p class="text-xs text-slate-500">NISN: {{ $siswa->nisn }}</p>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <div class="mt-2 flex justify-between items-center">
                            <p class="text-xs text-slate-500">Pilih satu atau lebih siswa untuk ditambahkan</p>
                            <div class="space-x-2">
                                <button type="button" onclick="selectAllSiswa()" class="text-xs text-edu-blue hover:text-blue-800">Pilih Semua</button>
                                <button type="button" onclick="clearAllSiswa()" class="text-xs text-slate-500 hover:text-slate-700">Bersihkan</button>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-8 text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>Semua siswa sudah terdaftar di kelas ini</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                @if(count($availableSiswa) > 0)
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-edu-blue hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Tambah Siswa
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Modal Copy from Another Kelas -->
<div id="copyModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Salin Siswa dari Kelas Lain</h3>
                    <button onclick="closeCopyModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="copyForm">
                <div class="px-6 py-4">
                    <input type="hidden" name="to_kelas_ta" value="{{ $selectedKelas }}">
                    
                    <div class="mb-4">
                        <label for="from_kelas_ta" class="block text-sm font-medium text-slate-700 mb-2">Kelas Sumber</label>
                        <select name="from_kelas_ta" id="from_kelas_ta" required 
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-edu-blue focus:border-transparent">
                            <option value="">Pilih kelas sumber</option>
                            @foreach($kelasList as $kelas)
                                @if($kelas->id_kelas_ta != $selectedKelas)
                                <option value="{{ $kelas->id_kelas_ta }}">{{ $kelas->nama_kelas }}</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Semua siswa aktif dari kelas sumber akan disalin</p>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeCopyModal()" 
                            class="px-4 py-2 text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        Salin Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div id="editModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Edit Status Siswa</h3>
                    <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="editForm">
                <div class="px-6 py-4 space-y-4">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Nama Siswa</label>
                        <input type="text" id="edit_siswa_nama" readonly 
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">NISN</label>
                        <input type="text" id="edit_nisn" readonly 
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                    </div>
                    
                    <div>
                        <label for="edit_aktif" class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select name="aktif" id="edit_aktif" required 
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-edu-blue focus:border-transparent">
                            <option value="Y">Aktif</option>
                            <option value="N">Non-Aktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js_tambahan')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
let siswaTable;
let currentTA = '{{ $selectedTA }}';
let currentKelas = '{{ $selectedKelas }}';

// Get CSRF token from hidden input
function getCSRFToken() {
    return document.getElementById('csrf_token').value;
}

$(document).ready(function() {
    if (currentTA && currentKelas) {
        initDataTable();
        updateStats();
    }
    
    // Filter tahun ajaran change
    $('#tahunAjaranFilter').change(function() {
        const selectedTA = $(this).val();
        const baseUrl = window.location.href.split('?')[0];
        window.location.href = baseUrl + (selectedTA ? '?ta_filter=' + selectedTA : '');
    });
    
    // Filter kelas change
    $('#kelasFilter').change(function() {
        const selectedKelas = $(this).val();
        const selectedTA = $('#tahunAjaranFilter').val();
        const baseUrl = window.location.href.split('?')[0];
        
        let params = [];
        if (selectedTA) params.push('ta_filter=' + selectedTA);
        if (selectedKelas) params.push('kelas_filter=' + selectedKelas);
        
        const queryString = params.length > 0 ? '?' + params.join('&') : '';
        window.location.href = baseUrl + queryString;
    });
    
    // Search siswa functionality
    $('#searchSiswa').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('.siswa-item').filter(function() {
            $(this).toggle($(this).find('.siswa-nama').text().toLowerCase().indexOf(value) > -1);
        });
    });
});

function initDataTable() {
    if (siswaTable) {
        siswaTable.destroy();
    }
    
    siswaTable = $('#siswaTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: window.location.href,
            data: function(d) {
                d.ta_filter = currentTA;
                d.kelas_filter = currentKelas;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'siswa_nama', name: 'siswa_nama', width: '25%' },
            { data: 'nisn', name: 'nisn', width: '15%' },
            { data: 'jenis_kelamin', name: 'jenis_kelamin', width: '15%' },
            { data: 'status', name: 'status', orderable: false, searchable: false, width: '15%' },
            { data: 'tanggal_daftar', name: 'tanggal_daftar', width: '15%' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '10%' }
        ],
        language: {
            processing: "Memproses...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            loadingRecords: "Memuat...",
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "Tidak ada siswa yang terdaftar",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        },
        dom: '<"flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4"<"flex items-center"l><"flex items-center"f>>rtip',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        drawCallback: function() {
            updateStats();
        }
    });
}

function updateStats() {
    // Simple stats update based on table data
    if (siswaTable) {
        const info = siswaTable.page.info();
        $('#totalSiswa').text(info.recordsTotal);
        
        // Get more detailed stats via AJAX if needed
        // For now, using basic counts
        $('#siswaAktif').text('-');
        $('#siswaNonAktif').text('-');
    }
}

function refreshTable() {
    if (siswaTable) {
        siswaTable.ajax.reload();
        if (typeof showToast === 'function') {
            showToast({
                success: true,
                message: 'Data berhasil diperbarui'
            });
        }
    }
}

// Modal Functions
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('addForm').reset();
    $('#searchSiswa').val('');
    $('.siswa-item').show();
}

function openCopyModal() {
    document.getElementById('copyModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCopyModal() {
    document.getElementById('copyModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('copyForm').reset();
}

function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('editForm').reset();
}

// Checkbox helper functions
function selectAllSiswa() {
    $('.siswa-item:visible input[type="checkbox"]').prop('checked', true);
}

function clearAllSiswa() {
    $('.siswa-item input[type="checkbox"]').prop('checked', false);
}

// Form Handlers
$('#addForm').submit(function(e) {
    e.preventDefault();
    
    // Get selected checkboxes
    const selectedSiswa = [];
    $('input[name="id_user[]"]:checked').each(function() {
        selectedSiswa.push($(this).val());
    });
    
    if (selectedSiswa.length === 0) {
        alert('Pilih minimal satu siswa');
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Memproses...');
    
    const postData = {
        id_kelas_ta: $('input[name="id_kelas_ta"]').val(),
        id_user: selectedSiswa,
        _token: getCSRFToken()
    };
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: postData,
        success: function(response) {
            if (typeof showToast === 'function') {
                showToast(response);
            } else {
                alert(response.message || 'Berhasil menambahkan siswa');
            }
            
            closeAddModal();
            refreshTable();
            
            // Reload page to update available siswa
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response?.message || 'Terjadi kesalahan';
            
            if (typeof showToast === 'function') {
                showToast({
                    success: false,
                    message: message
                });
            } else {
                alert('Error: ' + message);
            }
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
});

$('#copyForm').submit(function(e) {
    e.preventDefault();
    
    const fromKelas = $('#from_kelas_ta').val();
    
    if (!fromKelas) {
        alert('Pilih kelas sumber');
        return;
    }
    
    if (!confirm('Apakah Anda yakin ingin menyalin semua siswa aktif dari kelas yang dipilih?')) {
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Memproses...');
    
    const postData = {
        from_kelas_ta: fromKelas,
        to_kelas_ta: $('input[name="to_kelas_ta"]').val(),
        _token: getCSRFToken()
    };
    
    const baseUrl = window.location.href.split('?')[0];
    const copyUrl = baseUrl + '/copy-kelas';
    
    $.ajax({
        url: copyUrl,
        method: 'POST',
        data: postData,
        success: function(response) {
            if (typeof showToast === 'function') {
                showToast(response);
            } else {
                alert(response.message || 'Berhasil menyalin siswa');
            }
            
            closeCopyModal();
            refreshTable();
            
            // Reload page to update available siswa
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response?.message || 'Terjadi kesalahan';
            
            if (typeof showToast === 'function') {
                showToast({
                    success: false,
                    message: message
                });
            } else {
                alert('Error: ' + message);
            }
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
});

$('#editForm').submit(function(e) {
    e.preventDefault();
    
    const siswaId = $('#edit_id').val();
    const aktif = $('#edit_aktif').val();
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Memproses...');
    
    const postData = {
        aktif: aktif,
        _token: getCSRFToken(),
        _method: 'PUT'
    };
    
    const baseUrl = window.location.href.split('?')[0];
    const updateUrl = baseUrl + '/' + siswaId;
    
    $.ajax({
        url: updateUrl,
        method: 'POST',
        data: postData,
        success: function(response) {
            if (typeof showToast === 'function') {
                showToast(response);
            } else {
                alert(response.message || 'Berhasil memperbarui status siswa');
            }
            
            closeEditModal();
            refreshTable();
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response?.message || 'Terjadi kesalahan';
            
            if (typeof showToast === 'function') {
                showToast({
                    success: false,
                    message: message
                });
            } else {
                alert('Error: ' + message);
            }
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
});

// Action Functions
function editSiswa(id) {
    const baseUrl = window.location.href.split('?')[0];
    const showUrl = baseUrl + '/' + id;
    
    $.ajax({
        url: showUrl,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                $('#edit_id').val(data.id);
                $('#edit_siswa_nama').val(data.siswa_nama);
                $('#edit_nisn').val(data.nisn || '-');
                $('#edit_aktif').val(data.aktif);
                
                openEditModal();
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response?.message || 'Gagal mengambil data siswa';
            
            if (typeof showToast === 'function') {
                showToast({
                    success: false,
                    message: message
                });
            } else {
                alert('Error: ' + message);
            }
        }
    });
}

function removeSiswa(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus siswa ini dari kelas?')) {
        return;
    }
    
    const baseUrl = window.location.href.split('?')[0];
    const deleteUrl = baseUrl + '/' + id;
    
    $.ajax({
        url: deleteUrl,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': getCSRFToken()
        },
        success: function(response) {
            if (typeof showToast === 'function') {
                showToast(response);
            } else {
                alert(response.message || 'Berhasil menghapus siswa dari kelas');
            }
            refreshTable();
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response?.message || 'Terjadi kesalahan';
            
            if (typeof showToast === 'function') {
                showToast({
                    success: false,
                    message: message
                });
            } else {
                alert('Error: ' + message);
            }
        }
    });
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeCopyModal();
        closeEditModal();
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const copyModal = document.getElementById('copyModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === copyModal) {
        closeCopyModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
};
</script>
@endsection