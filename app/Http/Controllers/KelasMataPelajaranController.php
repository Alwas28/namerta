<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelasMataPelajaranController extends Controller
{
    /**
     * Display mata pelajaran enrollment per kelas
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
        
        // Get kelas for selected TA
        $kelasList = [];
        if ($selectedTA) {
            $kelasList = DB::table('kelas_ta')
                ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
                ->where('kelas_ta.id_ta', $selectedTA)
                ->select('kelas_ta.id_kelas_ta', 'kelas.nama_kelas')
                ->orderBy('kelas.nama_kelas')
                ->get();
        }
        
        // Get selected kelas
        $selectedKelas = $request->kelas_filter;
        $currentKelas = null;
        if ($selectedKelas) {
            $currentKelas = DB::table('kelas_ta')
                ->join('kelas', 'kelas_ta.id_kelas', '=', 'kelas.id_kelas')
                ->where('kelas_ta.id_kelas_ta', $selectedKelas)
                ->select('kelas_ta.*', 'kelas.nama_kelas', 'kelas.deskripsi')
                ->first();
        }
        
        if ($request->ajax()) {
            if (!$selectedKelas) {
                return response()->json([
                    'draw' => intval($request->draw),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }
            
            // Get mata pelajaran for selected kelas
            $query = DB::table('kelas_mp')
                ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
                ->join('users', 'kelas_mp.id_user', '=', 'users.id_user')
                ->join('profile', 'users.id_user', '=', 'profile.id_user')
                ->where('kelas_mp.id_kelas_ta', $selectedKelas)
                ->select(
                    'kelas_mp.*',
                    'mata_pelajaran.nama_mata_pelajaran',
                    'mata_pelajaran.deskripsi as mapel_deskripsi',
                    'mata_pelajaran.aktif as mapel_aktif',
                    'profile.nama as guru_nama'
                );
            
            // Search
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('mata_pelajaran.nama_mata_pelajaran', 'like', "%{$search}%")
                      ->orWhere('profile.nama', 'like', "%{$search}%");
                });
            }
            
            // Count total before pagination
            $totalFiltered = $query->count();
            $totalData = DB::table('kelas_mp')->where('id_kelas_ta', $selectedKelas)->count();
            
            // Ordering
            if ($request->has('order')) {
                $columns = ['kelas_mp.id_kelas_mp', 'mata_pelajaran.nama_mata_pelajaran', 'profile.nama'];
                $columnIndex = $request->order[0]['column'];
                
                if ($columnIndex > 0 && $columnIndex < count($columns)) {
                    $columnName = $columns[$columnIndex];
                    $direction = $request->order[0]['dir'];
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('mata_pelajaran.nama_mata_pelajaran', 'asc');
            }
            
            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            $kelasMP = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($kelasMP as $index => $kmp) {
                // Count modul for this mata pelajaran
                $modulCount = DB::table('modul')
                    ->where('id_mata_pelajaran', $kmp->id_mata_pelajaran)
                    ->count();
                
                // Action buttons
                $btn = '<button onclick="editMapel('.$kmp->id_kelas_mp.')" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm mr-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                
                $btn .= '<button onclick="removeMapel('.$kmp->id_kelas_mp.')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>';
                
                // Status badge
                $status = $kmp->aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>';
                
                $mapelStatus = $kmp->mapel_aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Non-Aktif</span>';
                
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nama_mata_pelajaran' => $kmp->nama_mata_pelajaran . '<br><small class="text-gray-500">' . $mapelStatus . '</small>',
                    'guru_nama' => $kmp->guru_nama,
                    'modul_count' => '<span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">'.$modulCount.' modul</span>',
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
        
        // Get available mata pelajaran and guru for enrollment
        $availableMapel = DB::table('mata_pelajaran')
            ->where('aktif', 'Y')
            ->orderBy('nama_mata_pelajaran')
            ->get();
            
        $availableGuru = DB::table('users')
            ->join('profile', 'users.id_user', '=', 'profile.id_user')
            ->where('users.aktif', 'Y')
            ->where('profile.status', 'guru')
            ->select('users.id_user', 'profile.nama')
            ->orderBy('profile.nama')
            ->get();
        
        return view('enroll.kelas_mapel', compact(
            'tahunAjaran', 
            'currentTA', 
            'selectedTA', 
            'kelasList',
            'currentKelas',
            'selectedKelas',
            'availableMapel',
            'availableGuru'
        ));
    }

    /**
     * Store mata pelajaran enrollment
     */
    public function store(Request $request)
    {
        Log::info('Store mata pelajaran enrollment:', $request->all());

        $validated = $request->validate([
            'id_kelas_ta' => 'required|exists:kelas_ta,id_kelas_ta',
            'id_mata_pelajaran' => 'required|exists:mata_pelajaran,id_mata_pelajaran',
            'id_user' => 'required|exists:users,id_user'
        ]);

        try {
            // Check if already exists
            $exists = DB::table('kelas_mp')
                ->where('id_kelas_ta', $request->id_kelas_ta)
                ->where('id_mata_pelajaran', $request->id_mata_pelajaran)
                ->exists();
            
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata pelajaran sudah ada di kelas ini'
                ], 400);
            }

            // Insert new enrollment
            DB::table('kelas_mp')->insert([
                'id_kelas_ta' => $request->id_kelas_ta,
                'id_mata_pelajaran' => $request->id_mata_pelajaran,
                'id_user' => $request->id_user,
                'aktif' => 'Y',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil ditambahkan ke kelas'
            ]);

        } catch (\Exception $e) {
            Log::error('Store mata pelajaran failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan mata pelajaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update mata pelajaran enrollment
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_user' => 'required|exists:users,id_user',
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            $kelasMP = DB::table('kelas_mp')->where('id_kelas_mp', $id)->first();
            
            if (!$kelasMP) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::table('kelas_mp')
                ->where('id_kelas_mp', $id)
                ->update([
                    'id_user' => $request->id_user,
                    'aktif' => $request->aktif,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            Log::error('Update mata pelajaran failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data'
            ], 500);
        }
    }

    /**
     * Remove mata pelajaran from kelas
     */
    public function destroy($id)
    {
        try {
            $kelasMP = DB::table('kelas_mp')->where('id_kelas_mp', $id)->first();
            
            if (!$kelasMP) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Check if has related modul or other dependencies
            // Add checks here if needed

            DB::table('kelas_mp')->where('id_kelas_mp', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil dihapus dari kelas'
            ]);

        } catch (\Exception $e) {
            Log::error('Destroy mata pelajaran failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus mata pelajaran'
            ], 500);
        }
    }

    /**
     * Get mata pelajaran details for edit
     */
    public function show($id)
    {
        try {
            $kelasMP = DB::table('kelas_mp')
                ->join('mata_pelajaran', 'kelas_mp.id_mata_pelajaran', '=', 'mata_pelajaran.id_mata_pelajaran')
                ->join('users', 'kelas_mp.id_user', '=', 'users.id_user')
                ->join('profile', 'users.id_user', '=', 'profile.id_user')
                ->where('kelas_mp.id_kelas_mp', $id)
                ->select(
                    'kelas_mp.*',
                    'mata_pelajaran.nama_mata_pelajaran',
                    'profile.nama as guru_nama'
                )
                ->first();

            if (!$kelasMP) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $kelasMP
            ]);

        } catch (\Exception $e) {
            Log::error('Show mata pelajaran failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data'
            ], 500);
        }
    }
}