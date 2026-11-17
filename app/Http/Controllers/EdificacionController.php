<?php

namespace App\Http\Controllers;

use App\Models\Edificacion;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EdificacionController extends Controller
{
    // Mostrar formulario de registro
    public function create(): View
    {
        abort_unless(
            Auth::user()?->rol === 'inspector',
            403,
            'No tienes permiso para registrar edificaciones.'
        );

        return view('edificaciones.create');
    }

    // Guardar una nueva edificación
    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            Auth::user()?->rol === 'inspector',
            403,
            'No tienes permiso para registrar edificaciones.'
        );

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'tipo' => [
                'required',
                'in:Residencial,Comercial,Educativa,Industrial,Otra'
            ],
            'direccion' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],

            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
        ]);

        // Los campos de estado y usuario no se reciben del formulario.
        Edificacion::create([
            ...$datos,
            'id_usuario' => Auth::id(),
            'estado_inspeccion' => 'Pendiente',
        ]);

        return redirect()
            ->route('edificaciones.create')
            ->with('success', 'Edificación registrada correctamente.');
    }
    //index
    public function index(): View
    {
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

        return view('edificaciones.index', compact('edificaciones'));
    }

    //Actualizar estado
    public function actualizarEstado(
        Request $request,
        int $edificacion
    ): RedirectResponse {

    // Solo los inspectores pueden modificar inspecciones.
    abort_unless(
        Auth::user()?->rol === 'inspector',
        403,
        'No tienes permiso para actualizar inspecciones.'
    );

    // Validar los datos enviados.
    $datos = $request->validate([
        'estado_inspeccion' => [
            'required',
            'string',
            'in:Pendiente,En proceso,Completada'
        ],
        'observacion' => [
            'nullable',
            'string'
        ],
    ]);

    // Buscar la edificación en PostgreSQL.
    $registro = Edificacion::findOrFail($edificacion);

    // Actualizar fecha únicamente si cambió el estado.
    if (
        $registro->estado_inspeccion !==
        $datos['estado_inspeccion']
    ) {
        $registro->fecha_actualizacion_estado = now();
    }

    // Guardar el estado y la observación.
    $registro->estado_inspeccion = $datos['estado_inspeccion'];

    $registro->observacion = $datos['observacion'] ?? null;

    $registro->save();

    return redirect()
        ->route('edificaciones.index', [
            'seleccionada' => $registro->id_edificacion
        ])
        ->with(
            'success',
            'Estado de inspección actualizado correctamente.'
        );
    }
}
