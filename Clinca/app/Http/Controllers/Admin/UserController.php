<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function index()
    {
        // Build users + roles without relying on a User::roles relationship (models are final)
        $users = DB::table('users')->select('id', 'name', 'email')->get()->map(function($u){
            return (array) $u;
        })->toArray();

        // Fetch pivot roles and attach to users
        $roleRows = DB::table('users_roles')
            ->join('roles', 'users_roles.role_id', '=', 'roles.id')
            ->select('users_roles.user_id', 'roles.id as role_id', 'roles.name', 'roles.code')
            ->get();

        $rolesByUser = [];
        foreach($roleRows as $r){
            $rolesByUser[$r->user_id][] = ['id'=>$r->role_id, 'name'=>$r->name, 'code'=>$r->code];
        }

        $result = array_map(function($u) use ($rolesByUser){
            $u['roles'] = $rolesByUser[$u['id']] ?? [];
            return $u;
        }, $users);

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
        ]);

        // generate a random password (will be hashed by model)
        $password = Str::random(12);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password,
        ]);

        $role = Role::where('name', $data['role'])->orWhere('code', $data['role'])->first();
        if ($role) {
            // insert directly into pivot table to avoid model relation
            DB::table('users_roles')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }

        // return user with roles
        $userArray = ['id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'roles'=>[]];
        if ($role) $userArray['roles'][] = ['id'=>$role->id,'name'=>$role->name,'code'=>$role->code];
        return response()->json($userArray, 201);
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => 'required|string',
        ]);

        $role = Role::where('name', $data['role'])->orWhere('code', $data['role'])->first();
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        // manage pivot table directly
        DB::table('users_roles')->where('user_id', $user->id)->delete();
        DB::table('users_roles')->insert(['user_id' => $user->id, 'role_id' => $role->id]);

        $userArray = ['id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'roles'=>[['id'=>$role->id,'name'=>$role->name,'code'=>$role->code]]];
        return response()->json($userArray);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['deleted' => true]);
    }
}
