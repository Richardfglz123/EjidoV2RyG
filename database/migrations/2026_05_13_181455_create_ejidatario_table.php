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
        Schema::create('Ejidatario', function (Blueprint $table) {
            $table->id('Id_Ejidatario');
            // Relación con la tabla usuario
            $table->unsignedBigInteger('Id_Usuario');
            $table->string('Num_Ejidatario');
            $table->text('qr_payload')->nullable();
            $table->integer('Id_Estatus')->default(1);
            $table->timestamp('Fecha_Ingreso')->useCurrent();
            $table->timestamps();

            // Si quieres que sea una llave foránea real:
            $table->foreign('Id_Usuario')->references('Id_Usuario')->on('usuario')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Ejidatario');
    }

};
