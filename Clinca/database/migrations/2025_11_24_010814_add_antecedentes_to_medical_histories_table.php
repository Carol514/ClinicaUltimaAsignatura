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
        Schema::table('medical_histories', function (Blueprint $table) {
            // Agregar columna de antecedentes médicos
            // La ponemos después de 'details', que sí existe
            $table->text('medical_background')
                  ->nullable()
                  ->after('details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_histories', function (Blueprint $table) {
            $table->dropColumn('medical_background');
        });
    }
};
