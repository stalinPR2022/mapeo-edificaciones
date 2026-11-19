<?php

namespace App\Http\Controllers;

use App\Models\Edificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupervisionController extends Controller
{
    // Mostrar la pantalla del supervisor.
    public function index(): View
    {
        abort_unless(
            Auth::user()?->rol === 'supervisor',
            403,
            'No tienes permiso para acceder a supervisión.'
        );

        return view('supervision.index');
    }

    // Proporcionar información actualizada al mapa.
    public function datos(): JsonResponse
    {
        abort_unless(
            Auth::user()?->rol === 'supervisor',
            403,
            'No tienes permiso para consultar supervisión.'
        );

        $edificaciones = Edificacion::query()
            ->select([
                'id_edificacion',
                'nombre',
                'tipo',
                'direccion',
                'descripcion',
                'latitud',
                'longitud',
                'estado_inspeccion',
                'observacion',
                'fecha_registro',
                'fecha_actualizacion_estado',
            ])
            ->orderBy('id_edificacion', 'desc')
            ->get();

        return response()->json([
            'edificaciones' => $edificaciones,
            'fecha_consulta' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store');
    }
}
