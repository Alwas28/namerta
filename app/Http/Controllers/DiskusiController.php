<?php

namespace App\Http\Controllers;

use App\Models\TopikDiskusi;
use App\Models\KomentarDiskusi;
use App\Models\LikeKomentar;
use App\Models\DiskusiReadStatus;
use App\Models\Materi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DiskusiController extends Controller
{
    /**
     * Get all diskusi topics for a material
     */
    public function index($courseId, $materialId)
    {
        try {
            $materi = Materi::findOrFail($materialId);
            $userId = Auth::id();
            
            $topikList = TopikDiskusi::where('id_materi', $materialId)
                ->with(['pembuat.profile', 'allKomentar'])
                ->withCount('allKomentar')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($topik) use ($userId) {
                    return [
                        'id_topik_diskusi' => $topik->id_topik_diskusi,
                        'judul' => $topik->judul,
                        'deskripsi' => $topik->deskripsi,
                        'status' => $topik->status,
                        'pembuat' => $topik->pembuat->profile->nama ?? 'Unknown',
                        'dibuat_pada' => $topik->created_at->diffForHumans(),
                        'total_komentar' => $topik->all_komentar_count,
                        'unread_count' => $topik->getUnreadCountForUser($userId),
                        'is_aktif' => $topik->isAktif()
                    ];
                });

            return response()->json([
                'success' => true,
                'topik' => $topikList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar diskusi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new diskusi topic (Guru only)
     */
    public function store(Request $request, $courseId, $materialId)
    {
        try {
            $request->validate([
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string'
            ]);

            $topik = TopikDiskusi::create([
                'id_materi' => $materialId,
                'id_user' => Auth::id(),
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'status' => 'aktif',
                'dibuka_pada' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Topik diskusi berhasil dibuat',
                'topik' => $topik
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat topik diskusi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get diskusi detail with comments
     */
    public function show($courseId, $materialId, $topikId)
    {
        try {
            $topik = TopikDiskusi::with([
                'pembuat.profile',
                'komentar' => function($query) {
                    $query->with([
                        'user.profile',
                        'replies' => function($q) {
                            $q->with(['user.profile', 'likes']);
                        },
                        'likes'
                    ]);
                }
            ])->findOrFail($topikId);

            $userId = Auth::id();

            // Update read status
            DiskusiReadStatus::updateOrCreate(
                [
                    'id_topik_diskusi' => $topikId,
                    'id_user' => $userId
                ],
                [
                    'last_read_at' => now()
                ]
            );

            // Format response
            $topikData = [
                'id_topik_diskusi' => $topik->id_topik_diskusi,
                'judul' => $topik->judul,
                'deskripsi' => $topik->deskripsi,
                'status' => $topik->status,
                'pembuat' => [
                    'nama' => $topik->pembuat->profile->nama ?? 'Unknown',
                    'is_guru' => $topik->pembuat->is_admin === 'Y'
                ],
                'dibuat_pada' => $topik->created_at->format('d M Y H:i'),
                'is_aktif' => $topik->isAktif(),
                'komentar' => $topik->komentar->map(function($komentar) use ($userId) {
                    return $this->formatKomentar($komentar, $userId);
                })
            ];

            return response()->json([
                'success' => true,
                'topik' => $topikData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail diskusi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add comment to diskusi
     */
    public function addComment(Request $request, $courseId, $materialId, $topikId)
    {
        try {
            $request->validate([
                'konten' => 'required|string',
                'id_parent' => 'nullable|exists:komentar_diskusi,id_komentar'
            ]);

            $topik = TopikDiskusi::findOrFail($topikId);

            if (!$topik->isAktif()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Diskusi sudah ditutup'
                ], 403);
            }

            $komentar = KomentarDiskusi::create([
                'id_topik_diskusi' => $topikId,
                'id_user' => Auth::id(),
                'id_parent' => $request->id_parent,
                'konten' => $request->konten
            ]);

            $komentar->load(['user.profile', 'likes']);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil ditambahkan',
                'komentar' => $this->formatKomentar($komentar, Auth::id())
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit comment
     */
    public function editComment(Request $request, $courseId, $materialId, $topikId, $komentarId)
    {
        try {
            $request->validate([
                'konten' => 'required|string'
            ]);

            $komentar = KomentarDiskusi::findOrFail($komentarId);

            // Check if user owns the comment
            if ($komentar->id_user !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengedit komentar ini'
                ], 403);
            }

            $komentar->update([
                'konten' => $request->konten,
                'diedit' => true,
                'edited_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengedit komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete comment
     */
    public function deleteComment($courseId, $materialId, $topikId, $komentarId)
    {
        try {
            $komentar = KomentarDiskusi::findOrFail($komentarId);

            // Check if user owns the comment or is admin
            if ($komentar->id_user !== Auth::id() && Auth::user()->is_admin !== 'Y') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus komentar ini'
                ], 403);
            }

            $komentar->delete();

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus komentar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like on comment
     */
    public function toggleLike($courseId, $materialId, $topikId, $komentarId)
    {
        try {
            $userId = Auth::id();
            
            $like = LikeKomentar::where('id_komentar', $komentarId)
                                ->where('id_user', $userId)
                                ->first();

            if ($like) {
                $like->delete();
                $liked = false;
            } else {
                LikeKomentar::create([
                    'id_komentar' => $komentarId,
                    'id_user' => $userId
                ]);
                $liked = true;
            }

            $komentar = KomentarDiskusi::findOrFail($komentarId);
            $likesCount = $komentar->getLikesCount();

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $likesCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses like: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close diskusi topic (Guru only)
     */
    public function closeTopic($courseId, $materialId, $topikId)
    {
        try {
            $topik = TopikDiskusi::findOrFail($topikId);

            // Check if user is the creator or admin
            if ($topik->id_user !== Auth::id() && Auth::user()->is_admin !== 'Y') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menutup diskusi ini'
                ], 403);
            }

            $topik->update([
                'status' => 'ditutup',
                'ditutup_pada' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Diskusi berhasil ditutup'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup diskusi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reopen diskusi topic (Guru only)
     */
    public function reopenTopic($courseId, $materialId, $topikId)
    {
        try {
            $topik = TopikDiskusi::findOrFail($topikId);

            // Check if user is the creator or admin
            if ($topik->id_user !== Auth::id() && Auth::user()->is_admin !== 'Y') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk membuka kembali diskusi ini'
                ], 403);
            }

            $topik->update([
                'status' => 'aktif',
                'ditutup_pada' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Diskusi berhasil dibuka kembali'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka kembali diskusi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to format komentar data
     */
    private function formatKomentar($komentar, $userId)
    {
        return [
            'id_komentar' => $komentar->id_komentar,
            'konten' => $komentar->konten,
            'user' => [
                'id' => $komentar->user->id_user,
                'nama' => $komentar->user->profile->nama ?? 'Unknown',
                'is_guru' => $komentar->user->is_admin === 'Y'
            ],
            'created_at' => $komentar->created_at->diffForHumans(),
            'created_at_full' => $komentar->created_at->format('d M Y H:i'),
            'diedit' => $komentar->diedit,
            'edited_at' => $komentar->edited_at ? $komentar->edited_at->format('d M Y H:i') : null,
            'likes_count' => $komentar->getLikesCount(),
            'is_liked' => $komentar->isLikedBy($userId),
            'can_edit' => $komentar->id_user === $userId,
            'can_delete' => $komentar->id_user === $userId || Auth::user()->is_admin === 'Y',
            'replies' => $komentar->replies->map(function($reply) use ($userId) {
                return $this->formatKomentar($reply, $userId);
            })
        ];
    }
}