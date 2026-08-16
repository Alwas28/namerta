<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    /**
     * Display list of tahun ajaran
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('tahun_pelajaran');
            
            // Search
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where('nama_ta', 'like', "%{$search}%");
            }
            
            // Ordering
            if ($request->has('order')) {
                $columns = ['id_ta', 'nama_ta', 'aktif'];
                $columnIndex = $request->order[0]['column'];
                
                if ($columnIndex > 0 && $columnIndex <= count($columns)) {
                    $columnName = $columns[$columnIndex - 1];
                    $direction = $request->order[0]['dir'];
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('id_ta', 'desc');
            }
            
            $totalData = DB::table('tahun_pelajaran')->count();
            $totalFiltered = $query->count();
            
            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            $tahunAjaran = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($tahunAjaran as $index => $ta) {
                // Action buttons
                $btn = '<button onclick="editTA('.$ta->id_ta.')" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm mr-1">
                            <i class="fas fa-edit"></i>
                        </button>';
                
                // Only allow delete if not active
                if ($ta->aktif !== 'Y') {
                    $btn .= '<button onclick="deleteTA('.$ta->id_ta.')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">
                                <i class="fas fa-trash"></i>
                            </button>';
                }
                
                // Status badge
                $status = $ta->aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>';
                
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nama_ta' => $ta->nama_ta,
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
        
        return view('admin.tahun_ajaran');
    }

    /**
     * Store new tahun ajaran
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_ta' => 'required|unique:tahun_pelajaran,nama_ta|max:100',
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            // If setting as active, deactivate others
            if ($request->aktif === 'Y') {
                DB::table('tahun_pelajaran')->update(['aktif' => 'N']);
            }
            
            DB::table('tahun_pelajaran')->insert([
                'nama_ta' => $request->nama_ta,
                'aktif' => $request->aktif,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan tahun ajaran'
            ], 500);
        }
    }

    /**
     * Get tahun ajaran for edit
     */
    public function edit($id)
    {
        $ta = DB::table('tahun_pelajaran')->where('id_ta', $id)->first();
        
        if (!$ta) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
        
        return response()->json($ta);
    }

    /**
     * Update tahun ajaran
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_ta' => 'required|max:100|unique:tahun_pelajaran,nama_ta,'.$id.',id_ta',
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            // If setting as active, deactivate others
            if ($request->aktif === 'Y') {
                DB::table('tahun_pelajaran')
                    ->where('id_ta', '!=', $id)
                    ->update(['aktif' => 'N']);
            }
            
            DB::table('tahun_pelajaran')
                ->where('id_ta', $id)
                ->update([
                    'nama_ta' => $request->nama_ta,
                    'aktif' => $request->aktif,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate tahun ajaran'
            ], 500);
        }
    }

    /**
     * Delete tahun ajaran
     */
    public function destroy($id)
    {
        try {
            // Check if it's active
            $ta = DB::table('tahun_pelajaran')->where('id_ta', $id)->first();
            
            if ($ta && $ta->aktif === 'Y') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun ajaran aktif tidak dapat dihapus'
                ], 400);
            }
            
            // Check if used in kelas_mp
            $used = DB::table('kelas_mp')->where('id_ta', $id)->exists();
            
            if ($used) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun ajaran sudah digunakan, tidak dapat dihapus'
                ], 400);
            }
            
            DB::table('tahun_pelajaran')->where('id_ta', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus tahun ajaran'
            ], 500);
        }
    }

    /**
     * Set tahun ajaran as active
     */
    public function setActive($id)
    {
        try {
            // Deactivate all
            DB::table('tahun_pelajaran')->update(['aktif' => 'N']);
            
            // Activate selected
            DB::table('tahun_pelajaran')
                ->where('id_ta', $id)
                ->update(['aktif' => 'Y']);
            
            return response()->json([
                'success' => true,
                'message' => 'Tahun ajaran berhasil diaktifkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan tahun ajaran'
            ], 500);
        }
    }
}