<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Raíz → login
Route::redirect('/', '/login');

// Vista de login
Route::view('/login', 'login')->name('login.view');

// Post del login (redirige por rol; solo UI)
Route::post('/login', function (Request $request) {
    $name = $request->input('username', 'Usuario');
    $role = $request->input('role');

    if (!in_array($role, ['admin','doctor','nurse','receptionist','patient'])) {
        return back()->withErrors(['role' => 'Selecciona un rol válido'])->withInput();
    }

    // Guardamos algo mínimo en sesión (placeholder; sin Auth real)
    session([
        'userName' => $name,
        'userRole' => $role,
    ]);

    return match ($role) {
        'admin'        => redirect('/administrador'),
        'doctor'       => redirect('/medico'),
        'nurse'        => redirect('/enfermera'),
        'receptionist' => redirect('/recepcionista'),
        'patient'      => redirect('/paciente'),
    };
})->name('login.post');

// Logout simple (limpia sesión y vuelve al login)
Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('login.view');
})->name('logout');

// Placeholders de dashboards (solo vistas para que no den 404)
Route::view('/administrador', 'administrador')->name('administrador');
Route::view('/medico', 'medico')->name('medico');
Route::view('/enfermera', 'enfermera')->name('enfermera');
Route::view('/recepcionista', 'recepcionista')->name('recepcionista');
Route::view('/paciente', 'paciente')->name('paciente');
