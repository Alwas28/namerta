@extends('layouts.home')

@section('css_tambahan')
<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .profile-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 30px;
        text-align: center;
        color: white;
        position: relative;
    }
    
    .btn-edit-mode {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        color: white;
        padding: 10px 20px;
        border: 2px solid white;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-edit-mode:hover {
        background: white;
        color: #667eea;
    }
    
    .photo-wrapper {
        position: relative;
        display: inline-block;
    }
    
    .profile-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid white;
        object-fit: cover;
        margin: 0 auto 20px;
        display: block;
        background: white;
    }
    
    .btn-change-photo {
        position: absolute;
        bottom: 25px;
        right: 5px;
        background: #3b82f6;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 3px solid white;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .btn-change-photo:hover {
        background: #2563eb;
        transform: scale(1.1);
    }
    
    .edit-mode .btn-change-photo {
        display: flex;
    }
    
    .profile-name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .profile-role {
        font-size: 16px;
        opacity: 0.9;
    }
    
    .profile-body {
        padding: 30px;
    }
    
    .info-group {
        margin-bottom: 30px;
    }
    
    .info-title {
        font-size: 18px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .info-row {
        display: grid;
        grid-template-columns: 200px 1fr;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
        align-items: center;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 600;
        color: #6b7280;
    }
    
    .info-value {
        color: #1f2937;
    }
    
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .view-mode .form-control {
        display: none;
    }
    
    .edit-mode .info-value-text {
        display: none;
    }
    
    .edit-mode .form-control {
        display: block;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
        padding: 12px 32px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
        padding: 12px 32px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
        padding: 12px 32px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .action-buttons {
        text-align: center;
        margin-top: 30px;
        display: none;
    }
    
    .edit-mode .action-buttons {
        display: block;
    }
    
    .alert {
        padding: 16px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }
    
    .badge-guru {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-siswa {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-tendik {
        background: #e0e7ff;
        color: #4338ca;
    }
    
    .empty-value {
        color: #9ca3af;
        font-style: italic;
    }
    
    .password-section {
        background: #f9fafb;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        display: none;
    }
    
    .edit-mode .password-section {
        display: block;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
    }
    
    .modal.show {
        display: flex;
    }
    
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 12px;
        max-width: 500px;
        width: 90%;
    }
    
    .modal-header {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 20px;
        color: #1f2937;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        font-size: 14px;
    }
</style>
@endsection

@section('konten')
<div class="profile-container">
    @if(session('success'))
    <div class="alert alert-success">
        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-error">
        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
        {{ session('error') }}
    </div>
    @endif
    
    @if($errors->any())
    <div class="alert alert-error">
        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif
    
    <div class="profile-card view-mode" id="profileCard">
        <form action="{{ route('profile.update') }}" method="POST" id="profileForm">
            @csrf
            @method('PUT')
            
            <!-- Profile Header -->
            <div class="profile-header">
                <button type="button" class="btn-edit-mode" onclick="toggleEditMode()">
                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <span id="editModeText">Edit Profile</span>
                </button>
                
                <div class="photo-wrapper">
                    @if($profile && $profile->foto)
                        <img src="{{ asset('storage/' . $profile->foto) }}" alt="Profile Photo" class="profile-photo" id="profilePhoto">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($profile->nama ?? $user->username) }}&size=150&background=667eea&color=fff" alt="Profile Photo" class="profile-photo" id="profilePhoto">
                    @endif
                    <button type="button" class="btn-change-photo" onclick="openPhotoModal()">
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="profile-name">{{ $profile->nama ?? $user->username }}</div>
                <div class="profile-role">
                    @if($profile && $profile->status)
                        <span class="badge badge-{{ $profile->status }}">
                            {{ ucfirst($profile->status) }}
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Profile Body -->
            <div class="profile-body">
                <!-- Informasi Akun -->
                <div class="info-group">
                    <div class="info-title">Informasi Akun</div>
                    
                    <div class="info-row">
                        <div class="info-label">Username</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $user->username }}</span>
                            <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Status Akun</div>
                        <div class="info-value">
                            @if($user->aktif == 'Y')
                                <span style="color: #10b981; font-weight: 600;">● Aktif</span>
                            @else
                                <span style="color: #ef4444; font-weight: 600;">● Tidak Aktif</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Bergabung Sejak</div>
                        <div class="info-value">{{ date('d F Y', strtotime($user->created_at)) }}</div>
                    </div>
                </div>
                
                <!-- Data Pribadi -->
                <div class="info-group">
                    <div class="info-title">Data Pribadi</div>
                    
                    <div class="info-row">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $profile->nama ?? '-' }}</span>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $profile->nama ?? '') }}" required>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">NIP/NIS</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $profile->nip_nis ?? '-' }}</span>
                            <input type="text" name="nip_nis" class="form-control" value="{{ old('nip_nis', $profile->nip_nis ?? '') }}">
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Tempat Lahir</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $profile->tempat_lahir ?? '-' }}</span>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $profile->tempat_lahir ?? '') }}">
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Tanggal Lahir</div>
                        <div class="info-value">
                            <span class="info-value-text">
                                @if($profile && $profile->tanggal_lahir)
                                    {{ date('d F Y', strtotime($profile->tanggal_lahir)) }}
                                @else
                                    -
                                @endif
                            </span>
                            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $profile->tanggal_lahir ?? '') }}">
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Jenis Kelamin</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $profile->jenis_kelamin ?? '-' }}</span>
                            <select name="jenis_kelamin" class="form-control">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('jenis_kelamin', $profile->jenis_kelamin ?? '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('jenis_kelamin', $profile->jenis_kelamin ?? '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Agama</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $profile->agama ?? '-' }}</span>
                            <select name="agama" class="form-control">
                                <option value="">Pilih Agama</option>
                                <option value="Islam" {{ old('agama', $profile->agama ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('agama', $profile->agama ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('agama', $profile->agama ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('agama', $profile->agama ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('agama', $profile->agama ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('agama', $profile->agama ?? '') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label">Alamat</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $profile->alamat ?? '-' }}</span>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $profile->alamat ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Informasi Sekolah -->
                <div class="info-group">
                    <div class="info-title">Informasi Sekolah</div>
                    
                    <div class="info-row">
                        <div class="info-label">Nama Sekolah</div>
                        <div class="info-value">
                            <span class="info-value-text">{{ $profile->nama_sekolah ?? '-' }}</span>
                            <select name="id_sekolah" class="form-control">
                                <option value="">{{ $profile->nama_sekolah ?? '-' }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Password Section (only in edit mode) -->
                <div class="password-section">
                    <div class="info-title">Ubah Password (Opsional)</div>
                    <button type="button" class="btn-danger" onclick="openPasswordModal()">
                        <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Ubah Password
                    </button>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn-primary">
                        <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Perubahan
                    </button>
                    <button type="button" class="btn-secondary" onclick="cancelEdit()" style="margin-left: 10px;">
                        <svg style="width: 20px; height: 20px; display: inline-block; vertical-align: middle; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Upload Photo -->
<div id="photoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Upload Foto Profile</div>
        <form action="{{ route('profile.uploadPhoto') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Pilih Foto</label>
                <input type="file" name="photo" class="form-control" accept="image/*" required>
                <small style="color: #6b7280; display: block; margin-top: 5px;">Format: JPG, JPEG, PNG. Maksimal 2MB</small>
            </div>
            <div style="text-align: right;">
                <button type="button" class="btn-secondary" onclick="closePhotoModal()">Batal</button>
                <button type="submit" class="btn-primary" style="margin-left: 10px;">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Change Password -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Ubah Password</div>
        <form action="{{ route('profile.changePassword') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Password Lama</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="new_password" class="form-control" required minlength="6">
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="new_password_confirmation" class="form-control" required minlength="6">
            </div>
            <div style="text-align: right;">
                <button type="button" class="btn-secondary" onclick="closePasswordModal()">Batal</button>
                <button type="submit" class="btn-primary" style="margin-left: 10px;">Ubah Password</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js_tambahan')
<script>
function toggleEditMode() {
    const card = document.getElementById('profileCard');
    const editButton = document.getElementById('editModeText');
    
    if (card.classList.contains('view-mode')) {
        card.classList.remove('view-mode');
        card.classList.add('edit-mode');
        editButton.textContent = 'Mode Edit';
    } else {
        card.classList.remove('edit-mode');
        card.classList.add('view-mode');
        editButton.textContent = 'Edit Profile';
    }
}

function cancelEdit() {
    const card = document.getElementById('profileCard');
    const editButton = document.getElementById('editModeText');
    
    card.classList.remove('edit-mode');
    card.classList.add('view-mode');
    editButton.textContent = 'Edit Profile';
    
    // Reset form
    document.getElementById('profileForm').reset();
}

function openPhotoModal() {
    document.getElementById('photoModal').classList.add('show');
}

function closePhotoModal() {
    document.getElementById('photoModal').classList.remove('show');
}

function openPasswordModal() {
    document.getElementById('passwordModal').classList.add('show');
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.remove('show');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const photoModal = document.getElementById('photoModal');
    const passwordModal = document.getElementById('passwordModal');
    
    if (event.target == photoModal) {
        closePhotoModal();
    }
    if (event.target == passwordModal) {
        closePasswordModal();
    }
}
</script>
@endsection