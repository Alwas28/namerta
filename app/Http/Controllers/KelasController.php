<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    /**
     * Display list of kelas (master data only)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('kelas');
            
            // Search
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('nama_kelas', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }
            
            // Ordering
            if ($request->has('order')) {
                $columns = ['id_kelas', 'nama_kelas', 'deskripsi', 'aktif'];
                $columnIndex = $request->order[0]['column'];
                
                if ($columnIndex > 0 && $columnIndex <= count($columns)) {
                    $columnName = $columns[$columnIndex - 1];
                    $direction = $request->order[0]['dir'];
                    $query->orderBy($columnName, $direction);
                }
            } else {
                $query->orderBy('nama_kelas', 'asc');
            }
            
            $totalData = DB::table('kelas')->count();
            $totalFiltered = $query->count();
            
            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            $kelas = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($kelas as $index => $k) {
                // Action buttons
                $btn = '<button onclick="editKelas('.$k->id_kelas.')" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm mr-1">
                            <i class="fas fa-edit"></i>
                        </button>';
                
                // Check if kelas is used in kelas_ta
                $used = DB::table('kelas_ta')->where('id_kelas', $k->id_kelas)->exists();
                
                if (!$used) {
                    $btn .= '<button onclick="deleteKelas('.$k->id_kelas.')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">
                                <i class="fas fa-trash"></i>
                            </button>';
                }
                
                // Status badge
                $status = $k->aktif === 'Y' 
                    ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Non-Aktif</span>';
                
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nama_kelas' => $k->nama_kelas,
                    'deskripsi' => $k->deskripsi ?? '-',
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
        
        return view('admin.kelas');
    }

    /**
     * Store new kelas
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|unique:kelas,nama_kelas|max:100',
            'deskripsi' => 'nullable|max:500',
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            DB::table('kelas')->insert([
                'nama_kelas' => $request->nama_kelas,
                'deskripsi' => $request->deskripsi,
                'aktif' => $request->aktif,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kelas'
            ], 500);
        }
    }

    /**
     * Get kelas for edit
     */
    public function edit($id)
    {
        $kelas = DB::table('kelas')->where('id_kelas', $id)->first();
        
        if (!$kelas) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
        
        return response()->json($kelas);
    }

    /**
     * Update kelas
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kelas' => 'required|max:100|unique:kelas,nama_kelas,'.$id.',id_kelas',
            'deskripsi' => 'nullable|max:500',
            'aktif' => 'required|in:Y,N'
        ]);

        try {
            DB::table('kelas')
                ->where('id_kelas', $id)
                ->update([
                    'nama_kelas' => $request->nama_kelas,
                    'deskripsi' => $request->deskripsi,
                    'aktif' => $request->aktif
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate kelas'
            ], 500);
        }
    }

    /**
     * Delete kelas
     */
    public function destroy($id)
    {
        try {
            // Check if used in kelas_ta
            $used = DB::table('kelas_ta')->where('id_kelas', $id)->exists();
            
            if ($used) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kelas sudah digunakan di tahun ajaran, tidak dapat dihapus'
                ], 400);
            }
            
            DB::table('kelas')->where('id_kelas', $id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Kelas berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kelas'
            ], 500);
        }
    }
}