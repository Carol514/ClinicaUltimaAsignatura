<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/* ---------- LOGIN ---------- */
Route::view('/login', 'login')->name('login');

Route::post('/login', function (Request $request) {
    $name = $request->input('username', 'Usuario');
    $role = $request->input('role');

    if (!in_array($role, ['admin','doctor','nurse','receptionist','patient'])) {
        return back()->withErrors(['role' => 'Selecciona un rol válido'])->withInput();
    }

    // Simulación de sesión (luego será Auth real)
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

/* ---------- PANELES ---------- */
// Admin
Route::view('/administrador', 'administrador')->name('admin.panel');

// Médico (ya creadas arriba)
Route::prefix('medico')->group(function () {
    Route::view('/',            'medico.panel')->name('medico.panel');
    Route::view('/historial',   'medico.historial')->name('medico.historial');
    Route::view('/documentos',  'medico.documentos')->name('medico.documentos');
    Route::view('/tratamientos','medico.tratamientos')->name('medico.tratamientos');
});

// Placeholders para que no den 404 (se pueden reemplazar luego)
Route::view('/enfermera',      'enfermera')->name('enfermera.panel');
Route::view('/recepcionista',  'recepcionista')->name('recepcionista.panel');
Route::view('/paciente',       'paciente')->name('paciente.panel');

/* Raíz -> login */
Route::redirect('/', '/login');

// Enfermera
Route::prefix('enfermera')->group(function () {
    Route::view('/',       'enfermera.panel')->name('enfermera.panel');
    Route::view('/signos', 'enfermera.signos')->name('enfermera.signos');
});

// Recepcionista
Route::prefix('recepcionista')->group(function () {
    Route::view('/',          'recepcionista.panel')->name('recepcionista.panel');
    Route::view('/registro',  'recepcionista.registro')->name('recepcionista.registro');

    // NUEVAS
    Route::view('/citas',     'recepcionista.citas')->name('recepcionista.citas');
    Route::view('/agenda',    'recepcionista.agenda')->name('recepcionista.agenda');
});

