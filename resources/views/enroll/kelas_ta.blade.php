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
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Kelas per Tahun Ajaran</h1>
                <p class="text-slate-600 mt-1">Kelola kelas yang aktif untuk setiap tahun ajaran</p>
            </div>
            
            <!-- Filter Tahun Ajaran -->
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex items-center space-x-2">
                    <label for="tahunAjaranFilter" class="text-sm font-medium text-slate-700 whitespace-nowrap">Tahun Ajaran:</label>
                    <select id="tahunAjaranFilter" class="border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white min-w-[200px]">
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($tahunAjaran as $ta)
                            <option value="{{ $ta->id_ta }}" {{ $ta->id_ta == $selectedTA ? 'selected' : '' }}>
                                {{ $ta->nama_ta }}
                                @if($ta->aktif === 'Y') (Aktif) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        @if($currentTA)
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-blue-800 font-medium">Tahun Ajaran Terpilih: {{ $currentTA->nama_ta }}</span>
                @if($currentTA->aktif === 'Y')
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    @if($selectedTA)
    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-edu-blue hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Kelas
            </button>
            
            <button onclick="openCopyModal()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Salin dari TA Lain
            </button>
            
            <button onclick="refreshTable()" class="inline-flex items-center px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-medium transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6">
            <table id="kelasTable" class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">No</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Nama Kelas</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Deskripsi</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Mata Pelajaran</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Siswa</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    @else
    <!-- No TA Selected -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <h3 class="text-lg font-medium text-slate-900 mb-2">Pilih Tahun Ajaran</h3>
        <p class="text-slate-600">Pilih tahun ajaran untuk melihat dan mengelola kelas</p>
    </div>
    @endif
</div>

<!-- Modal Add Kelas -->
<div id="addModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Tambah Kelas ke Tahun Ajaran</h3>
                    <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="addForm">
                <div class="px-6 py-4">
                    <input type="hidden" name="id_ta" value="{{ $selectedTA }}">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Kelas</label>
                        @if(count($availableKelas) > 0)
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3">
                            @foreach($availableKelas as $kelas)
                            <label class="flex items-center space-x-3 cursor-pointer hover:bg-slate-50 p-2 rounded">
                                <input type="checkbox" name="id_kelas[]" value="{{ $kelas->id_kelas }}" 
                                       class="w-4 h-4 text-edu-blue border-slate-300 rounded focus:ring-edu-blue">
                                <div class="flex-1">
                                    <span class="font-medium text-slate-900">{{ $kelas->nama_kelas }}</span>
                                    @if($kelas->deskripsi)
                                    <p class="text-xs text-slate-500">{{ $kelas->deskripsi }}</p>
                                    @endif
                                </div>
                            </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Pilih satu atau lebih kelas untuk ditambahkan</p>
                        @else
                        <div class="text-center py-8 text-slate-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>Semua kelas sudah ditambahkan ke tahun ajaran ini</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                @if(count($availableKelas) > 0)
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-edu-blue hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Tambah Kelas
                    </button>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Modal Copy from Previous TA -->
<div id="copyModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Salin Kelas dari Tahun Ajaran Lain</h3>
                    <button onclick="closeCopyModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="copyForm">
                <div class="px-6 py-4">
                    <input type="hidden" name="to_ta" value="{{ $selectedTA }}">
                    
                    <div class="mb-4">
                        <label for="from_ta" class="block text-sm font-medium text-slate-700 mb-2">Tahun Ajaran Sumber</label>
                        <select name="from_ta" id="from_ta" required 
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-edu-blue focus:border-transparent">
                            <option value="">Pilih tahun ajaran sumber</option>
                            @foreach($tahunAjaran as $ta)
                                @if($ta->id_ta != $selectedTA)
                                <option value="{{ $ta->id_ta }}">{{ $ta->nama_ta }}</option>
                                @endif
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Semua kelas dari tahun ajaran sumber akan disalin</p>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeCopyModal()" 
                            class="px-4 py-2 text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        Salin Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Manage Mata Pelajaran -->
<div id="mapelModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Kelola Mata Pelajaran</h3>
                    <button onclick="closeMapelModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-4">
                <div id="mapelContent" class="min-h-[300px] flex items-center justify-center">
                    <div class="text-slate-500">
                        <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Memuat...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_tambahan')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.tailwindcss.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
let kelasTable;
let currentTA = '{{ $selectedTA }}';

// Get CSRF token from hidden input
function getCSRFToken() {
    return document.getElementById('csrf_token').value;
}

$(document).ready(function() {
    if (currentTA) {
        initDataTable();
    }
    
    // Filter tahun ajaran change
    $('#tahunAjaranFilter').change(function() {
        const selectedTA = $(this).val();
        const baseUrl = window.location.href.split('?')[0];
        window.location.href = baseUrl + (selectedTA ? '?ta_filter=' + selectedTA : '');
    });
});

function initDataTable() {
    if (kelasTable) {
        kelasTable.destroy();
    }
    
    kelasTable = $('#kelasTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: window.location.href,
            data: function(d) {
                d.ta_filter = currentTA;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
            { data: 'nama_kelas', name: 'nama_kelas', width: '20%' },
            { data: 'deskripsi', name: 'deskripsi', width: '25%' },
            { data: 'mapel_count', name: 'mapel_count', orderable: false, searchable: false, width: '15%' },
            { data: 'siswa_count', name: 'siswa_count', orderable: false, searchable: false, width: '15%' },
            { data: 'status', name: 'status', orderable: false, searchable: false, width: '10%' },
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
            emptyTable: "Tidak ada data yang tersedia",
            paginate: {
                first: "Pertama",
                previous: "Sebelumnya",
                next: "Selanjutnya",
                last: "Terakhir"
            }
        },
        dom: '<"flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4"<"flex items-center"l><"flex items-center"f>>rtip',
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
    });
}

function refreshTable() {
    if (kelasTable) {
        kelasTable.ajax.reload();
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

function closeMapelModal() {
    document.getElementById('mapelModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Form Handlers
$('#addForm').submit(function(e) {
    e.preventDefault();
    
    // Get selected checkboxes
    const selectedKelas = [];
    $('input[name="id_kelas[]"]:checked').each(function() {
        selectedKelas.push($(this).val());
    });
    
    console.log('Selected kelas:', selectedKelas);
    
    if (selectedKelas.length === 0) {
        alert('Pilih minimal satu kelas');
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Memproses...');
    
    // Prepare data
    const postData = {
        id_ta: $('input[name="id_ta"]').val(),
        id_kelas: selectedKelas,
        _token: getCSRFToken()
    };
    
    console.log('Sending data:', postData);
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: postData,
        success: function(response) {
            console.log('Success response:', response);
            
            if (typeof showToast === 'function') {
                showToast(response);
            } else {
                alert(response.message || 'Berhasil menambahkan kelas');
            }
            
            closeAddModal();
            refreshTable();
            
            // Reload page to update available kelas
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        },
        error: function(xhr) {
            console.log('Error response:', xhr);
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
    
    const fromTA = $('#from_ta').val();
    
    if (!fromTA) {
        alert('Pilih tahun ajaran sumber');
        return;
    }
    
    if (!confirm('Apakah Anda yakin ingin menyalin semua kelas dari tahun ajaran yang dipilih?')) {
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Memproses...');
    
    const postData = {
        from_ta: fromTA,
        to_ta: $('input[name="to_ta"]').val(),
        _token: getCSRFToken()
    };
    
    // Use relative URL for copy endpoint
    const baseUrl = window.location.href.split('?')[0];
    const copyUrl = baseUrl + '/copy-previous';
    
    $.ajax({
        url: copyUrl,
        method: 'POST',
        data: postData,
        success: function(response) {
            if (typeof showToast === 'function') {
                showToast(response);
            } else {
                alert(response.message || 'Berhasil menyalin kelas');
            }
            
            closeCopyModal();
            refreshTable();
            
            // Reload page to update available kelas
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

// Action Functions
function removeFromTA(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus kelas ini dari tahun ajaran?')) {
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
                alert(response.message || 'Berhasil menghapus kelas');
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

function manageMapel(kelasTA) {
    document.getElementById('mapelModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Load mata pelajaran management content
    document.getElementById('mapelContent').innerHTML = `
        <div class="text-slate-500 text-center">
            <svg class="w-8 h-8 mx-auto mb-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Memuat mata pelajaran...
        </div>
    `;
    
    // Placeholder for mata pelajaran management
    setTimeout(() => {
        document.getElementById('mapelContent').innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="text-lg font-medium text-slate-900 mb-2">Kelola Mata Pelajaran</h3>
                <p class="text-slate-600 mb-4">Fitur ini akan mengintegrasikan dengan halaman kelas-mata pelajaran</p>
                <p class="text-sm text-slate-500">Kelas TA ID: ${kelasTA}</p>
                <button onclick="closeMapelModal()" class="mt-4 px-4 py-2 bg-edu-blue text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Tutup
                </button>
            </div>
        `;
    }, 1000);
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeCopyModal();
        closeMapelModal();
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const copyModal = document.getElementById('copyModal');
    const mapelModal = document.getElementById('mapelModal');
    
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === copyModal) {
        closeCopyModal();
    }
    if (event.target === mapelModal) {
        closeMapelModal();
    }
};
</script>
@endsection