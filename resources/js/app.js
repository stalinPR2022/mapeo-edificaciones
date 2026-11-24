import './bootstrap';
import './echo';

//Temporal

document.addEventListener('DOMContentLoaded', () => {
    // Ejecutar esta prueba solo en supervisión.
    if (!document.getElementById('mapa')) {
        return;
    }

    // Evitar ejecutar la prueba en otras pantallas
    // que también tienen un mapa.
    if (!document.getElementById('estado-conexion')) {
        return;
    }

    if (!window.Echo) {
        console.error('Laravel Echo no está disponible.');
        return;
    }

    // Comprobar conexión WebSocket.
    window.Echo.connector.pusher.connection.bind(
        'connected',
        () => {
            console.log('Conexión WebSocket establecida.');
        }
    );

    // Comprobar autorización del canal privado.
    
    window.Echo.private('supervision.edificaciones')

        .subscribed(() => {
            console.log(
                'Suscripción privada autorizada: supervision.edificaciones'
            );
        })

        .listen('.edificacion.actualizada', (evento) => {
            console.log(
                'Evento recibido desde Laravel Reverb:',
                evento
            );
        })

        .error((error) => {
            console.error(
                'Error al suscribirse al canal privado:',
                error
            );
        });

});

