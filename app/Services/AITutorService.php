<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AITutorService
{
    protected $apiKey;
    protected $apiUrl;
    protected $model;
    protected $fallbackEnabled;
    protected $fallbackDelay;

    public function __construct()
    {
        $this->apiKey = config('ai.openai.api_key');
        $this->apiUrl = config('ai.openai.api_url', 'https://api.openai.com/v1/chat/completions');
        $this->model = config('ai.openai.model', 'gpt-3.5-turbo');
        $this->fallbackEnabled = config('ai.fallback.enabled', true);
        $this->fallbackDelay = config('ai.fallback.response_delay', 1500);
    }

    public function generateResponse(string $message, array $context): string
    {
        try {
            // Prioritas pertama: gunakan OpenAI API jika tersedia
            if ($this->apiKey && $this->shouldUseAPI()) {
                $apiResponse = $this->callOpenAI($message, $context);
                if ($apiResponse !== null) {
                    return $apiResponse;
                }
            }

            // Fallback jika API tidak tersedia atau gagal
            if ($this->fallbackEnabled) {
                Log::info('Using fallback response for AI chat', [
                    'reason' => $this->apiKey ? 'API call failed' : 'No API key',
                    'message_preview' => substr($message, 0, 50)
                ]);
                
                return $this->getFallbackResponse($message, $context);
            }

            // Jika fallback disabled dan API gagal
            return 'Maaf, sistem AI sedang tidak tersedia. Silakan coba lagi nanti.';

        } catch (\Exception $e) {
            Log::error('AI Tutor Service Critical Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->fallbackEnabled 
                ? $this->getFallbackResponse($message, $context)
                : 'Terjadi kesalahan sistem. Silakan hubungi administrator.';
        }
    }

    protected function callOpenAI(string $message, array $context): ?string
    {
        try {
            $systemPrompt = $this->buildSystemPrompt($context);
            
            // Cache key untuk menghindari request duplikat
            $cacheKey = 'ai_response_' . md5($systemPrompt . $message);
            
            // Check cache first (optional, untuk menghemat API calls)
            if (config('ai.cache_enabled', false)) {
                $cached = Cache::get($cacheKey);
                if ($cached) {
                    Log::info('Returning cached AI response');
                    return $cached;
                }
            }

            Log::info('Calling OpenAI API', [
                'model' => $this->model,
                'message_length' => strlen($message),
                'context' => $context['learning_component'] ?? 'unknown'
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(30)->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
                'presence_penalty' => 0.1,
                'frequency_penalty' => 0.1,
                'top_p' => 0.9
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['choices'][0]['message']['content'])) {
                    $aiResponse = trim($data['choices'][0]['message']['content']);
                    
                    // Cache response if caching enabled
                    if (config('ai.cache_enabled', false)) {
                        Cache::put($cacheKey, $aiResponse, now()->addMinutes(30));
                    }
                    
                    Log::info('OpenAI API response received successfully', [
                        'response_length' => strlen($aiResponse),
                        'usage' => $data['usage'] ?? null
                    ]);
                    
                    return $aiResponse;
                } else {
                    Log::error('OpenAI API response missing content', ['response' => $data]);
                }
            } else {
                $errorBody = $response->body();
                Log::error('OpenAI API HTTP Error', [
                    'status' => $response->status(),
                    'body' => $errorBody
                ]);
                
                // Parse specific error types
                if ($response->status() === 401) {
                    Log::error('OpenAI API: Invalid API Key');
                } elseif ($response->status() === 429) {
                    Log::error('OpenAI API: Rate limit exceeded');
                } elseif ($response->status() === 500) {
                    Log::error('OpenAI API: Server error');
                }
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('OpenAI API Connection Error: ' . $e->getMessage());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('OpenAI API Request Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('OpenAI API Unexpected Error: ' . $e->getMessage());
        }

        return null;
    }

    protected function shouldUseAPI(): bool
    {
        // Cek berbagai kondisi untuk menentukan kapan menggunakan API
        
        // 1. Cek apakah API key valid format
        if (!$this->apiKey || !str_starts_with($this->apiKey, 'sk-')) {
            return false;
        }

        // 2. Cek apakah ada rate limiting active
        if (Cache::has('openai_rate_limit')) {
            Log::info('OpenAI rate limit active, using fallback');
            return false;
        }

        // 3. Cek status API health (optional)
        if (config('ai.check_api_health', false)) {
            return $this->checkAPIHealth();
        }

        return true;
    }

    protected function checkAPIHealth(): bool
    {
        try {
            // Simple health check dengan request minimal
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(5)->get('https://api.openai.com/v1/models');
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('OpenAI API health check failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function buildSystemPrompt(array $context): string
    {
        $mataPelajaran = $context['mata_pelajaran'] ?? 'Pembelajaran';
        $modulName = $context['modul_name'] ?? 'Modul';
        $materiName = $context['materi_name'] ?? 'Materi';
        $component = $context['learning_component'] ?? 'eksplorasi_konsep';
        $userRole = $context['user_role'] ?? 'student';
        $availableComponents = $context['available_components'] ?? [];

        $componentNames = [
            'mulai_dari_diri' => 'Mulai Dari Diri',
            'eksplorasi_konsep' => 'Eksplorasi Konsep', 
            'ruang_kolaborasi' => 'Ruang Kolaborasi',
            'refleksi_terbimbing' => 'Refleksi Terbimbing',
            'demonstrasi_konseptual' => 'Demonstrasi Konseptual',
            'elaborasi_pemahaman' => 'Elaborasi Pemahaman'
        ];

        $currentComponent = $componentNames[$component] ?? ucwords(str_replace('_', ' ', $component));
        $availableList = array_keys($availableComponents);
        $availableComponentsText = empty($availableList) ? 'Belum tersedia' : 
            implode(', ', array_map(fn($comp) => $componentNames[$comp] ?? $comp, $availableList));

        return "Anda adalah AI Tutor cerdas yang membantu pembelajaran online dengan model 6 komponen pembelajaran. Berikan respons yang personal, relevan, dan mendidik.

KONTEKS PEMBELAJARAN:
- Mata Pelajaran: {$mataPelajaran}
- Modul: {$modulName}  
- Materi: {$materiName}
- Komponen Aktif: {$currentComponent}
- Komponen Tersedia: {$availableComponentsText}
- Peran Pengguna: " . ucfirst($userRole) . "

MODEL 6 KOMPONEN PEMBELAJARAN:
1. Mulai Dari Diri: Refleksi personal, mengaitkan dengan pengalaman
2. Eksplorasi Konsep: Memahami teori, konsep inti, definisi
3. Ruang Kolaborasi: Diskusi, sharing perspektif, kerja sama
4. Refleksi Terbimbing: Analisis mendalam, evaluasi pembelajaran
5. Demonstrasi Konseptual: Aplikasi, praktik, show understanding
6. Elaborasi Pemahaman: Transfer knowledge, koneksi lintas topik

GAYA KOMUNIKASI:
- Gunakan bahasa Indonesia yang natural dan mudah dipahami
- Tone ramah, mendukung, seperti tutor berpengalaman
- Berikan penjelasan step-by-step yang jelas
- Gunakan analogi dan contoh konkret yang relevan
- Ajukan pertanyaan yang memancing pemikiran kritis
- Sesuaikan respons dengan komponen pembelajaran aktif

PEDOMAN RESPONS:
- Fokus pada komponen '{$currentComponent}' saat ini
- Maksimal 2-3 paragraf yang padat dan bermakna
- Jika pertanyaan di luar konteks, arahkan kembali dengan lembut
- Dorong eksplorasi lebih lanjut sesuai tahap pembelajaran
- Hindari memberikan jawaban langsung untuk tugas/ujian
- Prioritaskan pemahaman konsep over hafalan

ADAPTASI BERDASARKAN KOMPONEN:
- Mulai Dari Diri: Gali pengalaman pribadi, refleksi awal
- Eksplorasi Konsep: Jelaskan teori, berikan contoh, analogi
- Ruang Kolaborasi: Dorong diskusi, multiple perspectives
- Refleksi Terbimbing: Ajukan pertanyaan reflektif mendalam
- Demonstrasi Konseptual: Minta penjelasan balik, aplikasi
- Elaborasi Pemahaman: Hubungkan dengan konteks luas, real-world

Berikan respons yang membantu siswa memahami '{$materiName}' dalam konteks '{$currentComponent}' dengan cara yang engaging dan edukatif.";
    }

    protected function getFallbackResponse(string $message, array $context): string
    {
        // Simulate thinking delay untuk memberikan kesan natural
        if ($this->fallbackDelay > 0) {
            usleep($this->fallbackDelay * 1000);
        }

        $modulName = $context['modul_name'] ?? 'modul ini';
        $materiName = $context['materi_name'] ?? 'materi ini';
        $component = $context['learning_component'] ?? 'pembelajaran';
        $mataPelajaran = $context['mata_pelajaran'] ?? 'mata pelajaran';
        
        $componentNames = [
            'mulai_dari_diri' => 'Mulai Dari Diri',
            'eksplorasi_konsep' => 'Eksplorasi Konsep',
            'ruang_kolaborasi' => 'Ruang Kolaborasi', 
            'refleksi_terbimbing' => 'Refleksi Terbimbing',
            'demonstrasi_konseptual' => 'Demonstrasi Konseptual',
            'elaborasi_pemahaman' => 'Elaborasi Pemahaman'
        ];
        
        $componentName = $componentNames[$component] ?? ucwords(str_replace('_', ' ', $component));
        $message = strtolower(trim($message));
        
        // Analisis kata kunci lebih sophisticated
        $keywords = [
            'konsep' => ['konsep', 'pengertian', 'definisi', 'arti', 'makna'],
            'contoh' => ['contoh', 'penerapan', 'aplikasi', 'implementasi', 'praktik'],
            'refleksi' => ['refleksi', 'pengalaman', 'perasaan', 'pemikiran', 'renungan'],
            'diskusi' => ['diskusi', 'kolaborasi', 'bersama', 'sharing', 'berbagi'],
            'kesulitan' => ['susah', 'sulit', 'bingung', 'tidak paham', 'rumit'],
            'lanjut' => ['selanjutnya', 'lanjut', 'berikutnya', 'next', 'tahap'],
            'greeting' => ['halo', 'hi', 'hai', 'selamat', 'permisi']
        ];

        $detectedKeyword = null;
        foreach ($keywords as $category => $words) {
            foreach ($words as $word) {
                if (strpos($message, $word) !== false) {
                    $detectedKeyword = $category;
                    break 2;
                }
            }
        }

        // Generate respons berdasarkan kata kunci dan komponen
        switch ($detectedKeyword) {
            case 'greeting':
                return "Halo! Selamat datang di sesi pembelajaran {$materiName}. Saya AI Tutor yang akan membantu Anda di tahap {$componentName}. " . 
                       $this->getComponentSpecificPrompt($component, $materiName, $mataPelajaran);

            case 'konsep':
                if ($component === 'eksplorasi_konsep') {
                    return "Di tahap {$componentName}, mari kita dalami konsep {$materiName}. Konsep ini penting dalam {$mataPelajaran} karena menjadi fondasi pemahaman. " .
                           "Coba mulai dengan memikirkan apa yang sudah Anda ketahui, lalu kita eksplorasi lebih mendalam. Bagian mana yang ingin Anda pahami terlebih dahulu?";
                } else {
                    return "Pertanyaan konsep sangat bagus! Dalam tahap {$componentName}, kita perlu mengaitkan konsep {$materiName} dengan " .
                           ($component === 'mulai_dari_diri' ? 'pengalaman pribadi Anda' : 'tujuan pembelajaran tahap ini') . 
                           ". Apakah Anda sudah familiar dengan konsep dasarnya dari tahap Eksplorasi Konsep?";
                }

            case 'contoh':
                $responses = [
                    'demonstrasi_konseptual' => "Sangat tepat! Di tahap {$componentName}, contoh dan penerapan adalah kunci. Untuk {$materiName}, coba pikirkan situasi nyata di sekitar Anda. Bagaimana konsep ini bisa Anda terapkan? Mari kita diskusikan contoh yang Anda pikirkan.",
                    'elaborasi_pemahaman' => "Penerapan adalah fokus utama {$componentName}! Konsep {$materiName} punya banyak aplikasi dalam {$mataPelajaran}. Coba hubungkan dengan topik lain yang pernah dipelajari. Di mana lagi Anda bisa menggunakan pemahaman ini?",
                    'default' => "Contoh memang membantu pemahaman! Dalam konteks {$componentName}, mari kita lihat bagaimana {$materiName} berkaitan dengan tahap pembelajaran ini. Contoh apa yang paling relevan dengan situasi Anda saat ini?"
                ];
                return $responses[$component] ?? $responses['default'];

            case 'refleksi':
                if (in_array($component, ['mulai_dari_diri', 'refleksi_terbimbing'])) {
                    return "Refleksi adalah inti dari {$componentName}! Mari renungkan: Bagaimana {$materiName} ini berkaitan dengan pengalaman Anda? Apa yang Anda rasakan saat mempelajarinya? " .
                           "Refleksi yang jujur akan memperdalam pemahaman dan membantu Anda mengaitkan pembelajaran dengan kehidupan nyata.";
                } else {
                    return "Refleksi memang penting dalam pembelajaran! Meski sekarang di tahap {$componentName}, unsur reflektif tetap berharga. " .
                           "Untuk {$materiName}, bagaimana perasaan Anda sejauh ini? Ada insight menarik yang muncul?";
                }

            case 'diskusi':
                if ($component === 'ruang_kolaborasi') {
                    return "Tepat sekali! {$componentName} adalah momentum berbagi dan berkolaborasi. Untuk {$materiName}, pikirkan: perspektif apa yang bisa diperkaya melalui diskusi? " .
                           "Bagaimana pengalaman atau pemahaman teman-teman bisa melengkapi insight Anda? Mari manfaatkan keberagaman pandangan!";
                } else {
                    return "Kolaborasi sangat berharga! Meski di tahap {$componentName}, Anda tetap bisa mendiskusikan {$materiName} dengan rekan belajar. " .
                           "Sharing pemahaman sering membuka perspektif baru yang tidak terpikirkan sebelumnya.";
                }

            case 'kesulitan':
                return "Wajar jika {$materiName} terasa menantang di tahap {$componentName}. Ini bagian normal dari proses pembelajaran! " .
                       "Mari kita breakdown: bagian spesifik mana yang paling membingungkan? Kita bisa mulai dari yang paling dasar dan bertahap membangun pemahaman. " .
                       "Ingat, setiap tahap punya tujuan berbeda, jadi fokus dulu pada apa yang relevan untuk {$componentName}.";

            case 'lanjut':
                return "Setelah menguasai {$componentName} untuk {$materiName}, Anda akan siap melangkah ke tahap berikutnya dalam model 6 komponen. " .
                       "Namun, pastikan dulu fondasi di tahap ini sudah kuat. Apakah ada aspek yang masih perlu diperdalam atau diperjelas sebelum melanjut?";

            default:
                // Respons berdasarkan komponen pembelajaran
                return $this->getComponentSpecificResponse($component, $materiName, $modulName, $mataPelajaran, $message);
        }
    }

    protected function getComponentSpecificPrompt(string $component, string $materiName, string $mataPelajaran): string
    {
        $prompts = [
            'mulai_dari_diri' => "Apa yang sudah Anda ketahui tentang {$materiName}? Bagaimana topik ini berkaitan dengan pengalaman Anda?",
            'eksplorasi_konsep' => "Mari kita eksplorasi konsep-konsep kunci dalam {$materiName}. Bagian mana yang ingin dipahami lebih dalam?",
            'ruang_kolaborasi' => "Saatnya berbagi perspektif tentang {$materiName}. Apa insight yang bisa didiskusikan dengan rekan belajar?",
            'refleksi_terbimbing' => "Mari berefleksi mendalam tentang pembelajaran {$materiName}. Apa yang paling berkesan atau mengubah cara pandang Anda?",
            'demonstrasi_konseptual' => "Waktunya menunjukkan pemahaman {$materiName}. Bagaimana Anda akan menjelaskan konsep ini kepada orang lain?",
            'elaborasi_pemahaman' => "Mari kembangkan pemahaman {$materiName} ke konteks yang lebih luas. Bagaimana konsep ini bisa diterapkan di berbagai situasi?"
        ];

        return $prompts[$component] ?? "Mari kita mulai eksplorasi {$materiName} dalam konteks {$mataPelajaran}.";
    }

    protected function getComponentSpecificResponse(string $component, string $materiName, string $modulName, string $mataPelajaran, string $message): string
    {
        $componentNames = [
            'mulai_dari_diri' => 'Mulai Dari Diri',
            'eksplorasi_konsep' => 'Eksplorasi Konsep',
            'ruang_kolaborasi' => 'Ruang Kolaborasi',
            'refleksi_terbimbing' => 'Refleksi Terbimbing', 
            'demonstrasi_konseptual' => 'Demonstrasi Konseptual',
            'elaborasi_pemahaman' => 'Elaborasi Pemahaman'
        ];

        $componentName = $componentNames[$component] ?? $component;

        $responses = [
            'mulai_dari_diri' => "Dalam {$componentName}, mari mulai dari diri Anda sendiri. Apa pengalaman atau pengetahuan awal yang Anda miliki tentang {$materiName}? Bagaimana topik ini bersinggungan dengan kehidupan sehari-hari Anda? Refleksi personal ini akan menjadi fondasi untuk pembelajaran yang lebih mendalam.",

            'eksplorasi_konsep' => "Di tahap {$componentName}, kita akan mendalami konsep inti {$materiName} dalam konteks {$mataPelajaran}. Mari kita eksplorasi definisi, prinsip-prinsip utama, dan bagaimana konsep ini bekerja. Apa aspek spesifik yang ingin Anda pahami lebih detail?",

            'ruang_kolaborasi' => "Sekarang saatnya {$componentName}! Bagaimana Anda bisa berbagi pemahaman tentang {$materiName} dengan rekan belajar? Diskusi dan kolaborasi sering membuka perspektif baru yang memperkaya pembelajaran. Apa yang ingin Anda sharing atau tanyakan dalam diskusi kelompok?",

            'refleksi_terbimbing' => "Dalam {$componentName}, mari kita berefleksi lebih mendalam tentang pembelajaran {$materiName}. Bagaimana pemahaman Anda berkembang? Apa insight baru yang didapat? Refleksi ini membantu mengkonsolidasikan pembelajaran dan mempersiapkan aplikasi praktis.",

            'demonstrasi_konseptual' => "Di tahap {$componentName}, saatnya menunjukkan pemahaman Anda tentang {$materiName}. Bagaimana Anda akan mendemonstrasikan atau menjelaskan konsep ini? Praktik menjelaskan kembali adalah cara efektif untuk memastikan pemahaman yang mendalam.",

            'elaborasi_pemahaman' => "Dalam {$componentName}, mari kembangkan pemahaman {$materiName} ke level yang lebih luas. Bagaimana konsep ini bisa diterapkan dalam konteks berbeda? Apa kaitannya dengan topik lain dalam {$mataPelajaran}? Mari kita eksplorasi koneksi dan aplikasi yang lebih luas."
        ];

        return $responses[$component] ?? "Terima kasih atas pertanyaan tentang {$materiName}. Dalam konteks {$componentName} dari modul {$modulName}, mari kita bahas lebih spesifik. Apa aspek yang paling ingin Anda dalami?";
    }
}