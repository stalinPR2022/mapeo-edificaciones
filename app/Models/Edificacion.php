<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Edificacion extends Model
{
    protected $table = 'edificaciones';

    protected $primaryKey = 'id_edificacion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
        'direccion',
        'descripcion',
        'latitud',
        'longitud',
        'estado_inspeccion',
        'observacion',
        'id_usuario',
    ];

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'fecha_registro' => 'datetime',
            'fecha_actualizacion_estado' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id_usuario'
        );
    }
}
