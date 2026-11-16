<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function mostrarLogin(): View
    {
        return view('auth.login');
    }

    public function iniciarSesion(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credenciales = [
            'correo' => $datos['correo'],
            'password' => $datos['password'],
            'activo' => true,
        ];

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();

            return redirect()->route('inicio');
        }

        return back()
            ->withErrors([
                'correo' => 'Las credenciales son incorrectas o la cuenta está inactiva.',
            ])
            ->onlyInput('correo');
    }

    public function cerrarSesion(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
