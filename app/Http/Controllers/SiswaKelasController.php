<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiswaKelasController extends Controller
{
    /**
     * Display siswa enrollment per kelas
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
            
            // Create table if not exists
            $this->createSiswaKelasTable();
            
            // Get siswa for selected kelas
            $query = DB::table('siswa_kelas')
                ->join('users', 'siswa_kelas.id_user', '=', 'users.id_user')
                ->join('profile', 'users.id_user', '=', 'profile.id_user')
                ->where('siswa_kelas.id_kelas_ta', $selectedKelas)
                ->select(
                    'siswa_kelas.*',
                    'profile.nama as siswa_nama',
                    'profile.id_status as nisn',
                    'profile.jenis_kelamin',
                    'users.aktif as user_aktif'
                );
            
            // Search
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('profile.nama', 'like', "%{$search}%")
                      ->orWhere('profile.id_status', 'like', "%{$search}%");
                });
            }
            
            // Count total before pagination
            $totalFiltered = $query->count();
            $totalData = DB::table('siswa_kelas')->where('id_kelas_ta', $selectedKelas)->count();
            
            // Ordering
            if ($request->has('order')) {
                $columns = ['siswa_kelas.id', 'profile.nama', 'profile.id_status', 'profile.jenis_kelamin'];
                $columnIndex = $request->order[0]['column'];
                
                if ($columnIndex > 0 && $columnIndex < count($columns)) {
                    $columnName = $columns[$columnIndex];
                    $direction = $request->order[0]['dir'];
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('profile.nama', 'asc');
            }
            
            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            $siswaKelas = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($siswaKelas as $index => $sk) {
                // Action buttons
                $btn = '<button onclick="editSiswa('.$sk->id.')" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm mr-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                
                $btn .= '<button onclick="removeSiswa('.$sk->id.')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>';
                
                // Status badge
                $status = $sk->aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>';
                
                $userStatus = $sk->user_aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Non-Aktif</span>';
                
                $jk = $sk->jenis_kelamin === 'L' ? 'Laki-laki' : ($sk->jenis_kelamin === 'P' ? 'Perempuan' : '-');
                
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'siswa_nama' => $sk->siswa_nama . '<br><small class="text-gray-500">' . $userStatus . '</small>',
                    'nisn' => $sk->nisn ?? '-',
                    'jenis_kelamin' => $jk,
                    'status' => $status,
                    'tanggal_daftar' => date('d/m/Y', strtotime($sk->created_at)),
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
        
        // Get available siswa for enrollment
        $availableSiswa = [];
        if ($selectedKelas) {
            // Create table if not exists
            $this->createSiswaKelasTable();
            
            $siswaInKelas = DB::table('siswa_kelas')
                ->where('id_kelas_ta', $selectedKelas)
                ->pluck('id_user')
                ->toArray();
            
            $availableSiswa = DB::table('users')
                ->join('profile', 'users.id_user', '=', 'profile.id_user')
                ->where('users.aktif', 'Y')
                ->where('profile.status', 'siswa')
                ->whereNotIn('users.id_user', $siswaInKelas)
                ->select('users.id_user', 'profile.nama', 'profile.id_status as nisn')
                ->orderBy('profile.nama')
                ->get();
        }
        
        return view('enroll.siswa_kelas', compact(
            'tahunAjaran', 
            'currentTA', 
            'selectedTA', 
            'kelasList',
            'currentKelas',
            'selectedKelas',
            'availableSiswa'
        ));
    }

    /**
     * Create siswa_kelas table if not exists
     */
    private function createSiswaKelasTable()
    {
        if (!DB::getSchemaBuilder()->hasTable('siswa_kelas')) {
            DB::statement('
                CREATE TABLE siswa_kelas (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    id_kelas_ta BIGINT UNSIGNED NOT NULL,
                    id_user BIGINT UNSIGNED NOT NULL,
                    aktif ENUM("Y", "N") NOT NULL DEFAULT "Y",
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (id_kelas_ta) REFERENCES kelas_ta(id_kelas_ta) ON DELETE CASCADE,
                    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
                    UNIQUE KEY unique_siswa_kelas (id_kelas_ta, id_user)
                )
            ');
        }
    }

    /**
     * Store siswa enrollment
     */
    public function store(Request $request)
    {
        Log::info('Store siswa enrollment:', $request->all());

        // Create table if not exists
        $this->createSiswaKelasTable();

        try {
            // Handle array or single value
            $siswaIds = $request->id_user;
            if (!is_array($siswaIds)) {
                $siswaIds = [$siswaIds];
            }

            // Validate each siswa exists and is a student
            foreach ($siswaIds as $siswaId) {
                $siswaExists = DB::table('users')
                    ->join('profile', 'users.id_user', '=', 'profile.id_user')
                    ->where('users.id_user', $siswaId)
                    ->where('profile.status', 'siswa')
                    ->exists();
                    
                if (!$siswaExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Siswa dengan ID ' . $siswaId . ' tidak ditemukan'
                    ], 400);
                }
            }

            $insertData = [];
            $skipped = 0;
            
            foreach ($siswaIds as $siswaId) {
                // Check if already exists
                $exists = DB::table('siswa_kelas')
                    ->where('id_kelas_ta', $request->id_kelas_ta)
                    ->where('id_user', $siswaId)
                    ->exists();
                
                if (!$exists) {
                    $insertData[] = [
                        'id_kelas_ta' => $request->id_kelas_ta,
                        'id_user' => $siswaId,
                        'aktif' => 'Y',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                } else {
                    $skipped++;
                }
            }
            
            $addedCount = 0;
            if (!empty($insertData)) {
                DB::table('siswa_kelas')->insert($insertData);
                $addedCount = count($insertData);
            }
            
            $message = $addedCount . ' siswa berhasil ditambahkan';
            if ($skipped > 0) {
                $message .= ', ' . $skipped . ' siswa dilewati (sudah ada)';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Store siswa failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update siswa enrollment
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            $siswaKelas = DB::table('siswa_kelas')->where('id', $id)->first();
            
            if (!$siswaKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::table('siswa_kelas')
                ->where('id', $id)
                ->update([
                    'aktif' => $request->aktif,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status siswa berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            Log::error('Update siswa failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status siswa'
            ], 500);
        }
    }

    /**
     * Remove siswa from kelas
     */
    public function destroy($id)
    {
        try {
            $siswaKelas = DB::table('siswa_kelas')->where('id', $id)->first();
            
            if (!$siswaKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::table('siswa_kelas')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil dihapus dari kelas'
            ]);

        } catch (\Exception $e) {
            Log::error('Destroy siswa failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus siswa dari kelas'
            ], 500);
        }
    }

    /**
     * Get siswa details for edit
     */
    public function show($id)
    {
        try {
            $siswaKelas = DB::table('siswa_kelas')
                ->join('users', 'siswa_kelas.id_user', '=', 'users.id_user')
                ->join('profile', 'users.id_user', '=', 'profile.id_user')
                ->where('siswa_kelas.id', $id)
                ->select(
                    'siswa_kelas.*',
                    'profile.nama as siswa_nama',
                    'profile.id_status as nisn'
                )
                ->first();

            if (!$siswaKelas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $siswaKelas
            ]);

        } catch (\Exception $e) {
            Log::error('Show siswa failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data siswa'
            ], 500);
        }
    }

    /**
     * Copy siswa from another kelas
     */
    public function copyFromKelas(Request $request)
    {
        $request->validate([
            'from_kelas_ta' => 'required|exists:kelas_ta,id_kelas_ta',
            'to_kelas_ta' => 'required|exists:kelas_ta,id_kelas_ta'
        ]);

        try {
            // Create table if not exists
            $this->createSiswaKelasTable();

            // Get siswa from source kelas
            $sourceSiswa = DB::table('siswa_kelas')
                ->where('id_kelas_ta', $request->from_kelas_ta)
                ->where('aktif', 'Y')
                ->get();
            
            if ($sourceSiswa->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada siswa aktif di kelas sumber'
                ], 400);
            }
            
            $insertData = [];
            $skipped = 0;
            
            foreach ($sourceSiswa as $siswa) {
                // Check if already exists in destination
                $exists = DB::table('siswa_kelas')
                    ->where('id_kelas_ta', $request->to_kelas_ta)
                    ->where('id_user', $siswa->id_user)
                    ->exists();
                
                if (!$exists) {
                    $insertData[] = [
                        'id_kelas_ta' => $request->to_kelas_ta,
                        'id_user' => $siswa->id_user,
                        'aktif' => 'Y',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                } else {
                    $skipped++;
                }
            }
            
            $addedCount = 0;
            if (!empty($insertData)) {
                DB::table('siswa_kelas')->insert($insertData);
                $addedCount = count($insertData);
            }
            
            $message = $addedCount . ' siswa berhasil disalin';
            if ($skipped > 0) {
                $message .= ', ' . $skipped . ' siswa dilewati (sudah ada)';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Copy siswa failed:', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyalin siswa: ' . $e->getMessage()
            ], 500);
        }
    }
}