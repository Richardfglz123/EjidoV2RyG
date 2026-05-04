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
        Schema::table('Usuario', function (Blueprint $table) {
            // Agregamos la columna 'foto' que permita valores nulos
            $table->string('foto')->nullable()->after('Correo');
        });
    }

    public function down(): void
    {
        Schema::table('Usuario', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
