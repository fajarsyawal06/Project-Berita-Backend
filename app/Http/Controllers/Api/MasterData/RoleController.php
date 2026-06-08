<?php

namespace App\Http\Controllers\Api\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $data = Role::with('permissions')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_role' => 'nullable|string|unique:roles,kode_role',
            'nama_role' => 'required|string|max:255',
        ]);

        $data = $request->all();
        if (empty($data['kode_role'])) {
            $latest = Role::latest('id')->first();
            $nextId = $latest ? $latest->id + 1 : 1;
            $data['kode_role'] = 'R-' . str_pad($nextId, 2, '0', STR_PAD_LEFT);
        }

        $role = Role::create($data);

        if ($request->has('permissions') && is_array($request->permissions)) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json(['success' => true, 'message' => 'Role berhasil ditambahkan', 'data' => $role], 201);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->find($id);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role tidak ditemukan'], 404);
        }
        return response()->json(['success' => true, 'data' => $role]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_role' => 'nullable|string|unique:roles,kode_role,' . $id,
            'nama_role' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $role->update($request->all());

        if ($request->has('permissions') && is_array($request->permissions)) {
            $role->permissions()->sync($request->permissions);
        }

        $role->load('permissions');

        return response()->json(['success' => true, 'message' => 'Role berhasil diperbarui', 'data' => $role]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['success' => false, 'message' => 'Role tidak ditemukan'], 404);
        }

        $role->delete();

        return response()->json(['success' => true, 'message' => 'Role berhasil dihapus']);
    }
}
