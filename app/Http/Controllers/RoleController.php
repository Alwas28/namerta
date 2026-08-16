<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Role::withCount('users');
            
            // Search functionality
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('nama_role', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }
            
            // Ordering
            if ($request->has('order')) {
                $columns = ['id_role', 'nama_role', 'deskripsi', 'users_count'];
                $columnIndex = $request->order[0]['column'];
                $columnName = $columns[$columnIndex] ?? 'id_role';
                $direction = $request->order[0]['dir'];
                $query->orderBy($columnName, $direction);
            }
            
            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            $totalData = Role::count();
            $totalFiltered = $query->count();
            
            $roles = $query->skip($start)->take($length)->get();
            
            $data = [];
            foreach ($roles as $index => $role) {
                // Definisikan $btn DULU sebelum digunakan
                $btn = '<button onclick="editRole('.$role->id_role.')" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-sm mr-1">
                            <i class="fas fa-edit"></i>
                        </button>';
                $btn .= '<a href="'.route('roles.permissions.show', $role->id_role).'" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-sm mr-1">
                            <i class="fas fa-key"></i>
                        </a>';
                        
                if ($role->nama_role !== 'super_admin') {
                    $btn .= '<button onclick="deleteRole('.$role->id_role.')" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">
                                <i class="fas fa-trash"></i>
                            </button>';
                }
                
                // Baru masukkan ke array
                $data[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'nama_role' => $role->nama_role,
                    'deskripsi' => $role->deskripsi ?? '-',
                    'total_users' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">' 
                                    . $role->users_count . ' users</span>',
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
        
        return view('admin.role');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_role' => 'required|unique:roles,nama_role|max:50',
            'deskripsi' => 'nullable|max:255'
        ]);

        try {
            $role = Role::create([
                'nama_role' => $request->nama_role,
                'deskripsi' => $request->deskripsi
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan role'
            ], 500);
        }
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_role' => 'required|max:50|unique:roles,nama_role,'.$id.',id_role',
            'deskripsi' => 'nullable|max:255'
        ]);

        try {
            $role = Role::findOrFail($id);
            $role->update([
                'nama_role' => $request->nama_role,
                'deskripsi' => $request->deskripsi
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate role'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            
            // Prevent deleting super_admin role
            if ($role->nama_role === 'super_admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Role super_admin tidak dapat dihapus'
                ], 403);
            }

            // Check if role has users
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role masih memiliki user, tidak dapat dihapus'
                ], 400);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus role'
            ], 500);
        }
    }

    public function getPermissions($id)
    {
        $role = Role::findOrFail($id);
        $allPermissions = Permission::orderBy('grup')->orderBy('nama_permission')->get();
        $rolePermissions = $role->permissions->pluck('id_permission')->toArray();
        
        $groupedPermissions = $allPermissions->groupBy('grup');
        
        return response()->json([
            'role' => $role,
            'permissions' => $groupedPermissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function updatePermissions(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);
            $permissions = $request->permissions ?? [];
            
            // Sync permissions
            $role->permissions()->sync($permissions);
            
            return response()->json([
                'success' => true,
                'message' => 'Permissions berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate permissions'
            ], 500);
        }
    }


    public function showPermissions($id)
    {
        $role = Role::findOrFail($id);
        
        // Get all permissions for this role
        $rolePermissions = DB::table('role_permissions')
            ->join('permissions', 'role_permissions.id_permission', '=', 'permissions.id_permission')
            ->where('role_permissions.id_role', $id)
            ->pluck('permissions.nama_permission')
            ->toArray();
        
        return view('admin.role_permission', compact('role', 'rolePermissions'));
    }

    public function updatePermissionsPage(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);
            
            // Get permission names from request
            $permissionNames = $request->permissions ?? [];
            
            // Get permission IDs based on names
            $permissionIds = Permission::whereIn('nama_permission', $permissionNames)
                ->pluck('id_permission')
                ->toArray();
            
            // Sync permissions
            $role->permissions()->sync($permissionIds);
            
            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permissions: ' . $e->getMessage()
            ], 500);
        }
    }
}