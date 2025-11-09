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

// Médico
Route::prefix('medico')->group(function () {
    Route::view('/',            'medico.panel')->name('medico.panel');
    Route::view('/historial',   'medico.historial')->name('medico.historial');
    Route::view('/documentos',  'medico.documentos')->name('medico.documentos');
    Route::view('/tratamientos','medico.tratamientos')->name('medico.tratamientos');
    Route::view('/alta-historial', 'medico.alta_historial')->name('medico.alta');
});

// Enfermera
Route::prefix('enfermera')->group(function () {
    Route::view('/',       'enfermera.panel')->name('enfermera.panel');
    Route::view('/signos', 'enfermera.signos')->name('enfermera.signos');
});

// Recepcionista 
Route::prefix('recepcionista')->group(function () {
    Route::view('/',         'recepcionista.panel')->name('recepcionista.panel');
    Route::view('/registro', 'recepcionista.registro')->name('recepcionista.registro');

    // HU-15
    Route::view('/citas',    'recepcionista.citas')->name('recepcionista.citas');
    //Route::view('/agenda',   'recepcionista.agenda')->name('recepcionista.agenda');
});


// Paciente
Route::prefix('paciente')->group(function () {
    Route::view('/',              'paciente.panel')->name('paciente.panel');          // dashboard
    Route::view('/historial',     'paciente.historial')->name('paciente.historial');  // consulta historial
    Route::view('/recordatorios', 'paciente.recordatorios')->name('paciente.recordatorios'); // recordatorios
});


/* ---------- REDIRECCIÓN RAÍZ ---------- */
Route::redirect('/', '/login');
