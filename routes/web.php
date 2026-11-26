<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'mensaje' => 'Configuración funcionando correctamente.',
    ]);
})->name('home');

Route::get('/experto', function () {
    return Inertia::render('Register');
})->name('register');

Route::get('/seguridad', function () {
    return Inertia::render('Seguridad');
})->name('seguridad');

Route::get("/usuarios", function (){
    return Inertia::render("Usuarios");
})->name("usuarios");