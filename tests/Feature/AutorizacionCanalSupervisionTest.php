<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AutorizacionCanalSupervisionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Utilizar Reverb durante las pruebas.
        config()->set('broadcasting.default', 'reverb');

        // Registrar los canales de la aplicación
        // en el broadcaster utilizado por la prueba.
        require base_path('routes/channels.php');
    }

    
    public function test_inspector_no_puede_suscribirse_al_canal(): void
    {
        // Crear un usuario inspector simulado.
        $inspector = new User();

        $inspector->forceFill([
            'id_usuario' => 1,
            'nombre' => 'Inspector de prueba',
            'correo' => 'inspector@ejemplo.test',
            'rol' => 'inspector',
            'activo' => true,
        ]);

        // Autenticar al inspector durante la prueba.
        $this->actingAs($inspector, 'web');

        // Comprobar que está autenticado correctamente.
        $this->assertAuthenticatedAs($inspector, 'web');

        // Intentar suscribirse al canal privado.
        $respuesta = $this->post('/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-supervision.edificaciones',
        ]);

        // Verificar que el acceso sea denegado.
        $respuesta->assertForbidden();
    }


    public function test_supervisor_puede_suscribirse_al_canal(): void
    {
        // Crear un usuario supervisor simulado.
        $supervisor = new User();

        $supervisor->forceFill([
            'id_usuario' => 2,
            'nombre' => 'Supervisor de prueba',
            'correo' => 'supervisor@ejemplo.test',
            'rol' => 'supervisor',
            'activo' => true,
        ]);

        
            $this->actingAs($supervisor, 'web');

            // Verificar que Laravel reconoce al usuario autenticado.
            $this->assertAuthenticatedAs($supervisor, 'web');

            // Verificar los atributos utilizados por el canal.
            $this->assertSame('supervisor', $supervisor->rol);
            $this->assertTrue((bool) $supervisor->activo);

            // Solicitar autorización al canal privado.
            $respuesta = $this->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => 'private-supervision.edificaciones',
            ]);

            // Mostrar información útil si la autorización falla.
            if ($respuesta->status() !== 200) {
                fwrite(STDERR, "\nEstado HTTP: " . $respuesta->status());
                fwrite(STDERR, "\nRespuesta: " . $respuesta->getContent() . "\n");
            }

            $respuesta->assertOk();

    }
}
