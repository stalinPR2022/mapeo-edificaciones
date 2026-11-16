<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edificaciones', function (Blueprint $table) {
            $table->id('id_edificacion');
            $table->string('nombre', 150);
            $table->string('tipo', 30);
            $table->string('direccion', 255);
            $table->text('descripcion')->nullable();

            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);

            $table->string('estado_inspeccion', 20)
                  ->default('Pendiente');

            $table->text('observacion')->nullable();

            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamp('fecha_actualizacion_estado')
                  ->useCurrent();

            $table->foreignId('id_usuario')
                  ->constrained('usuarios', 'id_usuario')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edificaciones');
    }
};
