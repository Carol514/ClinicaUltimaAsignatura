<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('treatments', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('encounter_id')->nullable();              // -> encounters.id (uuid)
      $table->uuid('record_id');                              // -> medical_records.id (uuid)
      $table->string('name');
      $table->string('dose')->nullable();
      $table->string('route')->nullable();
      $table->timestamp('start_dt')->nullable();
      $table->timestamp('end_dt')->nullable();
      $table->enum('status',['activo','suspendido','completado'])->default('activo');
      $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); // users.id BIGINT
      $table->timestamps();

      $table->foreign('encounter_id')->references('id')->on('encounters')->nullOnDelete();
      $table->foreign('record_id')->references('id')->on('medical_records')->cascadeOnDelete();
    });
  }
  public function down(): void { Schema::dropIfExists('treatments'); }
};
