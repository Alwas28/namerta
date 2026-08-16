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
                <h1 class="text-2xl font-bold text-slate-900">Enroll Mata Pelajaran di Kelas</h1>
                <p class="text-slate-600 mt-1">Kelola mata pelajaran dan guru yang mengajar di setiap kelas</p>
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
                Tambah Mata Pelajaran
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
            <table id="mapelTable" class="w-full">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">No</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Mata Pelajaran</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Guru Pengajar</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Modul</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-700">Status</th>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <h3 class="text-lg font-medium text-slate-900 mb-2">Pilih Tahun Ajaran dan Kelas</h3>
        <p class="text-slate-600">Pilih tahun ajaran dan kelas untuk mengelola mata pelajaran</p>
    </div>
    @endif
</div>

<!-- Modal Add Mata Pelajaran -->
<div id="addModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Tambah Mata Pelajaran</h3>
                    <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="addForm">
                <div class="px-6 py-4 space-y-4">
                    <input type="hidden" name="id_kelas_ta" value="{{ $selectedKelas }}">
                    
                    <div>
                        <label for="id_mata_pelajaran" class="block text-sm font-medium text-slate-700 mb-2">Mata Pelajaran</label>
                        <select name="id_mata_pelajaran" id="id_mata_pelajaran" required 
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-edu-blue focus:border-transparent">
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($availableMapel as $mapel)
                                <option value="{{ $mapel->id_mata_pelajaran }}">{{ $mapel->nama_mata_pelajaran }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="id_user" class="block text-sm font-medium text-slate-700 mb-2">Guru Pengajar</label>
                        <select name="id_user" id="id_user" required 
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-edu-blue focus:border-transparent">
                            <option value="">Pilih Guru</option>
                            @foreach($availableGuru as $guru)
                                <option value="{{ $guru->id_user }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 text-slate-700 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-edu-blue hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Mata Pelajaran -->
<div id="editModal" class="hidden fixed inset-0 z-50 modal-overlay">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-900">Edit Mata Pelajaran</h3>
                    <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="editForm">
                <div class="px-6 py-4 space-y-4">
                    <input type="hidden" name="id_kelas_mp" id="edit_id_kelas_mp">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Mata Pelajaran</label>
                        <input type="text" id="edit_mata_pelajaran" readonly 
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-700">
                    </div>
                    
                    <div>
                        <label for="edit_id_user" class="block text-sm font-medium text-slate-700 mb-2">Guru Pengajar</label>
                        <select name="id_user" id="edit_id_user" required 
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-edu-blue focus:border-transparent">
                            <option value="">Pilih Guru</option>
                            @foreach($availableGuru as $guru)
                                <option value="{{ $guru->id_user }}">{{ $guru->nama }}</option>
                            @endforeach
                        </select>
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
let mapelTable;
let currentTA = '{{ $selectedTA }}';
let currentKelas = '{{ $selectedKelas }}';

// Get CSRF token from hidden input
function getCSRFToken() {
    return document.getElementById('csrf_token').value;
}

$(document).ready(function() {
    if (currentTA && currentKelas) {
        initDataTable();
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
});

function initDataTable() {
    if (mapelTable) {
        mapelTable.destroy();
    }
    
    mapelTable = $('#mapelTable').DataTable({
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
            { data: 'nama_mata_pelajaran', name: 'nama_mata_pelajaran', width: '30%' },
            { data: 'guru_nama', name: 'guru_nama', width: '25%' },
            { data: 'modul_count', name: 'modul_count', orderable: false, searchable: false, width: '15%' },
            { data: 'status', name: 'status', orderable: false, searchable: false, width: '15%' },
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
    if (mapelTable) {
        mapelTable.ajax.reload();
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

function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('editForm').reset();
}

// Form Handlers
$('#addForm').submit(function(e) {
    e.preventDefault();
    
    const mapelId = $('#id_mata_pelajaran').val();
    const guruId = $('#id_user').val();
    
    if (!mapelId || !guruId) {
        alert('Pilih mata pelajaran dan guru');
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Memproses...');
    
    const postData = {
        id_kelas_ta: $('input[name="id_kelas_ta"]').val(),
        id_mata_pelajaran: mapelId,
        id_user: guruId,
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
                alert(response.message || 'Berhasil menambahkan mata pelajaran');
            }
            
            closeAddModal();
            refreshTable();
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

$('#editForm').submit(function(e) {
    e.preventDefault();
    
    const kelasMP = $('#edit_id_kelas_mp').val();
    const guruId = $('#edit_id_user').val();
    const aktif = $('#edit_aktif').val();
    
    if (!guruId) {
        alert('Pilih guru');
        return;
    }
    
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Memproses...');
    
    const postData = {
        id_user: guruId,
        aktif: aktif,
        _token: getCSRFToken(),
        _method: 'PUT'
    };
    
    const baseUrl = window.location.href.split('?')[0];
    const updateUrl = baseUrl + '/' + kelasMP;
    
    $.ajax({
        url: updateUrl,
        method: 'POST',
        data: postData,
        success: function(response) {
            if (typeof showToast === 'function') {
                showToast(response);
            } else {
                alert(response.message || 'Berhasil memperbarui data');
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
function editMapel(id) {
    // Get data for edit
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
                
                $('#edit_id_kelas_mp').val(data.id_kelas_mp);
                $('#edit_mata_pelajaran').val(data.nama_mata_pelajaran);
                $('#edit_id_user').val(data.id_user);
                $('#edit_aktif').val(data.aktif);
                
                openEditModal();
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response?.message || 'Gagal mengambil data';
            
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

function removeMapel(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini dari kelas?')) {
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
                alert(response.message || 'Berhasil menghapus mata pelajaran');
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
        closeEditModal();
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
};
</script>
@endsection