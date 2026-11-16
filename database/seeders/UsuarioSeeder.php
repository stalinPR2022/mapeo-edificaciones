<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $contrasena = env('USUARIOS_PRUEBA_PASSWORD');

        if (!$contrasena) {
            throw new RuntimeException(
                'Configurá USUARIOS_PRUEBA_PASSWORD en el archivo .env'
            );
        }

        $usuarios = [
            [
                'nombre' => 'Inspector de prueba',
                'correo' => 'inspector@ejemplo.test',
                'rol' => 'inspector',
            ],
            [
                'nombre' => 'Supervisor de prueba',
                'correo' => 'supervisor@ejemplo.test',
                'rol' => 'supervisor',
            ],
        ];

        foreach ($usuarios as $usuario) {
            User::updateOrCreate(
                ['correo' => $usuario['correo']],
                [
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['rol'],
                    'contrasena' => Hash::make($contrasena),
                    'activo' => true,
                ]
            );
        }
    }
}
