<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:roles,code',
            'name' => 'required|string',
        ]);

        $role = Role::create($data);
        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'sometimes|string',
            'permissions' => 'sometimes|array',
        ]);

        if (array_key_exists('name', $data)) {
            $role->name = $data['name'];
        }
        if (array_key_exists('permissions', $data)) {
            $role->permissions = $data['permissions'];
        }
        $role->save();

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['deleted' => true]);
    }
}
