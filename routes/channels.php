<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'supervision.edificaciones',
    function ($usuario) {
        return $usuario->activo
            && $usuario->rol === 'supervisor';
    }
);
