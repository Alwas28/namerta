<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MataPelajaranController extends Controller
{
    /**
     * Display list of mata pelajaran
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('mata_pelajaran');
            
            // Search
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('nama_mata_pelajaran', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }
            
            // Ordering
            if ($request->has('order')) {
                $columns = ['id_mata_pelajaran', 'nama_mata_pelajaran', 'deskripsi', 'aktif'];
                $columnIndex = $request->order[0]['column'];
                
                if ($columnIndex > 0 && $columnIndex <= count($columns)) {
                    $columnName = $columns[$columnIndex - 1];
                    $direction = $request->order[0]['dir'];
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('nama_mata_pelajaran', 'asc');
            }
            
            $totalData = DB::table('mata_pelajaran')->count();
            $totalFiltered = $query->count();
            
            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            $mataPelajaran = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($mataPelajaran as $index => $mp) {
                // Count classes using this subject
                $kelasCount = DB::table('kelas_mp')
                    ->where('id_mata_pelajaran', $mp->id_mata_pelajaran)
                    ->count();
                
                // Action buttons
                $btn = '<button onclick="editMP('.$mp->id_mata_pelajaran.')" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm mr-1">
                            <i class="fas fa-edit"></i>
                        </button>';
                
                // Only allow delete if not used in kelas_mp
                if ($kelasCount == 0) {
                    $btn .= '<button onclick="deleteMP('.$mp->id_mata_pelajaran.')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">
                                <i class="fas fa-trash"></i>
                            </button>';
                }
                
                // Status badge
                $status = isset($mp->aktif) && $mp->aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>';
                
                // Kelas count badge
                $kelasBadge = '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">'.$kelasCount.' kelas</span>';
                
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nama_mata_pelajaran' => $mp->nama_mata_pelajaran,
                    'deskripsi' => $mp->deskripsi ?? '-',
                    'kelas_count' => $kelasBadge,
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
        
        return view('admin.mata_pelajaran');
    }

    /**
     * Store new mata pelajaran
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_mata_pelajaran' => 'required|unique:mata_pelajaran,nama_mata_pelajaran|max:255',
            'deskripsi' => 'nullable|max:500',
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            DB::table('mata_pelajaran')->insert([
                'nama_mata_pelajaran' => $request->nama_mata_pelajaran,
                'deskripsi' => $request->deskripsi,
                'aktif' => $request->aktif ?? 'Y',
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan mata pelajaran'
            ], 500);
        }
    }

    /**
     * Get mata pelajaran for edit
     */
    public function edit($id)
    {
        $mp = DB::table('mata_pelajaran')->where('id_mata_pelajaran', $id)->first();
        
        if (!$mp) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
        
        return response()->json($mp);
    }

    /**
     * Update mata pelajaran
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_mata_pelajaran' => 'required|max:255|unique:mata_pelajaran,nama_mata_pelajaran,'.$id.',id_mata_pelajaran',
            'deskripsi' => 'nullable|max:500',
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            DB::table('mata_pelajaran')
                ->where('id_mata_pelajaran', $id)
                ->update([
                    'nama_mata_pelajaran' => $request->nama_mata_pelajaran,
                    'deskripsi' => $request->deskripsi,
                    'aktif' => $request->aktif
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate mata pelajaran'
            ], 500);
        }
    }

    /**
     * Delete mata pelajaran
     */
    public function destroy($id)
    {
        try {
            // Check if used in kelas_mp
            $used = DB::table('kelas_mp')->where('id_mata_pelajaran', $id)->exists();
            
            if ($used) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata pelajaran sudah digunakan di kelas, tidak dapat dihapus'
                ], 400);
            }
            
            // Check if used in modul
            $usedInModul = DB::table('modul')->where('id_mata_pelajaran', $id)->exists();
            
            if ($usedInModul) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mata pelajaran sudah memiliki modul, tidak dapat dihapus'
                ], 400);
            }
            
            DB::table('mata_pelajaran')->where('id_mata_pelajaran', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Mata pelajaran berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus mata pelajaran'
            ], 500);
        }
    }

    /**
     * Toggle status aktif/non-aktif
     */
    public function toggleStatus($id)
    {
        try {
            $mp = DB::table('mata_pelajaran')->where('id_mata_pelajaran', $id)->first();
            
            if (!$mp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            
            $newStatus = $mp->aktif === 'Y' ? 'N' : 'Y';
            
            DB::table('mata_pelajaran')
                ->where('id_mata_pelajaran', $id)
                ->update(['aktif' => $newStatus]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status'
            ], 500);
        }
    }
}