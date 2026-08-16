<?php

// Fixed Controller - app/Http/Controllers/AIChatController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AITutorService;
use App\Models\Materi;
use App\Models\Modul;
use App\Models\AiChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIChatController extends Controller
{
    protected $aiTutorService;

    public function __construct(AITutorService $aiTutorService)
    {
        $this->aiTutorService = $aiTutorService;
        $this->middleware('auth');
    }

    public function sendMessage(Request $request, $modulId, $materiId): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
                'context' => 'required|string'
            ]);

            // Get modul and materi info for context - with error handling
            $modul = Modul::find($modulId);
            $materi = Materi::find($materiId);

            if (!$modul) {
                return response()->json([
                    'success' => false,
                    'message' => 'Modul tidak ditemukan'
                ], 404);
            }

            if (!$materi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materi tidak ditemukan'
                ], 404);
            }

            // Verify that materi belongs to modul
            if ($materi->id_modul != $modulId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Materi tidak sesuai dengan modul'
                ], 400);
            }

            // Load relationships if they exist
            try {
                $modul->load('mataPelajaran');
            } catch (\Exception $e) {
                // If relationship doesn't exist, continue without it
                Log::info('MataPelajaran relationship not loaded: ' . $e->getMessage());
            }

            // Prepare context for AI
            $context = [
                'mata_pelajaran' => optional($modul->mataPelajaran)->nama_mata_pelajaran ?? 'Pembelajaran',
                'modul_name' => $modul->nama_modul ?? 'Modul',
                'modul_description' => $modul->desk ?? '',
                'materi_name' => $materi->nama_materi ?? 'Materi',
                'learning_component' => $request->context,
                'user_role' => auth()->user()->role ?? 'student',
                'available_components' => $this->getAvailableComponents($materi)
            ];

            // Get AI response
            $aiResponse = $this->aiTutorService->generateResponse(
                $request->message,
                $context
            );

            // Save to database if table exists
            $this->saveChatMessage(
                auth()->id(),
                $modulId,
                $materiId,
                $request->message,
                $aiResponse,
                $request->context
            );

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('AI Chat Send Message Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'modul_id' => $modulId,
                'materi_id' => $materiId,
                'message' => $request->message ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan saat memproses pertanyaan Anda.',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function getChatHistory(Request $request, $modulId, $materiId): JsonResponse
    {
        try {
            $userId = auth()->id();
            $context = $request->query('context', 'eksplorasi_konsep');
            
            // Check if AiChatMessage model and table exist
            if (!class_exists('App\\Models\\AiChatMessage')) {
                return response()->json([
                    'success' => true,
                    'messages' => []
                ]);
            }

            try {
                // Get chat history from database
                $messages = AiChatMessage::where('id_modul', $modulId)
                    ->where('id_materi', $materiId)
                    ->where('id_user', $userId)
                    ->where('context', $context)
                    ->orderBy('created_at', 'asc')
                    ->limit(50)
                    ->get()
                    ->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'user_message' => $message->user_message,
                            'ai_response' => $message->ai_response,
                            'timestamp' => $message->created_at->toISOString(),
                            'context' => $message->context
                        ];
                    });

                return response()->json([
                    'success' => true,
                    'messages' => $messages
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                // If table doesn't exist, return empty messages
                Log::info('AI Chat table not found, returning empty messages: ' . $e->getMessage());
                
                return response()->json([
                    'success' => true,
                    'messages' => []
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Get Chat History Error: ' . $e->getMessage(), [
                'id_modul' => $modulId,
                'id_materi' => $materiId,
                'id_user' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => true, // Return success with empty messages as fallback
                'messages' => []
            ]);
        }
    }

    public function clearChatHistory(Request $request, $modulId, $materiId): JsonResponse
    {
        try {
            $userId = auth()->id();
            $context = $request->input('context', 'eksplorasi_konsep');
            
            if (!class_exists('App\\Models\\AiChatMessage')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada riwayat chat untuk dihapus'
                ]);
            }

            try {
                $deleted = AiChatMessage::where('id_modul', $modulId)
                    ->where('id_materi', $materiId)
                    ->where('id_user', $userId)
                    ->where('context', $context)
                    ->delete();

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$deleted} pesan chat"
                ]);

            } catch (\Illuminate\Database\QueryException $e) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada riwayat chat untuk dihapus'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Clear Chat History Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus riwayat chat'
            ], 500);
        }
    }

    // protected function saveChatMessage($userId, $modulId, $materiId, $userMessage, $aiResponse, $context)
    // {
    //     try {
    //         // Check if model and table exist before saving
    //         if (!class_exists('App\\Models\\AiChatMessage')) {
    //             return;
    //         }

    //         AiChatMessage::create([
    //             'user_id' => $userId,
    //             'modul_id' => $modulId,
    //             'materi_id' => $materiId,
    //             'user_message' => $userMessage,
    //             'ai_response' => $aiResponse,
    //             'context' => $context,
    //             'metadata' => [
    //                 'user_agent' => request()->userAgent(),
    //                 'ip_address' => request()->ip(),
    //                 'timestamp' => now()->toISOString()
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         // Log error but don't fail the main request
    //         Log::info('Failed to save chat message (continuing without saving): ' . $e->getMessage());
    //     }
    // }

    protected function saveChatMessage($userId, $modulId, $materiId, $userMessage, $aiResponse, $context)
    {
        try {
            Log::info('Attempting to save chat message', [
                'id_user' => $userId,
                'id_modul' => $modulId,
                'id_materi' => $materiId,
                'context' => $context,
                'message_length' => strlen($userMessage),
                'response_length' => strlen($aiResponse)
            ]);

            if (!class_exists('App\\Models\\AiChatMessage')) {
                Log::error('AiChatMessage model not found');
                return;
            }

            $saved = AiChatMessage::create([
                'id_user' => $userId,
                'id_modul' => $modulId,
                'id_materi' => $materiId,
                'user_message' => $userMessage,
                'ai_response' => $aiResponse,
                'context' => $context,
                'metadata' => [
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                    'timestamp' => now()->toISOString()
                ]
            ]);

            Log::info('Chat message saved successfully', ['id' => $saved->id]);

        } catch (\Exception $e) {
            Log::error('Failed to save chat message: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
    }
}

    protected function getAvailableComponents($materi)
    {
        try {
            $components = [
                'mulai_dari_diri' => $materi->mulai_dari_diri,
                'eksplorasi_konsep' => $materi->eksplorasi_konsep,
                'ruang_kolaborasi' => $materi->ruang_kolaborasi,
                'refleksi_terbimbing' => $materi->refleksi_terbimbing,
                'demonstrasi_konseptual' => $materi->demonstrasi_konseptual,
                'elaborasi_pemahaman' => $materi->elaborasi_pemahaman,
            ];

            return array_filter($components, function($value) {
                return !empty($value);
            });
        } catch (\Exception $e) {
            return [];
        }
    }
}