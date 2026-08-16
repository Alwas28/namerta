@extends('layouts.home')

@section('css_tambahan')
<!-- DataTables CSS -->
<link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">

<style>
    /* Custom DataTables Styling for Tailwind */
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
    .dataTables_wrapper .dataTables_info {
        @apply text-sm text-gray-600 mt-2;
    }
    table.dataTable thead th {
        @apply font-semibold text-gray-700 uppercase text-xs tracking-wider;
    }
    table.dataTable tbody td {
        @apply text-sm text-gray-900;
    }
</style>
@endsection

@section('konten')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Role</h1>
                <button onclick="createRole()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tambah Role
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="p-6">
            <table id="roleTable" class="w-full">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Role</th>
                        <th>Deskripsi</th>
                        <th>Total User</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create/Edit Role -->
<div id="roleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900" id="roleModalTitle">Tambah Role</h3>
            <button onclick="closeRoleModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="roleForm">
            @csrf
            <input type="hidden" id="role_id" name="role_id">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Nama Role <span class="text-red-500">*</span>
                </label>
                <input type="text" id="nama_role" name="nama_role" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-red-500 text-xs hidden" id="error_nama_role"></span>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeRoleModal()" 
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

<!-- Modal Manage Permissions -->
<div id="permissionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">
                Kelola Permission - <span id="roleNamePermission"></span>
            </h3>
            <button onclick="closePermissionModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="permissionForm">
            @csrf
            <input type="hidden" id="permission_role_id">
            
            <div class="mb-4">
                <button type="button" onclick="selectAll()" 
                    class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 mr-2">
                    <i class="fas fa-check-square mr-1"></i>Pilih Semua
                </button>
                <button type="button" onclick="deselectAll()" 
                    class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                    <i class="fas fa-square mr-1"></i>Hapus Semua
                </button>
            </div>
            
            <div id="permissionList" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                <!-- Permissions will be loaded here -->
            </div>
            
            <div class="flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closePermissionModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    Simpan Permission
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
    $('#roleTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('roles.index') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'nama_role', name: 'nama_role'},
            {data: 'deskripsi', name: 'deskripsi'},
            {data: 'total_users', name: 'total_users', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        language: {
            "sEmptyTable": "Tidak ada data yang tersedia",
            "sProcessing": "Sedang memproses...",
            "sLengthMenu": "Tampilkan _MENU_ entri",
            "sZeroRecords": "Tidak ditemukan data yang sesuai",
            "sInfo": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "sInfoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
            "sInfoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
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

// Modal Functions
function createRole() {
    document.getElementById('roleModal').classList.remove('hidden');
    document.getElementById('roleModalTitle').textContent = 'Tambah Role';
    document.getElementById('roleForm').reset();
    document.getElementById('role_id').value = '';
}

function closeRoleModal() {
    document.getElementById('roleModal').classList.add('hidden');
}

function closePermissionModal() {
    document.getElementById('permissionModal').classList.add('hidden');
}

function editRole(id) {
    fetch(`/roles/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('roleModal').classList.remove('hidden');
            document.getElementById('roleModalTitle').textContent = 'Edit Role';
            document.getElementById('role_id').value = data.id_role;
            document.getElementById('nama_role').value = data.nama_role;
            document.getElementById('deskripsi').value = data.deskripsi || '';
        });
}

function deleteRole(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus role ini?')) return;
    
    fetch(`/roles/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        showToast(data);
        $('#roleTable').DataTable().ajax.reload();
    });
}

function managePermissions(id) {
    fetch(`/roles/${id}/permissions`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('permissionModal').classList.remove('hidden');
            document.getElementById('roleNamePermission').textContent = data.role.nama_role;
            document.getElementById('permission_role_id').value = data.role.id_role;
            
            let html = '';
            Object.keys(data.permissions).forEach(grup => {
                html += `<div class="bg-gray-50 rounded-lg p-4">`;
                html += `<h4 class="font-semibold text-gray-700 mb-3 capitalize">${grup.replace('_', ' ')}</h4>`;
                
                data.permissions[grup].forEach(permission => {
                    const checked = data.rolePermissions.includes(permission.id_permission) ? 'checked' : '';
                    html += `<label class="flex items-start mb-2 cursor-pointer">`;
                    html += `<input type="checkbox" class="permission-check mt-1 mr-2" 
                            value="${permission.id_permission}" ${checked}>`;
                    html += `<div>`;
                    html += `<span class="text-sm font-medium">${permission.nama_permission}</span><br>`;
                    html += `<span class="text-xs text-gray-500">${permission.deskripsi || ''}</span>`;
                    html += `</div>`;
                    html += `</label>`;
                });
                
                html += `</div>`;
            });
            
            document.getElementById('permissionList').innerHTML = html;
        });
}

// Form Submissions
document.getElementById('roleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('role_id').value;
    const url = id ? `/roles/${id}` : '/roles';
    const method = id ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            nama_role: document.getElementById('nama_role').value,
            deskripsi: document.getElementById('deskripsi').value
        })
    })
    .then(response => response.json())
    .then(data => {
        closeRoleModal();
        showToast(data);
        $('#roleTable').DataTable().ajax.reload();
    });
});

document.getElementById('permissionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('permission_role_id').value;
    const permissions = Array.from(document.querySelectorAll('.permission-check:checked'))
        .map(cb => cb.value);
    
    fetch(`/roles/${id}/permissions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ permissions })
    })
    .then(response => response.json())
    .then(data => {
        closePermissionModal();
        showToast(data);
    });
});

function selectAll() {
    document.querySelectorAll('.permission-check').forEach(cb => cb.checked = true);
}

function deselectAll() {
    document.querySelectorAll('.permission-check').forEach(cb => cb.checked = false);
}
</script>
@endsection