<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EdificacionController;
use App\Http\Controllers\SupervisionController;

Route::get('/', function () {
    return redirect()->route(
        //auth()->check() ? 'inicio' : 'login'
        Auth::check() ? 'inicio' : 'login'
    );
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'mostrarLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'iniciarSesion'])
        ->middleware('throttle:5,1')
        ->name('login.procesar');
});

Route::middleware('auth')->group(function () {
    Route::get('/inicio', function () {
        return view('inicio');
    })->name('inicio');

    Route::post('/logout', [AuthController::class, 'cerrarSesion'])
        ->name('logout');
    
    // Registro de edificaciones
    Route::get(
        '/edificaciones/crear',
        [EdificacionController::class, 'create']
    )->name('edificaciones.create');

    Route::post(
        '/edificaciones',
        [EdificacionController::class, 'store']
    )->name('edificaciones.store');

    // fin Registro de edificaciones

    //Obtener edificaciones
    Route::get(
        '/edificaciones',
        [EdificacionController::class, 'index']
    )->name('edificaciones.index');

    //Ruta para la actualización
    Route::patch(
        '/edificaciones/{edificacion}/estado',
        [EdificacionController::class, 'actualizarEstado']
    )->name('edificaciones.estado.update');

    //Para la pantalla de supervision
    Route::get(
        '/supervision',
        [SupervisionController::class, 'index']
    )->name('supervision.index');

    Route::get(
        '/supervision/datos',
        [SupervisionController::class, 'datos']
    )->name('supervision.datos');


});

