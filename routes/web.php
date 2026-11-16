<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
});
