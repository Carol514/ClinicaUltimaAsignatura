<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/administrador', 'administrador');
Route::view('/administrar-roles', 'administrar_roles');   // luego creamos esta vista
Route::view('/reportes', 'generador_reportes');           // luego creamos esta vista
Route::view('/respaldo-bd', 'respaldo_bd');               // luego creamos esta vista
