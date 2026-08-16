@extends('layouts.home')

@section('konten')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Edit User</h2>
            </div>

            <!-- Form -->
            <form action="{{ route('users.update', $user->id_user) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <!-- Account Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Informasi Akun</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="username" value="{{ old('username', $user->username) }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror">
                            @error('username')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password (kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" name="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select name="id_status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('id_status') border-red-500 @enderror">
                                <option value="">Pilih Role</option>
                                <option value="siswa" {{ old('id_status', $user->profile->id_status ?? '') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                                <option value="guru" {{ old('id_status', $user->profile->id_status ?? '') == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="tendik" {{ old('id_status', $user->profile->id_status ?? '') == 'tendik' ? 'selected' : '' }}>Tendik</option>
                            </select>
                            @error('id_status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Admin Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Admin</label>
                            <select name="is_admin"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="N" {{ $user->is_admin == 'N' ? 'selected' : '' }}>Bukan Admin</option>
                                <option value="Y" {{ $user->is_admin == 'Y' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <!-- Status Aktif -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status Aktif</label>
                            <select name="aktif"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="Y" {{ $user->aktif == 'Y' ? 'selected' : '' }}>Aktif</option>
                                <option value="N" {{ $user->aktif == 'N' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Profile Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Informasi Profil</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" value="{{ old('nama', $user->profile->nama ?? '') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama') border-red-500 @enderror">
                            @error('nama')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sekolah -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sekolah</label>
                            <select name="id_sekolah" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('id_sekolah') border-red-500 @enderror">
                                <option value="">Pilih Sekolah</option>
                                @foreach($sekolah as $s)
                                    <option value="{{ $s->id_sekolah }}" {{ old('id_sekolah', $user->profile->id_sekolah ?? '') == $s->id_sekolah ? 'selected' : '' }}>
                                        {{ $s->nama_sekolah }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_sekolah')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $user->profile->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $user->profile->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $user->profile->tempat_lahir ?? '') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->profile->tanggal_lahir ?? '') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Agama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Agama</label>
                            <select name="agama"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Agama</option>
                                <option value="Islam" {{ old('agama', $user->profile->agama ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('agama', $user->profile->agama ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('agama', $user->profile->agama ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('agama', $user->profile->agama ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('agama', $user->profile->agama ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('agama', $user->profile->agama ?? '') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                        </div>

                        <!-- Alamat -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea name="alamat" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $user->profile->alamat ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('users.index') }}" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection