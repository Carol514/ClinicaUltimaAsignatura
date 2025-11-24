<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BackupController;
use App\Models\Role;
use App\Models\User;
use App\Http\Controllers\Recepcionista\RecepcionistaController;

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

    $roleCode = $roleRow->code ?? strtolower($roleRow->name ?? 'paciente');

    // Store useful session values
    session([
        'userName' => $user->name,
        'userRole' => $roleCode,
    ]);

    return match ($roleCode) {
        'administrador'        => redirect()->route('admin.panel'),
        'medico'       => redirect()->route('medico.panel'),
        'enfermera'        => redirect()->route('enfermera.panel'),
        'recepcionista' => redirect()->route('recepcionista.panel'),
        'paciente'        => redirect()->route('paciente.panel'),
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

        Route::post('/documentos', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->uploadDocuments(request());
        });

    Route::get('/tratamientos', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        // Allow medicos, enfermeras and administrators to open the tratamientos view
        if (!in_array($role, ['medico','enfermera','administrador'])) return redirect()->route('login');
        return view('medico.tratamientos');
    })->name('medico.tratamientos');

    Route::get('/alta-historial', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'medico') return redirect()->route('login');
        return view('medico.alta_historial');
    })->name('medico.alta');

    // Medico API endpoints (require medico or administrador role)
    Route::prefix('api')->group(function () {
        Route::get('dashboard', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->dashboardStats(request());
        });

        Route::get('history', function () { 
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->historial(request());
        });

        Route::get('patients', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->searchPatients(request());
        });

        Route::get('diagnoses', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->getDiagnoses(request());
        });

        Route::get('documentos', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->documentos(request());
        });

        Route::get('documentos/{id}/download', function ($id) {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->downloadDocument($id);
        });

        Route::post('upload-documentos', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->uploadDocuments(request());
        });

        Route::get('tratamientos', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            // Allow medicos, enfermeras and administrators to fetch tratamientos via API
            if (!in_array($role, ['medico','enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->tratamientos(request());
        });

        Route::post('tratamientos', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            // Allow medicos, enfermeras and administrators to create/update tratamientos via API
            if (!in_array($role, ['medico','enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->storeTreatment(request());
        });

        Route::post('encounters', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->storeEncounter(request());
        });

        Route::get('debug-auth', function () {
            return response()->json([
                'session_role' => session('userRole'),
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
                'all_session' => session()->all()
            ]);
        });

        Route::get('vitals', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->vitals(request());
        });

        Route::post('alta-historial', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->storeAltaHistorial(request());
        });

        Route::get('allergies', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->getAllergies(request());
        });

        Route::get('medical-history', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->getMedicalHistory(request());
        });

        Route::post('documentos', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->uploadDocuments(request());
        });

        Route::get('documentos/{id}/download', function ($id) {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['medico','administrador'])) abort(403);
            return app(\App\Http\Controllers\Medico\MedicoController::class)->downloadDocument($id);
        });
    });
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

    // Enfermera API endpoints
    Route::prefix('api')->group(function () {
        Route::get('paciente', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Enfermera\EnfermeraController::class)->searchPatients(request());
        });

        Route::get('vitals', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Enfermera\EnfermeraController::class)->vitals(request());
        });

        Route::post('vitals', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Enfermera\EnfermeraController::class)->storeVital(request());
        });

        Route::get('treatments', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Enfermera\EnfermeraController::class)->treatments(request());
        });

        Route::post('treatments', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Enfermera\EnfermeraController::class)->storeTreatment(request());
        });

        Route::put('treatments/{id}', function ($id) {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['enfermera','administrador'])) abort(403);
            return app(\App\Http\Controllers\Enfermera\EnfermeraController::class)->updateTreatment(request(), $id);
        });
    });

    // Temporary debug route for enfermera to inspect patient rows (only enfermera or admin)
    Route::get('debug/patients', function (Request $request) {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if (!in_array($role, ['enfermera','administrador'])) abort(403);

        $q = $request->query('q');
        if (!$q) return response()->json([], 200);

        $qClean = trim($q);
        $rows = DB::table('patients')
            ->select('id','first_name','last_name','email','curp','dob','sex','created_at')
            ->where(function($qq) use ($qClean){
                $qq->where('first_name', 'like', "%{$qClean}%")
                   ->orWhere('last_name', 'like', "%{$qClean}%")
                   ->orWhere('email', 'like', "%{$qClean}%")
                   ->orWhere('curp', 'like', "%{$qClean}%")
                   ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$qClean}%"])
                   ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$qClean}%"]);
            })->limit(50)->get();

        return response()->json($rows);
    });
});

/* ============================================================
|                        RECEPCIONISTA
============================================================ */
Route::prefix('recepcionista')->group(function () {
    Route::get('/', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'recepcionista') return redirect()->route('login');
        return view('recepcionista.panel');
    })->name('recepcionista.panel');

    Route::get('/registro', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'recepcionista') return redirect()->route('login');
        return view('recepcionista.registro');
    })->name('recepcionista.registro');
    
    Route::get('/pacientes', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'recepcionista') return redirect()->route('login');
        return view('recepcionista.pacientes');
    })->name('recepcionista.pacientes');
    

    Route::get('/citas', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'recepcionista') return redirect()->route('login');
        return view('recepcionista.citas');
    })->name('recepcionista.citas');
    // Si en algún momento quieres volver a incluir agenda:
    // Route::view('/agenda',   'recepcionista.agenda')->name('recepcionista.agenda');

    // Recepcionista API endpoints (patient & appointment CRUD)
    Route::prefix('api')->group(function () {
        Route::get('patients', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->listPatients(request());
        });

        Route::get('patients/search', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->searchPatients(request());
        });

        Route::get('patients/{id}', function ($id) {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->getPatient(request(), $id);
        });

        // Medicos (doctors) list for select inputs
        Route::get('medicos', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->listMedicos(request());
        });

        Route::post('patients', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->storePatient(request());
        });

        Route::put('patients/{id}', function ($id) {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->updatePatient(request(), $id);
        });

        // Appointments
        Route::get('appointments', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->listAppointments(request());
        });

        Route::post('appointments', function () {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->storeAppointment(request());
        });

        Route::put('appointments/{id}', function ($id) {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->updateAppointment(request(), $id);
        });

        Route::delete('appointments/{id}', function ($id) {
            $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
                ->join('roles','roles.id','=','users_roles.role_id')
                ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
            if (!in_array($role, ['recepcionista','administrador'])) abort(403);
            return app(RecepcionistaController::class)->deleteAppointment(request(), $id);
        });
    });
});

/* ============================================================
|                        PACIENTE
============================================================ */
Route::prefix('paciente')->group(function () {
    Route::get('/', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'paciente') return redirect()->route('login');
        return view('paciente.panel');
    })->name('paciente.panel');

    Route::get('/historial', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'paciente') return redirect()->route('login');
        return view('paciente.historial');
    })->name('paciente.historial');

    Route::get('/recordatorios', function () {
        $role = session('userRole') ?? (Auth::check() ? DB::table('users_roles')
            ->join('roles','roles.id','=','users_roles.role_id')
            ->where('users_roles.user_id', Auth::id())->value('roles.code') : null);
        if ($role !== 'paciente') return redirect()->route('login');
        return view('paciente.recordatorios');
    })->name('paciente.recordatorios');

    // Paciente API endpoints used by paciente UI (return data from DB)
    Route::prefix('api')->group(function () {
        Route::get('history', [\App\Http\Controllers\Paciente\PatientController::class, 'history']);
        Route::get('reminders', [\App\Http\Controllers\Paciente\PatientController::class, 'reminders']);
        Route::get('notifications', [\App\Http\Controllers\Paciente\PatientController::class, 'getNotifications']);
        Route::post('notifications', [\App\Http\Controllers\Paciente\PatientController::class, 'notifications']);
        Route::get('documents/{id}/download', [\App\Http\Controllers\Paciente\PatientController::class, 'downloadDocument']);
    });
});

/* ============================================================
|                        RAÍZ
============================================================ */
Route::redirect('/', '/login');
