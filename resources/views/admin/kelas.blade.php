@extends('layouts.home')

@section('css_tambahan')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">

<style>
    /* Custom DataTables Styling */
    .dataTables_wrapper .dataTables_length select {
        @apply border border-gray-300 rounded px-3 py-1 mr-2;
    }
    .dataTables_wrapper .dataTables_filter input {
        @apply border border-gray-300 rounded px-3 py-1 ml-2;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        @apply px-3 py-1 border border-gray-300 rounded mx-1 hover:bg-gray-100;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        @apply bg-blue-500 text-white hover:bg-blue-600;
    }
    table.dataTable thead th {
        @apply font-semibold text-gray-700;
    }
</style>
@endsection

@section('konten')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Master Data Kelas</h1>
                    <p class="text-sm text-gray-600 mt-1">Kelola data master kelas yang dapat digunakan di berbagai tahun ajaran</p>
                </div>
                <button onclick="createKelas()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Kelas
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="p-6">
            <table id="kelasTable" class="w-full">
                <thead>
                    <tr>
                        <th width="10%">No</th>
                        <th width="25%">Nama Kelas</th>
                        <th width="40%">Deskripsi</th>
                        <th width="15%">Status</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create/Edit Kelas -->
<div id="kelasModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900" id="kelasModalTitle">Tambah Kelas</h3>
            <button onclick="closeKelasModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="kelasForm">
            @csrf
            <input type="hidden" id="kelas_id" name="kelas_id">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Nama Kelas <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_kelas" name="nama_kelas" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: X IPA 1">
                <span class="text-red-500 text-xs hidden" id="error_nama_kelas"></span>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Deskripsi
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Deskripsi kelas (opsional)"></textarea>
                <span class="text-red-500 text-xs hidden" id="error_deskripsi"></span>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Status <span class="text-red-500">*</span>
                </label>
                <select id="aktif" name="aktif" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Y">Aktif</option>
                    <option value="N">Non-Aktif</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle"></i> Kelas aktif dapat digunakan di tahun ajaran
                </p>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeKelasModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js_tambahan')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#kelasTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('kelas.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'nama_kelas', name: 'nama_kelas'},
            {data: 'deskripsi', name: 'deskripsi'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        language: {
            "sEmptyTable": "Tidak ada data kelas",
            "sProcessing": "Sedang memproses...",
            "sLengthMenu": "Tampilkan _MENU_ data",
            "sZeroRecords": "Tidak ditemukan data yang sesuai",
            "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
            "sInfoFiltered": "(disaring dari _MAX_ data keseluruhan)",
            "sSearch": "Cari:",
            "oPaginate": {
                "sFirst": "Pertama",
                "sPrevious": "Sebelumnya",
                "sNext": "Selanjutnya",
                "sLast": "Terakhir"
            }
        }
    });
});

// Create Kelas
function createKelas() {
    document.getElementById('kelasModal').classList.remove('hidden');
    document.getElementById('kelasModalTitle').textContent = 'Tambah Kelas';
    document.getElementById('kelasForm').reset();
    document.getElementById('kelas_id').value = '';
    document.getElementById('aktif').value = 'Y';
    clearErrors();
}

// Close Modal
function closeKelasModal() {
    document.getElementById('kelasModal').classList.add('hidden');
    clearErrors();
}

// Edit Kelas
function editKelas(id) {
    fetch(`/kelas/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('kelasModal').classList.remove('hidden');
            document.getElementById('kelasModalTitle').textContent = 'Edit Kelas';
            document.getElementById('kelas_id').value = data.id_kelas;
            document.getElementById('nama_kelas').value = data.nama_kelas;
            document.getElementById('deskripsi').value = data.deskripsi || '';
            document.getElementById('aktif').value = data.aktif;
            clearErrors();
        })
        .catch(error => {
            showToast({
                success: false,
                message: 'Gagal mengambil data'
            });
        });
}

// Delete Kelas
function deleteKelas(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus kelas ini?')) return;
    
    fetch(`/kelas/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        showToast(data);
        if (data.success) {
            $('#kelasTable').DataTable().ajax.reload();
        }
    })
    .catch(error => {
        showToast({
            success: false,
            message: 'Terjadi kesalahan'
        });
    });
}

// Submit Form
document.getElementById('kelasForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('kelas_id').value;
    const url = id ? `/kelas/${id}` : '/kelas';
    const method = id ? 'PUT' : 'POST';
    
    const formData = {
        nama_kelas: document.getElementById('nama_kelas').value,
        deskripsi: document.getElementById('deskripsi').value,
        aktif: document.getElementById('aktif').value
    };
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (response.status === 422) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeKelasModal();
            showToast(data);
            $('#kelasTable').DataTable().ajax.reload();
        } else {
            showToast(data);
        }
    })
    .catch(error => {
        if (error.errors) {
            Object.keys(error.errors).forEach(key => {
                const errorElement = document.getElementById(`error_${key}`);
                if (errorElement) {
                    errorElement.textContent = error.errors[key][0];
                    errorElement.classList.remove('hidden');
                }
            });
        } else {
            showToast({
                success: false,
                message: 'Terjadi kesalahan'
            });
        }
    });
});

// Clear validation errors
function clearErrors() {
    document.querySelectorAll('[id^="error_"]').forEach(element => {
        element.textContent = '';
        element.classList.add('hidden');
    });
}
</script>
@endsection