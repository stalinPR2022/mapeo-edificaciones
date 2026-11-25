<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Foundation\Events\Dispatchable;

class EdificacionActualizada implements ShouldBroadcastNow, ShouldRescue
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $idEdificacion,
        public string $accion
    ) {}

    // Canal privado al que se enviará el evento.
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('supervision.edificaciones')
        ];
    }

    // Nombre que escuchará Laravel Echo.
    public function broadcastAs(): string
    {
        return 'edificacion.actualizada';
    }

    // Datos enviados a los supervisores conectados.
    public function broadcastWith(): array
    {
        return [
            'id_edificacion' => $this->idEdificacion,
            'accion' => $this->accion,
        ];
    }
}
