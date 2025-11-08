<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('encounter_id');              // FK a encounters.uuid
            $table->timestamp('taken_at')->useCurrent();
            $table->decimal('temp_c',4,1)->nullable();
            $table->integer('sbp')->nullable();
            $table->integer('dbp')->nullable();
            $table->integer('hr')->nullable();
            $table->integer('rr')->nullable();
            $table->integer('spo2')->nullable();
            $table->decimal('weight_kg',5,2)->nullable();
            $table->decimal('height_cm',5,2)->nullable();
            $table->foreignId('nurse_id')->nullable()
                  ->constrained('users')->nullOnDelete(); // users.id BIGINT
            $table->timestamps();

            $table->foreign('encounter_id')
                  ->references('id')->on('encounters')
                  ->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('vital_signs'); }
};
