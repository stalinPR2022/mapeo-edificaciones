import './bootstrap';
import './echo';

//Temporal

document.addEventListener('DOMContentLoaded', () => {

    // Ejecutar únicamente en la vista de supervisión.
    if (
        !document.getElementById('mapa') ||
        !document.getElementById('estado-conexion')
    ) {
        return;
    }

    if (!window.Echo) {
        console.error('Laravel Echo no está disponible.');
        return;
    }

    // Obtener la conexión WebSocket.
    const conexion = window.Echo.connector.pusher.connection;

    // Indicador visual de la conexión.
    const indicador = document.getElementById('estado-conexion');
    // Mostrar el estado actual aunque la conexión
    // se haya establecido antes de registrar los eventos.
    if (conexion.state === 'connected') {
        indicador.textContent = 'WebSocket conectado';
    } else {
        indicador.textContent = 'Conectando...';
    }

    // Variables para controlar las reconexiones.
    let conexionInterrumpida = false;
    let sincronizacionPendiente = false;

    // Cuando se establece la conexión.
    conexion.bind('connected', () => {
        console.log('Conexión WebSocket establecida.');

        indicador.textContent = 'WebSocket conectado';
    });

    // Detectar cambios en el estado de la conexión.
    conexion.bind('state_change', (estados) => {

        // Mostrar cuando se pierde la conexión.
        if (estados.current !== 'connected') {
            indicador.textContent = 'Reconectando...';
        }

        // Registrar si una conexión activa se interrumpió.
        if (
            estados.previous === 'connected' &&
            estados.current !== 'connected'
        ) {
            conexionInterrumpida = true;

            console.log(
                'Conexión WebSocket interrumpida.'
            );
        }

        // Detectar cuando se recupera la conexión.
        if (
            estados.current === 'connected' &&
            conexionInterrumpida
        ) {
            conexionInterrumpida = false;
            sincronizacionPendiente = true;

            console.log(
                'Conexión recuperada; esperando suscripción.'
            );
        }
    });

    // Suscribirse al canal privado de supervisión.
    window.Echo.private('supervision.edificaciones')

        .subscribed(() => {
            console.log(
                'Suscripción privada autorizada: supervision.edificaciones'
            );

            indicador.textContent = 'WebSocket conectado';

            // Si hubo una reconexión, recuperar información.
            if (sincronizacionPendiente) {
                sincronizacionPendiente = false;

                window.dispatchEvent(
                    new CustomEvent('supervision:actualizar')
                );

                console.log(
                    'Sincronización solicitada después de reconectar.'
                );
            }
        })

        // Escuchar los eventos enviados por Laravel.
        .listen('.edificacion.actualizada', (evento) => {

            console.log(
                'Evento recibido desde Laravel Reverb:',
                evento
            );

            // Solicitar la actualización de la vista.
            window.dispatchEvent(
                new CustomEvent('supervision:actualizar', {
                    detail: evento
                })
            );
        })

        // Mostrar errores de autorización.
        .error((error) => {
            console.error(
                'Error de autorización del canal privado:',
                error
            );

            indicador.textContent =
                'Error de autorización WebSocket';
        });
});
