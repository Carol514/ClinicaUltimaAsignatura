<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Login (vista)
Route::view('/login', 'login')->name('login');

// Login (post simulado para redirigir por rol)
Route::post('/login', function (Request $request) {
    $name = $request->input('username', 'Usuario');
    $role = $request->input('role');

    if (!in_array($role, ['admin','doctor','nurse','receptionist','patient'])) {
        return back()->withErrors(['role' => 'Selecciona un rol válido'])->withInput();
    }

    // Guarda datos mínimos en sesión (luego se reemplaza por Auth real)
    session([
        'userName' => $name,
        'userRole' => $role,
    ]);

    return match ($role) {
        'admin'         => redirect('/administrador'),
        'doctor'        => redirect('/medico'),
        'nurse'         => redirect('/enfermera'),
        'receptionist'  => redirect('/recepcionista'),
        'patient'       => redirect('/paciente'),
    };
});

// Logout simple
Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('login');
})->name('logout');

// Dashboards de prueba (placeholders) para que no den 404.
// Los iremos reemplazando con sus vistas reales.
Route::view('/medico', 'medico')->name('medico');
Route::view('/enfermera', 'enfermera')->name('enfermera');
Route::view('/recepcionista', 'recepcionista')->name('recepcionista');
Route::view('/paciente', 'paciente')->name('paciente');

// Si quieres que la raíz vaya al login:
Route::redirect('/', '/login');
