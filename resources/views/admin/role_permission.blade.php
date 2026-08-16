@extends('layouts.home')

@section('css_tambahan')
<style>
    /* Toggle Switch Style */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e0;
        transition: .4s;
        border-radius: 24px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: #10b981;
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    
    .module-row:hover {
        background-color: #f9fafb;
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
                    <h1 class="text-2xl font-bold text-gray-800">
                        Permissions for <span class="text-blue-600">{{ $role->nama_role }}</span>
                    </h1>
                    <p class="text-gray-600 text-sm mt-1">{{ $role->deskripsi }}</p>
                </div>
                <div class="space-x-2">
                    <button onclick="savePermissions()" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        Save Changes
                    </button>
                    <button onclick="resetPermissions()" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        Reset
                    </button>
                    <a href="{{ route('roles.index') }}" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-2 px-4 rounded-lg transition-colors">
                        Back
                    </a>
                </div>
            </div>
        </div>

        
        <!-- Permission Table -->
        <div class="p-6">
            <div class="overflow-x-auto">
                <div class="max-h-96 overflow-y-auto border rounded-lg">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-700">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="selectAllModules" onchange="toggleAllModules()" class="mr-2">
                                        Module
                                    </label>
                                </th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Read</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Create</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Update</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-700">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $modules = [
                                    'user' => 'USER',
                                    'tahun_ajaran' => 'TAHUN AJARAN',
                                    'sekolah' => 'SEKOLAH',
                                    'kelas' => 'KELAS',
                                    'mata_pelajaran' => 'MATA PELAJARAN',
                                    'modul' => 'MODUL',
                                    'materi' => 'MATERI',
                                    'tes_kompetensi' => 'TES KOMPETENSI',
                                    'jawaban' => 'JAWABAN',
                                    'report' => 'LAPORAN',
                                    'aksi_nyata' => 'AKSI NYATA'
                                ];
                            @endphp
                            
                            @foreach($modules as $key => $label)
                            <tr class="module-row border-b">
                                <td class="py-3 px-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" class="module-check mr-3" data-module="{{ $key }}" onchange="toggleModule('{{ $key }}')">
                                        <span class="font-medium text-gray-700">{{ $label }}</span>
                                    </label>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                            class="permission-toggle" 
                                            data-module="{{ $key }}" 
                                            data-action="read"
                                            data-permission="{{ $key }}.view"
                                            {{ in_array($key.'.view', $rolePermissions) ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                            class="permission-toggle" 
                                            data-module="{{ $key }}" 
                                            data-action="create"
                                            data-permission="{{ $key }}.create"
                                            {{ in_array($key.'.create', $rolePermissions) ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                            class="permission-toggle" 
                                            data-module="{{ $key }}" 
                                            data-action="update"
                                            data-permission="{{ $key }}.edit"
                                            {{ in_array($key.'.edit', $rolePermissions) ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td class="text-center py-3 px-4">
                                    <label class="toggle-switch">
                                        <input type="checkbox" 
                                            class="permission-toggle" 
                                            data-module="{{ $key }}" 
                                            data-action="delete"
                                            data-permission="{{ $key }}.delete"
                                            {{ in_array($key.'.delete', $rolePermissions) ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="mt-6 flex space-x-4">
                <button onclick="selectAllPermissions()" class="text-sm bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200">
                    Select All Permissions
                </button>
                <button onclick="deselectAllPermissions()" class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">
                    Deselect All Permissions
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_tambahan')
<script>
// Check module checkbox based on permissions
function updateModuleCheckboxes() {
    document.querySelectorAll('.module-check').forEach(checkbox => {
        const module = checkbox.dataset.module;
        const permissions = document.querySelectorAll(`.permission-toggle[data-module="${module}"]`);
        const checkedCount = Array.from(permissions).filter(p => p.checked).length;
        checkbox.checked = checkedCount > 0;
    });
}

// Toggle all permissions for a module
function toggleModule(module) {
    const moduleCheckbox = document.querySelector(`.module-check[data-module="${module}"]`);
    const permissions = document.querySelectorAll(`.permission-toggle[data-module="${module}"]`);
    
    permissions.forEach(permission => {
        permission.checked = moduleCheckbox.checked;
    });
}

// Toggle all modules
function toggleAllModules() {
    const selectAll = document.getElementById('selectAllModules');
    document.querySelectorAll('.module-check').forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        toggleModule(checkbox.dataset.module);
    });
}

// Select all permissions
function selectAllPermissions() {
    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        toggle.checked = true;
    });
    updateModuleCheckboxes();
}

// Deselect all permissions
function deselectAllPermissions() {
    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        toggle.checked = false;
    });
    updateModuleCheckboxes();
}

// Save permissions
function savePermissions() {
    const permissionNames = [];
    
    // Collect all checked permissions
    document.querySelectorAll('.permission-toggle:checked').forEach(toggle => {
        permissionNames.push(toggle.dataset.permission);
        console.log('Permission checked:', toggle.dataset.permission);
    });
    
    console.log('Total permissions to save:', permissionNames.length);
    console.log('Permissions:', permissionNames);
    
    // Jika tidak ada permissions yang dipilih, konfirmasi
    if (permissionNames.length === 0) {
        if (!confirm('No permissions selected. Remove all permissions for this role?')) {
            return;
        }
    }
    
    // Test dengan fetch sederhana
    const data = {
        permissions: permissionNames,
        _token: '{{ csrf_token() }}'
    };
    
    console.log('Sending data:', data);
    
    fetch('/roles/{{ $role->id_role }}/permissions/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert('Permissions saved successfully!');
            // Atau gunakan showToast jika ada
            if (typeof showToast === 'function') {
                showToast({
                    success: true,
                    message: 'Permissions saved successfully!'
                });
            }
        } else {
            alert('Failed to save: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving permissions: ' + error.message);
    });
}

// Test function untuk check apakah permissions ter-detect
function testPermissions() {
    const permissions = [];
    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        permissions.push({
            module: toggle.dataset.module,
            action: toggle.dataset.action,
            permission: toggle.dataset.permission,
            checked: toggle.checked
        });
    });
    console.table(permissions);
    return permissions;
}

// Reset permissions to original
function resetPermissions() {
    location.reload();
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateModuleCheckboxes();
    
    // Update module checkbox when individual permission changes
    document.querySelectorAll('.permission-toggle').forEach(toggle => {
        toggle.addEventListener('change', updateModuleCheckboxes);
    });
});
</script>
@endsection