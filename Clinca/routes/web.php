<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BackupController;

/* ============================================================
|                        LOGIN / LOGOUT
============================================================ */
Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
    $name = $request->input('username', 'Usuario');
    $role = $request->input('role');

    if (!in_array($role, ['admin','doctor','nurse','receptionist','patient'])) {
        return back()->withErrors(['role' => 'Selecciona un rol válido'])->withInput();
    }

    // Simulación de sesión temporal (más adelante se reemplazará con Auth real)
    session([
        'userName' => $name,
        'userRole' => $role,
    ]);

    return match ($role) {
        'admin'        => redirect()->route('admin.panel'),
        'doctor'       => redirect()->route('medico.panel'),
        'nurse'        => redirect()->route('enfermera.panel'),
        'receptionist' => redirect()->route('recepcionista.panel'),
        'patient'      => redirect()->route('paciente.panel'),
    };
})->name('login.post');

Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('login');
})->name('logout');

/* ============================================================
|                        ADMINISTRADOR
============================================================ */
Route::prefix('administrador')->group(function () {
    Route::view('/', 'administrador')->name('admin.panel');
    Route::view('/usuarios-roles', 'administrador.usuarios_roles')->name('admin.roles');
    Route::view('/respaldos', 'administrador.respaldos')->name('admin.respaldos');
    Route::view('/reportes',  'administrador.reportes')->name('admin.reportes');

    // Lightweight admin JSON API (used by admin frontend JS)
    Route::prefix('api')->group(function () {
        // roles
        Route::get('roles', [RoleController::class, 'index']);
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);

        // users
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{user}/role', [UserController::class, 'updateRole']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);

        // reports
        Route::post('reports', [ReportController::class, 'generate']);

        // backups
        Route::post('backups', [BackupController::class, 'store']);
        Route::get('backups/{dir}/download', [BackupController::class, 'download'])->name('admin.backup.download');
    });
});

/* ============================================================
|                        MÉDICO
============================================================ */
Route::prefix('medico')->group(function () {
    Route::view('/',              'medico.panel')->name('medico.panel');
    Route::view('/historial',     'medico.historial')->name('medico.historial');
    Route::view('/documentos',    'medico.documentos')->name('medico.documentos');
    Route::view('/tratamientos',  'medico.tratamientos')->name('medico.tratamientos');
    Route::view('/alta-historial','medico.alta_historial')->name('medico.alta');
});

/* ============================================================
|                        ENFERMERA
============================================================ */
Route::prefix('enfermera')->group(function () {
    Route::view('/',       'enfermera.panel')->name('enfermera.panel');
    Route::view('/signos', 'enfermera.signos')->name('enfermera.signos');
});

/* ============================================================
|                        RECEPCIONISTA
============================================================ */
Route::prefix('recepcionista')->group(function () {
    Route::view('/',         'recepcionista.panel')->name('recepcionista.panel');
    Route::view('/registro', 'recepcionista.registro')->name('recepcionista.registro');
    Route::view('/citas',    'recepcionista.citas')->name('recepcionista.citas');
    // Si en algún momento quieres volver a incluir agenda:
    // Route::view('/agenda',   'recepcionista.agenda')->name('recepcionista.agenda');
});

/* ============================================================
|                        PACIENTE
============================================================ */
Route::prefix('paciente')->group(function () {
    Route::view('/',              'paciente.panel')->name('paciente.panel');
    Route::view('/historial',     'paciente.historial')->name('paciente.historial');
    Route::view('/recordatorios', 'paciente.recordatorios')->name('paciente.recordatorios');
});

/* ============================================================
|                        RAÍZ
============================================================ */
Route::redirect('/', '/login');
