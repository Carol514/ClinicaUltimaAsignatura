<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('encounters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('record_id');                 // FK a medical_records.uuid
            $table->timestamp('encounter_dt')->useCurrent();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('clinician_id')->nullable()
                  ->constrained('users')->nullOnDelete(); // users.id BIGINT
            $table->timestamps();

            $table->foreign('record_id')
                  ->references('id')->on('medical_records')
                  ->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('encounters'); }
};
