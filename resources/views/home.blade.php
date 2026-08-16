@extends('layouts.home')

@section('konten')
    <!-- Hero Section -->
    <section id="home" class="pt-20 gradient-bg hero-pattern relative overflow-hidden">
        <div class="w-full lg:max-w-7xl lg:mx-auto px-3 sm:px-4 lg:px-8 py-12 sm:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 sm:gap-12 items-center">
                <!-- Text Content -->
                <div class="animate-fade-in-up">
                    <h1 class="text-3xl sm:text-4xl md:text-6xl font-bold leading-tight mb-4 sm:mb-6 text-gray-900">
                        Belajar Tanpa Batas dengan
                        <span class="text-blue-600">{{ config('app.name', 'EduLearn') }}</span>
                    </h1>
                    <p class="text-lg sm:text-xl mb-6 sm:mb-8 text-gray-700 leading-relaxed">
                        Platform pembelajaran online yang menghubungkan siswa dan guru dengan teknologi terdepan. 
                        Akses materi pembelajaran berkualitas dari mana saja, kapan saja.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mb-6 sm:mb-8">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all shadow-xl text-center">
                                <i class="fas fa-home mr-2"></i>
                                Dashboard
                            </a>
                        @else
                            <button onclick="openLoginModal()" class="px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all shadow-xl">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Login Sekarang
                            </button>
                        @endauth
                        <a href="#courses" class="px-6 sm:px-8 py-3 sm:py-4 border-2 border-gray-700 text-gray-700 rounded-xl font-semibold hover:bg-gray-700 hover:text-white transition-all shadow-lg text-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Jelajahi Kursus
                        </a>
                    </div>
                    
                    <!-- Stats Dinamis -->
                    <div class="grid grid-cols-3 gap-4 sm:gap-8">
                        <div class="text-center bg-white rounded-lg p-3 sm:p-4 shadow-lg">
                            <div class="text-xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_siswa']) }}+</div>
                            <div class="text-sm sm:text-base text-gray-600 font-medium">Siswa Aktif</div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-3 sm:p-4 shadow-lg">
                            <div class="text-xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_kursus']) }}+</div>
                            <div class="text-sm sm:text-base text-gray-600 font-medium">Kursus Tersedia</div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-3 sm:p-4 shadow-lg">
                            <div class="text-xl sm:text-3xl font-bold text-gray-900">{{ number_format($stats['total_guru']) }}+</div>
                            <div class="text-sm sm:text-base text-gray-600 font-medium">Guru Pengajar</div>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="relative">
                    <div class="animate-float">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1471&q=80" 
                             alt="Students Learning" 
                             class="rounded-2xl shadow-2xl w-full h-64 sm:h-96 object-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Kenapa Memilih Kami?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Platform pembelajaran yang dirancang untuk memberikan pengalaman belajar terbaik
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-laptop text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Akses 24/7</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Belajar kapan saja dan di mana saja dengan akses unlimited ke semua materi pembelajaran
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-users text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Kolaborasi Tim</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Diskusi dan berkolaborasi dengan sesama siswa dan guru dalam ruang virtual
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Tracking Progress</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Monitor kemajuan belajar dengan analitik detail dan laporan komprehensif
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-orange-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Mobile Friendly</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Interface responsif yang dapat diakses dengan sempurna di semua perangkat
                    </p>
                </div>
                
                <!-- Feature 5 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-certificate text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Materi Berkualitas</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Materi pembelajaran yang disusun oleh guru berpengalaman dan profesional
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-headset text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Support Cepat</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Tim support yang siap membantu Anda jika mengalami kendala
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Courses Section - DINAMIS -->
    <section id="courses" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Mata Pelajaran Tersedia
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Pilihan mata pelajaran yang tersedia di sistem kami
                </p>
            </div>
            
            @if($mataPelajaran->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($mataPelajaran as $mp)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1">
                    <div class="h-48 bg-gradient-to-r {{ $loop->iteration % 3 == 0 ? 'from-purple-500 to-purple-600' : ($loop->iteration % 2 == 0 ? 'from-green-500 to-green-600' : 'from-blue-500 to-blue-600') }} flex items-center justify-center">
                        <i class="fas fa-book text-white text-6xl"></i>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">Mata Pelajaran</span>
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-400 text-sm mr-1"></i>
                                <span class="text-gray-600 text-sm">{{ $mp->total_siswa }} siswa</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $mp->nama_mata_pelajaran }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($mp->deskripsi ?? 'Pelajari materi berkualitas dengan metode pembelajaran modern dan interaktif', 100) }}</p>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-book-open mr-1"></i>
                                <span>{{ $mp->total_modul }} modul</span>
                            </div>
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-chalkboard-teacher mr-1"></i>
                                <span>{{ $mp->total_guru }} guru</span>
                            </div>
                        </div>
                        @auth
                            <a href="{{ route('dashboard') }}" class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Lihat Detail
                            </a>
                        @else
                            <button onclick="openLoginModal()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Login untuk Akses
                            </button>
                        @endauth
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-book text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">Belum ada mata pelajaran tersedia</p>
            </div>
            @endif
            
            @if($mataPelajaran->count() > 0)
            <div class="text-center mt-12">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-gray-100 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-200 transition-colors font-medium inline-block">
                        Lihat Semua Kursus
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                @else
                    <button onclick="openLoginModal()" class="bg-gray-100 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                        Login untuk Lihat Semua
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                @endauth
            </div>
            @endif
        </div>
    </section>

    <!-- About Section - DINAMIS -->
    <section id="about" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Text Content -->
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                        Tentang Kami
                    </h2>
                    <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                        Platform Learning Management System yang didirikan untuk memberikan akses pendidikan berkualitas 
                        kepada semua siswa. Kami percaya bahwa teknologi dapat menjembatani kesenjangan pendidikan dan 
                        memberdayakan siswa untuk mencapai potensi terbaik mereka.
                    </p>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Dengan {{ number_format($stats['total_kursus']) }} modul pembelajaran dan {{ number_format($stats['total_guru']) }} guru pengajar, 
                        platform kami telah membantu {{ number_format($stats['total_siswa']) }} siswa 
                        mengembangkan keterampilan yang dibutuhkan.
                    </p>
                    
                    <!-- Stats Dinamis -->
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center p-6 bg-white rounded-xl shadow-sm">
                            <div class="text-3xl font-bold text-blue-600 mb-2">{{ $stats['total_kelas'] }}+</div>
                            <div class="text-gray-600">Kelas Aktif</div>
                        </div>
                        <div class="text-center p-6 bg-white rounded-xl shadow-sm">
                            <div class="text-3xl font-bold text-green-600 mb-2">{{ number_format($stats['total_guru']) }}+</div>
                            <div class="text-gray-600">Guru Berpengalaman</div>
                        </div>
                    </div>
                </div>
                
                <!-- Image -->
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                         alt="About Us" 
                         class="rounded-2xl shadow-xl w-full h-96 object-cover">
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Hubungi Kami
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Ada pertanyaan? Tim kami siap membantu Anda
                </p>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Address -->
                    <div class="text-center bg-gray-50 rounded-xl p-6">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Alamat</h4>
                        <p class="text-gray-600 text-sm">{{ $sekolah->alamat ?? 'Alamat sekolah' }}<br>{{ $sekolah->kota ?? '' }}, {{ $sekolah->provinsi ?? '' }}</p>
                    </div>
                    
                    <!-- Phone -->
                    <div class="text-center bg-gray-50 rounded-xl p-6">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-phone text-green-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Telepon</h4>
                        <p class="text-gray-600 text-sm">+62 21 1234 5678<br>+62 811 2345 6789</p>
                    </div>
                    
                    <!-- Email -->
                    <div class="text-center bg-gray-50 rounded-xl p-6">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-envelope text-purple-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Email</h4>
                        <p class="text-gray-600 text-sm">info@sekolah.id<br>support@sekolah.id</p>
                    </div>
                    
                    <!-- Hours -->
                    <div class="text-center bg-gray-50 rounded-xl p-6">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-orange-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-900 mb-2">Jam Operasional</h4>
                        <p class="text-gray-600 text-sm">Senin - Jumat<br>07:00 - 15:00 WIB</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-12 sm:py-20 bg-blue-600">
        <div class="w-full lg:max-w-4xl lg:mx-auto text-center px-3 sm:px-4 lg:px-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-4 sm:mb-6">
                Siap Memulai Perjalanan Belajar Anda?
            </h2>
            <p class="text-lg sm:text-xl text-blue-100 mb-6 sm:mb-8">
                Bergabunglah dengan {{ number_format($stats['total_siswa']) }} siswa lainnya dan rasakan pengalaman belajar yang berbeda
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                    <button onclick="openRegisterModal()" class="px-6 sm:px-8 py-3 sm:py-4 bg-white text-blue-600 rounded-xl font-semibold hover:bg-gray-50 transform hover:scale-105 transition-all shadow-lg">
                        Daftar Gratis Sekarang
                    </button>
                    <button onclick="openLoginModal()" class="px-6 sm:px-8 py-3 sm:py-4 border-2 border-white text-white rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-all">
                        Login
                    </button>
                @else
                    <a href="{{ route('dashboard') }}" class="px-6 sm:px-8 py-3 sm:py-4 bg-white text-blue-600 rounded-xl font-semibold hover:bg-gray-50 transform hover:scale-105 transition-all shadow-lg inline-block">
                        Ke Dashboard
                    </a>
                @endguest
            </div>
        </div>
    </section>
@endsection

@section('js_tambahan')
<script>
        // Desktop-only dropdown functions
        function toggleAdminDropdown() {
            const dropdown = document.getElementById('adminDropdown');
            if (window.innerWidth >= 1024) { // Only work on desktop
                dropdown.classList.toggle('hidden');
            }
        }

        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById(`arrow-${submenuId}`);
            
            if (window.innerWidth >= 1024) { // Only work on desktop
                submenu.classList.toggle('hidden');
                if (arrow) {
                    arrow.classList.toggle('rotate-90');
                }
            }
        }

        // Mobile menu functions
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        function toggleMobileAdminDropdown() {
            const dropdown = document.getElementById('mobileAdminDropdown');
            const arrow = document.getElementById('mobileAdminArrow');
            
            dropdown.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        function toggleMobileSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById(`arrow-${submenuId}`);
            
            submenu.classList.toggle('hidden');
            if (arrow) {
                arrow.classList.toggle('rotate-90');
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (window.innerWidth >= 1024) {
                const adminDropdown = document.getElementById('adminDropdown');
                const adminButton = event.target.closest('button[onclick*="toggleAdminDropdown"]');
                
                if (!adminButton && !adminDropdown.contains(event.target)) {
                    adminDropdown.classList.add('hidden');
                }
            }
            
            // Close mobile menu when clicking outside
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileButton = event.target.closest('button[onclick*="toggleMobileMenu"]');
            
            if (!mobileButton && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
                // Also close all mobile submenus
                const mobileAdminDropdown = document.getElementById('mobileAdminDropdown');
                const mobileAdminArrow = document.getElementById('mobileAdminArrow');
                mobileAdminDropdown.classList.add('hidden');
                mobileAdminArrow.classList.remove('rotate-180');
                
                // Close all mobile submenus
                const mobileSubmenus = ['mobileUserManagement', 'mobileStudents', 'mobileInstructors', 'mobileCourseManagement', 'mobileCourses', 'mobileSystemSettings'];
                mobileSubmenus.forEach(submenuId => {
                    const submenu = document.getElementById(submenuId);
                    const arrow = document.getElementById(`arrow-${submenuId}`);
                    if (submenu) submenu.classList.add('hidden');
                    if (arrow) arrow.classList.remove('rotate-90');
                });
            }
        });

        // Modal Functions
        function openLoginModal() {
            document.getElementById('loginModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openRegisterModal() {
            document.getElementById('registerModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function switchToRegister() {
            closeLoginModal();
            openRegisterModal();
        }

        function switchToLogin() {
            closeRegisterModal();
            openLoginModal();
        }

        // Toggle Password Visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }

        // Form Handlers
        document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const errorContainer = document.getElementById('loginErrors');
    const errorList = document.getElementById('errorList');
    const submitButton = document.querySelector('#loginForm button[type="submit"]');
    const buttonText = submitButton.textContent;
    
    // Clear previous errors
    errorContainer.classList.add('hidden');
    errorList.innerHTML = '';
    
    // Client-side validation
    const errors = [];
    
    if (!email.trim()) {
        errors.push('Email harus diisi');
    } else if (!email.includes('@') || !email.includes('.')) {
        errors.push('Format email tidak valid');
    }
    
    if (!password.trim()) {
        errors.push('Password harus diisi');
    } else if (password.length < 6) {
        errors.push('Password minimal 6 karakter');
    }
    
    // Show validation errors
    if (errors.length > 0) {
        showLoginErrors(errors);
        return;
    }
    
    // Show loading
    submitButton.disabled = true;
    submitButton.textContent = 'Memproses...';
    
    // Send to route
    const form = document.getElementById('loginForm');
    const formData = new FormData(form);
    
    

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })

    .then(response => response.json())
    .then(data => {
        submitButton.disabled = false;
        submitButton.textContent = buttonText;
        
        if (data.success === true) {
            closeLoginModal();
            window.location.href = data.redirect || '/dashboard';
        } else {
            // Login failed - show error in modal
            const errorMessage = data.message || data.error || 'Login gagal';
            showLoginErrors([errorMessage]);
        }
    })
    .catch(error => {
        submitButton.disabled = false;
        submitButton.textContent = buttonText;
        showLoginErrors(['Terjadi kesalahan server']);
    });
});
        function showLoginErrors(errors) {
            const errorContainer = document.getElementById('loginErrors');
            const errorList = document.getElementById('errorList');
            
            errorList.innerHTML = '';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            
            errorContainer.classList.remove('hidden');
            
            // Scroll to top of modal to show error
            const modalBody = errorContainer.closest('.px-6');
            modalBody.scrollTop = 0;
        }
        
        function simulateLoginResponse(email, password) {
            const submitButton = document.querySelector('#loginForm button[type="submit"]');
            const buttonText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            `;
            
            // Simulate server delay
            setTimeout(() => {
                // Restore button state
                submitButton.disabled = false;
                submitButton.textContent = buttonText;
                
                // Simulate different login scenarios for demo
                if (email === 'admin@eduverse.com' && password === 'password123') {
                    // Success - close modal and redirect
                    alert('Login berhasil! Akan diarahkan ke dashboard.');
                    closeLoginModal();
                    // In real app: window.location.href = '/dashboard';
                } else if (email === 'test@eduverse.com' && password === 'test123') {
                    // Success case 2
                    alert('Login berhasil! Selamat datang kembali.');
                    closeLoginModal();
                } else {
                    // Error cases - stay in modal and show errors
                    const errors = [];
                    
                    if (password === '123456') {
                        errors.push('Password terlalu lemah, gunakan kombinasi huruf dan angka');
                    } else if (email.includes('banned')) {
                        errors.push('Akun Anda telah dinonaktifkan, hubungi administrator');
                    } else if (!email.includes('@eduverse.com')) {
                        errors.push('Email tidak terdaftar dalam sistem');
                    } else {
                        errors.push('Email atau password salah');
                        errors.push('Pastikan email dan password yang Anda masukkan benar');
                    }
                    
                    showLoginErrors(errors);
                }
            }, 1500); // 1.5 second delay to simulate server response
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const terms = document.getElementById('terms').checked;
            
            if (!terms) {
                alert('Harap setujui syarat dan ketentuan');
                return;
            }
            
            console.log('Registration attempt:', { name, email, password });
            alert('Registration functionality would be implemented here');
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const loginModal = document.getElementById('loginModal');
            const registerModal = document.getElementById('registerModal');
            
            if (e.target === loginModal) {
                closeLoginModal();
            }
            if (e.target === registerModal) {
                closeRegisterModal();
            }
        });

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInputs = document.querySelectorAll('input[placeholder*="Cari kursus"]');
            searchInputs.forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const query = this.value;
                        if (query) {
                            alert(`Mencari kursus: "${query}"`);
                        }
                    }
                });
            });
        });

        // Close mobile menu when window is resized to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const mobileMenu = document.getElementById('mobileMenu');
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
    @endsection



