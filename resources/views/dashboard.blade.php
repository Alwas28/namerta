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
                        <span class="text-blue-600">EduLearn</span>
                    </h1>
                    <p class="text-lg sm:text-xl mb-6 sm:mb-8 text-gray-700 leading-relaxed">
                        Platform pembelajaran online yang menghubungkan mahasiswa dan dosen dengan teknologi terdepan. 
                        Akses ribuan kursus berkualitas dari mana saja, kapan saja.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mb-6 sm:mb-8">
                        <button class="px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transform hover:scale-105 transition-all shadow-xl">
                            <i class="fas fa-play mr-2"></i>
                            Mulai Belajar Sekarang
                        </button>
                        <button class="px-6 sm:px-8 py-3 sm:py-4 border-2 border-gray-700 text-gray-700 rounded-xl font-semibold hover:bg-gray-700 hover:text-white transition-all shadow-lg">
                            <i class="fas fa-info-circle mr-2"></i>
                            Pelajari Lebih Lanjut
                        </button>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 sm:gap-8">
                        <div class="text-center bg-white rounded-lg p-3 sm:p-4 shadow-lg">
                            <div class="text-xl sm:text-3xl font-bold text-gray-900">50K+</div>
                            <div class="text-sm sm:text-base text-gray-600 font-medium">Mahasiswa Aktif</div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-3 sm:p-4 shadow-lg">
                            <div class="text-xl sm:text-3xl font-bold text-gray-900">1000+</div>
                            <div class="text-sm sm:text-base text-gray-600 font-medium">Kursus Tersedia</div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-3 sm:p-4 shadow-lg">
                            <div class="text-xl sm:text-3xl font-bold text-gray-900">95%</div>
                            <div class="text-sm sm:text-base text-gray-600 font-medium">Tingkat Kepuasan</div>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image -->
                <div class="relative">
                    <div class="animate-float">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1471&q=80" 
                             alt="Students Learning" 
                             class="rounded-2xl shadow-2xl w-full h-64 sm:h-96 object-cover">
                        <!-- Overlay with play button -->
                        <div class="absolute inset-0 bg-black bg-opacity-20 rounded-2xl flex items-center justify-center">
                            <button class="w-16 h-16 sm:w-20 sm:h-20 bg-white bg-opacity-90 rounded-full flex items-center justify-center hover:bg-opacity-100 transition-all transform hover:scale-110">
                                <i class="fas fa-play text-blue-600 text-xl sm:text-2xl ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 animate-float hidden sm:block" style="animation-delay: -2s">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg">
                <i class="fas fa-book text-blue-600 text-xl"></i>
            </div>
        </div>
        <div class="absolute bottom-20 right-10 animate-float hidden sm:block" style="animation-delay: -4s">
            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg">
                <i class="fas fa-certificate text-blue-600 text-xl"></i>
            </div>
        </div>
    </section>


    
    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Kenapa Memilih EduLearn?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Platform pembelajaran yang dirancang khusus untuk memberikan pengalaman belajar terbaik
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-laptop text-primary-600 text-2xl"></i>
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
                        Diskusi dan berkolaborasi dengan sesama mahasiswa dan dosen dalam ruang virtual
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
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Sertifikat Digital</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Dapatkan sertifikat digital yang dapat diverifikasi untuk setiap kursus yang diselesaikan
                    </p>
                </div>
                
                <!-- Feature 6 -->
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-headset text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Support 24/7</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Tim support yang siap membantu Anda kapan saja jika mengalami kendala
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Courses Section -->
    <section id="courses" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Kursus Populer
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Pilihan kursus terbaik yang telah dipercaya ribuan mahasiswa
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Course 1 -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1">
                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" 
                         alt="Programming Course" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">Pemrograman</span>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                                <span class="text-gray-600 text-sm ml-1">4.8</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Pemrograman Web Modern</h3>
                        <p class="text-gray-600 text-sm mb-4">Pelajari HTML, CSS, JavaScript, dan framework modern untuk membangun website responsif</p>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <span>24 jam</span>
                            </div>
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-users mr-1"></i>
                                <span>1,234 siswa</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-gray-900">
                                <span class="text-2xl font-bold">Gratis</span>
                            </div>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Mulai Belajar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Course 2 -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" 
                         alt="Data Science Course" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">Data Science</span>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                                <span class="text-gray-600 text-sm ml-1">4.9</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Analisis Data dengan Python</h3>
                        <p class="text-gray-600 text-sm mb-4">Kuasai Python, Pandas, NumPy, dan machine learning untuk analisis data profesional</p>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <span>36 jam</span>
                            </div>
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-users mr-1"></i>
                                <span>892 siswa</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-gray-900">
                                <span class="text-lg text-gray-500 line-through">Rp 299.000</span>
                                <span class="text-2xl font-bold ml-2">Rp 199.000</span>
                            </div>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Mulai Belajar
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Course 3 -->
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all transform hover:-translate-y-1">
                    <img src="https://images.unsplash.com/photo-1560472355-536de3962603?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" 
                         alt="UI/UX Design Course" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-purple-100 text-purple-800 text-sm font-medium px-3 py-1 rounded-full">Design</span>
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-400 text-sm"></i>
                                <span class="text-gray-600 text-sm ml-1">4.7</span>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">UI/UX Design Fundamentals</h3>
                        <p class="text-gray-600 text-sm mb-4">Pelajari prinsip design, prototyping, dan user research untuk menciptakan pengalaman digital terbaik</p>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-clock mr-1"></i>
                                <span>28 jam</span>
                            </div>
                            <div class="flex items-center text-gray-500 text-sm">
                                <i class="fas fa-users mr-1"></i>
                                <span>756 siswa</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="text-gray-900">
                                <span class="text-2xl font-bold">Rp 249.000</span>
                            </div>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Mulai Belajar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <button class="bg-gray-100 text-gray-800 px-8 py-3 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                    Lihat Semua Kursus
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Text Content -->
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
                        Tentang EduLearn
                    </h2>
                    <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                        EduLearn didirikan dengan misi untuk memberikan akses pendidikan berkualitas tinggi kepada semua orang, 
                        di mana pun dan kapan pun. Kami percaya bahwa teknologi dapat menjembatani kesenjangan pendidikan dan 
                        memberdayakan individu untuk mencapai potensi terbaik mereka.
                    </p>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Dengan lebih dari 1000 kursus dari instruktur terbaik, platform kami telah membantu ribuan mahasiswa 
                        mengembangkan keterampilan yang dibutuhkan di era digital ini.
                    </p>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center p-6 bg-white rounded-xl shadow-sm">
                            <div class="text-3xl font-bold text-blue-600 mb-2">5+</div>
                            <div class="text-gray-600">Tahun Pengalaman</div>
                        </div>
                        <div class="text-center p-6 bg-white rounded-xl shadow-sm">
                            <div class="text-3xl font-bold text-green-600 mb-2">200+</div>
                            <div class="text-gray-600">Instruktur Ahli</div>
                        </div>
                    </div>
                </div>
                
                <!-- Image -->
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" 
                         alt="About EduLearn" 
                         class="rounded-2xl shadow-xl w-full h-96 object-cover">
                    <!-- Overlay -->
                    <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-xl shadow-lg">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-award text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">Sertifikat Terakreditasi</div>
                                <div class="text-sm text-gray-600">Diakui industri</div>
                            </div>
                        </div>
                    </div>
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
                    Ada pertanyaan? Tim kami siap membantu Anda memulai perjalanan belajar
                </p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-1 gap-12">
                <!-- Contact Info -->
                <div class="max-w-4xl mx-auto">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-8 text-center">Informasi Kontak</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Address -->
                            <div class="text-center bg-gray-50 rounded-xl p-6">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-2">Alamat</h4>
                                <p class="text-gray-600 text-sm">Jl. Pendidikan No. 123<br>Jakarta Selatan, 12345</p>
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
                                <p class="text-gray-600 text-sm">info@edulearn.id<br>support@edulearn.id</p>
                            </div>
                            
                            <!-- Hours -->
                            <div class="text-center bg-gray-50 rounded-xl p-6">
                                <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                                </div>
                                <h4 class="font-semibold text-gray-900 mb-2">Jam Operasional</h4>
                                <p class="text-gray-600 text-sm">Senin - Jumat: 08:00 - 17:00<br>Sabtu: 09:00 - 15:00</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Social Media & Additional Info -->
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Ikuti Kami & Informasi Lainnya</h3>
                        
                        <!-- Social Media -->
                        <div class="flex justify-center space-x-4 mb-8">
                            <a href="#" class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center text-white hover:bg-blue-700 transition-colors">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-blue-400 rounded-lg flex items-center justify-center text-white hover:bg-blue-500 transition-colors">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-pink-600 rounded-lg flex items-center justify-center text-white hover:bg-pink-700 transition-colors">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-blue-700 rounded-lg flex items-center justify-center text-white hover:bg-blue-800 transition-colors">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="w-12 h-12 bg-red-600 rounded-lg flex items-center justify-center text-white hover:bg-red-700 transition-colors">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                        
                        <!-- Additional Contact Methods -->
                        <div class="bg-blue-50 rounded-xl p-6">
                            <h4 class="font-semibold text-gray-900 mb-4">Cara Lain Menghubungi Kami</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div class="flex items-center justify-center space-x-2">
                                    <i class="fab fa-whatsapp text-green-600"></i>
                                    <span class="text-gray-700">WhatsApp: +62 811 2345 6789</span>
                                </div>
                                <div class="flex items-center justify-center space-x-2">
                                    <i class="fab fa-telegram text-blue-600"></i>
                                    <span class="text-gray-700">Telegram: @edulearn_support</span>
                                </div>
                                <div class="flex items-center justify-center space-x-2">
                                    <i class="fas fa-headset text-purple-600"></i>
                                    <span class="text-gray-700">Live Chat: 24/7 di website</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Remaining sections (Features, Courses, About, Contact, etc.) remain the same -->
    <!-- For brevity, I'll include the key section with mobile optimization -->
    
    <!-- CTA Section -->
    <section class="py-12 sm:py-20 bg-blue-600">
        <div class="w-full lg:max-w-4xl lg:mx-auto text-center px-3 sm:px-4 lg:px-8">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-white mb-4 sm:mb-6">
                Siap Memulai Perjalanan Belajar Anda?
            </h2>
            <p class="text-lg sm:text-xl text-blue-100 mb-6 sm:mb-8">
                Bergabunglah dengan ribuan mahasiswa lainnya dan rasakan pengalaman belajar yang berbeda
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="openRegisterModal()" class="px-6 sm:px-8 py-3 sm:py-4 bg-white text-blue-600 rounded-xl font-semibold hover:bg-gray-50 transform hover:scale-105 transition-all shadow-lg">
                    Daftar Gratis Sekarang
                </button>
                <button class="px-6 sm:px-8 py-3 sm:py-4 border-2 border-white text-white rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-all">
                    Lihat Demo
                </button>
            </div>
        </div>
    </section>
@endsection
