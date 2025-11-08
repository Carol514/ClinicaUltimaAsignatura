<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('appointments', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('patient_id');                    // -> patients.id (uuid)
      $table->timestamp('scheduled_at');
      $table->integer('duration_min')->default(30);
      $table->string('reason')->nullable();
      $table->enum('status',['programada','confirmada','no_asistio','cancelada','atendida'])->default('programada');
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->foreignId('clinician_id')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();

      $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
    });
  }
  public function down(): void { Schema::dropIfExists('appointments'); }
};
