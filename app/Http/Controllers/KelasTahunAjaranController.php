<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelasTahunAjaranController extends Controller
{
    /**
     * Display kelas management per tahun ajaran
     */
    public function index(Request $request)
    {
        // Get all tahun ajaran for filter
        $tahunAjaran = DB::table('tahun_pelajaran')
            ->orderBy('id_ta', 'desc')
            ->get();
        
        // Get active tahun ajaran or selected one
        $selectedTA = $request->ta_filter;
        
        if (!$selectedTA) {
            // Default to active tahun ajaran
            $activeTA = DB::table('tahun_pelajaran')
                ->where('aktif', 'Y')
                ->first();
            $selectedTA = $activeTA ? $activeTA->id_ta : null;
        }
        
        // Get selected tahun ajaran details
        $currentTA = null;
        if ($selectedTA) {
            $currentTA = DB::table('tahun_pelajaran')
                ->where('id_ta', $selectedTA)
                ->first();
        }
        
        if ($request->ajax()) {
            if (!$selectedTA) {
                return response()->json([
                    'draw' => intval($request->draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Get kelas for selected tahun ajaran
            $query = DB::table('kelas_ta')
                ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
                ->where('kelas_ta.id_ta', $selectedTA)
                ->select('kelas_ta.*', 'kelas.nama_kelas', 'kelas.deskripsi', 'kelas.aktif');
            
            // Search
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('kelas.nama_kelas', 'like', "%{$search}%")
                      ->orWhere('kelas.deskripsi', 'like', "%{$search}%");
                });
            }
            
            // Count total before pagination
            $totalFiltered = $query->count();
            $totalData = DB::table('kelas_ta')->where('id_ta', $selectedTA)->count();
            
            // Ordering
            if ($request->has('order')) {
                $columns = ['kelas_ta.id_kelas_ta', 'kelas.nama_kelas', 'kelas.deskripsi'];
                $columnIndex = $request->order[0]['column'];
                
                if ($columnIndex > 0 && $columnIndex < count($columns)) {
                    $columnName = $columns[$columnIndex];
                    $direction = $request->order[0]['dir'];
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('kelas.nama_kelas', 'asc');
            }
            
            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            $kelasTA = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($kelasTA as $index => $kta) {
                // Count mata pelajaran for this kelas_ta
                $mapelCount = DB::table('kelas_mp')
                    ->where('id_kelas_ta', $kta->id_kelas_ta)
                    ->count();
                
                // Count students (if you have assignment table)
                $siswaCount = 0; // Implement based on your structure
                
                // Action buttons
                $btn = '<button onclick="manageMapel('.$kta->id_kelas_ta.')" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm mr-1" title="Kelola Mata Pelajaran">
                            <i class="fas fa-book"></i>
                        </button>';
                
                if ($mapelCount == 0) {
                    $btn .= '<button onclick="removeFromTA('.$kta->id_kelas_ta.')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm" title="Hapus dari Tahun Ajaran">
                                <i class="fas fa-trash"></i>
                            </button>';
                }
                
                // Status badge
                $status = $kta->aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>';
                
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nama_kelas' => $kta->nama_kelas,
                    'deskripsi' => $kta->deskripsi ?? '-',
                    'mapel_count' => '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">'.$mapelCount.' mapel</span>',
                    'siswa_count' => '<span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">'.$siswaCount.' siswa</span>',
                    'status' => $status,
                    'action' => $btn
                ];
            }
            
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalData,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);
        }
        
        // Get available kelas to add (not yet in selected TA)
        $availableKelas = [];
        if ($selectedTA) {
            $kelasInTA = DB::table('kelas_ta')
                ->where('id_ta', $selectedTA)
                ->pluck('id_kelas')
                ->toArray();
            
            $availableKelas = DB::table('kelas')
                ->where('aktif', 'Y')
                ->whereNotIn('id_kelas', $kelasInTA)
                ->orderBy('nama_kelas')
                ->get();
        }
        
        return view('enroll.kelas_ta', compact('tahunAjaran', 'currentTA', 'selectedTA', 'availableKelas'));
    }

    /**
     * Add kelas to tahun ajaran
     */
    public function store(Request $request)
    {
        Log::info('Store request received:', $request->all());

        // Validate request
        $validated = $request->validate([
            'id_kelas' => 'required',
            'id_ta' => 'required|exists:tahun_pelajaran,id_ta'
        ]);

        try {
            // Handle array or single value
            $kelasIds = $request->id_kelas;
            if (!is_array($kelasIds)) {
                $kelasIds = [$kelasIds];
            }

            // Validate each kelas exists
            foreach ($kelasIds as $kelasId) {
                $kelasExists = DB::table('kelas')->where('id_kelas', $kelasId)->exists();
                if (!$kelasExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kelas dengan ID ' . $kelasId . ' tidak ditemukan'
                    ], 400);
                }
            }

            $insertData = [];
            $skipped = 0;
            
            foreach ($kelasIds as $kelasId) {
                // Check if already exists
                $exists = DB::table('kelas_ta')
                    ->where('id_kelas', $kelasId)
                    ->where('id_ta', $request->id_ta)
                    ->exists();
                
                if (!$exists) {
                    $insertData[] = [
                        'id_kelas' => $kelasId,
                        'id_ta' => $request->id_ta,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                } else {
                    $skipped++;
                }
            }
            
            $addedCount = 0;
            if (!empty($insertData)) {
                DB::table('kelas_ta')->insert($insertData);
                $addedCount = count($insertData);
            }
            
            $message = $addedCount . ' kelas berhasil ditambahkan';
            if ($skipped > 0) {
                $message .= ', ' . $skipped . ' kelas dilewati (sudah ada)';
            }

            Log::info('Store successful:', ['added' => $addedCount, 'skipped' => $skipped]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Store failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kelas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove kelas from tahun ajaran
     */
    public function destroy($id)
    {
        try {
            // Check if kelas_ta exists
            $kelasTA = DB::table('kelas_ta')->where('id_kelas_ta', $id)->first();
            if (!$kelasTA) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Check if has mata pelajaran
            $hasMapel = DB::table('kelas_mp')
                ->where('id_kelas_ta', $id)
                ->exists();
            
            if ($hasMapel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas memiliki mata pelajaran, hapus mata pelajaran terlebih dahulu'
                ], 400);
            }
            
            DB::table('kelas_ta')->where('id_kelas_ta', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil dihapus dari tahun ajaran'
            ]);

        } catch (\Exception $e) {
            Log::error('Destroy failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelas dari tahun ajaran'
            ], 500);
        }
    }

    /**
     * Copy kelas from previous tahun ajaran
     */
    public function copyFromPreviousTA(Request $request)
    {
        $request->validate([
            'from_ta' => 'required|exists:tahun_pelajaran,id_ta',
            'to_ta' => 'required|exists:tahun_pelajaran,id_ta'
        ]);

        try {
            // Get kelas from source TA
            $sourceKelas = DB::table('kelas_ta')
                ->where('id_ta', $request->from_ta)
                ->get();
            
            if ($sourceKelas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada kelas di tahun ajaran sumber'
                ], 400);
            }
            
            $insertData = [];
            $skipped = 0;
            
            foreach ($sourceKelas as $kelas) {
                // Check if already exists in destination
                $exists = DB::table('kelas_ta')
                    ->where('id_kelas', $kelas->id_kelas)
                    ->where('id_ta', $request->to_ta)
                    ->exists();
                
                if (!$exists) {
                    $insertData[] = [
                        'id_kelas' => $kelas->id_kelas,
                        'id_ta' => $request->to_ta,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                } else {
                    $skipped++;
                }
            }
            
            $addedCount = 0;
            if (!empty($insertData)) {
                DB::table('kelas_ta')->insert($insertData);
                $addedCount = count($insertData);
            }
            
            $message = $addedCount . ' kelas berhasil disalin';
            if ($skipped > 0) {
                $message .= ', ' . $skipped . ' kelas dilewati (sudah ada)';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Copy failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyalin kelas: ' . $e->getMessage()
            ], 500);
        }
    }
}