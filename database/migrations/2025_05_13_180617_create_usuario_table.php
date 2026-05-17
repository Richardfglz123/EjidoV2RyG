<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('Id_Usuario');
            $table->string('Nombres');
            $table->string('Apellido_Paterno')->nullable();
            $table->string('Apellido_Materno')->nullable();

            // AGREGA ESTA COLUMNA (la que causó el error)
            $table->string('Usuario')->unique();

            $table->string('Correo')->unique();

            // CAMBIA 'Password' por 'Contraseña' para que coincida con tu Seeder
            // O cambia el Seeder para que diga 'Password'.
            // Lo más fácil ahora es que coincida con el error:
            $table->string('Contraseña');

            // AGREGA ESTAS PARA QUE EL SEEDER NO TRONE
            $table->string('Telefono')->nullable();
            $table->timestamp('Fecha_Creo')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
