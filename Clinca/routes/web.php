<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BackupController;
use App\Models\Role;
use App\Models\User;

/* ============================================================
|                        LOGIN / LOGOUT
============================================================ */
Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
    $username = $request->input('username');
    $password = $request->input('password');

    if (!$username || !$password) {
        return back()->withErrors(['username' => 'Usuario y contraseña son requeridos'])->withInput();
    }

    // Try authenticating by email if the input looks like an email, otherwise by name
    $credentials = filter_var($username, FILTER_VALIDATE_EMAIL)
        ? ['email' => $username, 'password' => $password]
        : ['name' => $username, 'password' => $password];

    if (!Auth::attempt($credentials)) {
        return back()->withErrors(['username' => 'Credenciales inválidas'])->withInput();
    }

    // Authentication successful
    $user = Auth::user();

    // Resolve the user's role from the pivot table (first role if multiple)
    $roleRow = DB::table('users_roles')
        ->join('roles', 'roles.id', '=', 'users_roles.role_id')
        ->where('users_roles.user_id', $user->id)
        ->select('roles.code', 'roles.name')
        ->first();

    $roleCode = $roleRow->code ?? strtolower($roleRow->name ?? 'patient');

    // Store useful session values
    session([
        'userName' => $user->name,
        'userRole' => $roleCode,
    ]);

    return match ($roleCode) {
        'administrador'        => redirect()->route('admin.panel'),
        'medico'       => redirect()->route('medico.panel'),
        'enfermera'        => redirect()->route('enfermera.panel'),
        'receptionist' => redirect()->route('recepcionista.panel'),
        default        => redirect()->route('paciente.panel'),
    };
})->name('login.post');

Route::get('/logout', function () {
    Auth::logout();
    session()->flush();
    return redirect()->route('login');
})->name('logout');

/* ============================================================
|                        ADMINISTRADOR
============================================================ */
Route::prefix('administrador')->group(function () {
    // Views: protect by role via inline checks (no kernel middleware required)
    Route::get('/', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'administrador') return redirect()->route('login');
        return view('administrador');
    })->name('admin.panel');

    Route::get('/usuarios-roles', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'administrador') return redirect()->route('login');
        return view('administrador.usuarios_roles');
    })->name('admin.roles');

    Route::get('/respaldos', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'administrador') return redirect()->route('login');
        return view('administrador.respaldos');
    })->name('admin.respaldos');

    Route::get('/reportes', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'administrador') return redirect()->route('login');
        return view('administrador.reportes');
    })->name('admin.reportes');

    // Lightweight admin JSON API (used by admin frontend JS) - each route checks role
    Route::prefix('api')->group(function () {
        // roles
        Route::get('roles', function () { if ((session('userRole') ?? null) !== 'administrador') abort(403); return app(RoleController::class)->index(); });
        Route::post('roles', function () { if ((session('userRole') ?? null) !== 'administrador') abort(403); return app(RoleController::class)->store(request()); });
        Route::put('roles/{role}', function ($role) {
            if ((session('userRole') ?? null) !== 'administrador') abort(403);
            $r = is_numeric($role)
                ? Role::findOrFail($role)
                : Role::where('code', $role)->orWhere('name', $role)->firstOrFail();
            return app(RoleController::class)->update(request(), $r);
        });
        Route::delete('roles/{role}', function ($role) {
            if ((session('userRole') ?? null) !== 'administrador') abort(403);
            $r = is_numeric($role)
                ? Role::findOrFail($role)
                : Role::where('code', $role)->orWhere('name', $role)->firstOrFail();
            return app(RoleController::class)->destroy($r);
        });

        // users
        Route::get('users', function () { if ((session('userRole') ?? null) !== 'administrador') abort(403); return app(UserController::class)->index(); });
        Route::post('users', function () { if ((session('userRole') ?? null) !== 'administrador') abort(403); return app(UserController::class)->store(request()); });
        Route::put('users/{user}/role', function ($user) {
            if ((session('userRole') ?? null) !== 'administrador') abort(403);
            $u = is_numeric($user)
                ? User::findOrFail($user)
                : User::where('email', $user)->orWhere('name', $user)->firstOrFail();
            return app(UserController::class)->updateRole(request(), $u);
        });
        Route::delete('users/{user}', function ($user) {
            if ((session('userRole') ?? null) !== 'administrador') abort(403);
            $u = is_numeric($user)
                ? User::findOrFail($user)
                : User::where('email', $user)->orWhere('name', $user)->firstOrFail();
            return app(UserController::class)->destroy($u);
        });

        // reports
        Route::post('reports', function () { if ((session('userRole') ?? null) !== 'administrador') abort(403); return app(ReportController::class)->generate(request()); });

        // backups
        Route::post('backups', function () { if ((session('userRole') ?? null) !== 'administrador') abort(403); return app(BackupController::class)->store(request()); });
        Route::get('backups/{dir}/download', function ($dir) { if ((session('userRole') ?? null) !== 'administrador') abort(403); return app(BackupController::class)->download($dir); })->name('admin.backup.download');
    });
});

/* ============================================================
|                        MÉDICO
============================================================ */
Route::prefix('medico')->group(function () {
    Route::get('/', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'medico') return redirect()->route('login');
        return view('medico.panel');
    })->name('medico.panel');

    Route::get('/historial', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'medico') return redirect()->route('login');
        return view('medico.historial');
    })->name('medico.historial');

    Route::get('/documentos', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'medico') return redirect()->route('login');
        return view('medico.documentos');
    })->name('medico.documentos');

    Route::get('/tratamientos', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'medico') return redirect()->route('login');
        return view('medico.tratamientos');
    })->name('medico.tratamientos');

    Route::get('/alta-historial', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'medico') return redirect()->route('login');
        return view('medico.alta_historial');
    })->name('medico.alta');
});

/* ============================================================
|                        ENFERMERA
============================================================ */
Route::prefix('enfermera')->group(function () {
    Route::get('/', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'enfermera') return redirect()->route('login');
        return view('enfermera.panel');
    })->name('enfermera.panel');

    Route::get('/signos', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'enfermera') return redirect()->route('login');
        return view('enfermera.signos');
    })->name('enfermera.signos');
});

/* ============================================================
|                        RECEPCIONISTA
============================================================ */
Route::prefix('recepcionista')->group(function () {
    Route::get('/', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'receptionist') return redirect()->route('login');
        return view('recepcionista.panel');
    })->name('recepcionista.panel');

    Route::get('/registro', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'receptionist') return redirect()->route('login');
        return view('recepcionista.registro');
    })->name('recepcionista.registro');

    Route::get('/citas', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'receptionist') return redirect()->route('login');
        return view('recepcionista.citas');
    })->name('recepcionista.citas');
    // Si en algún momento quieres volver a incluir agenda:
    // Route::view('/agenda',   'recepcionista.agenda')->name('recepcionista.agenda');
});

/* ============================================================
|                        PACIENTE
============================================================ */
Route::prefix('paciente')->group(function () {
    Route::get('/', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'patient') return redirect()->route('login');
        return view('paciente.panel');
    })->name('paciente.panel');

    Route::get('/historial', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'patient') return redirect()->route('login');
        return view('paciente.historial');
    })->name('paciente.historial');

    Route::get('/recordatorios', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'patient') return redirect()->route('login');
        return view('paciente.recordatorios');
    })->name('paciente.recordatorios');
});

/* ============================================================
|                        RAÍZ
============================================================ */
Route::redirect('/', '/login');
