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
        Schema::table('Ejidatario', function (Blueprint $table) {
            $table->text('qr_payload')->after('Num_Ejidatario')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('Ejidatario', function (Blueprint $table) {
            $table->dropColumn('qr_payload');
        });
    }
};
