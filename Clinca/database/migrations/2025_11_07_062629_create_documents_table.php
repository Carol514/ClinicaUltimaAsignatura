<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('documents', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('record_id');                         // -> medical_records.id
      $table->uuid('encounter_id')->nullable();          // -> encounters.id
      $table->enum('doc_type',['radiografia','analisis','otro']);
      $table->string('title');
      $table->text('storage_uri');                       // ruta/URL del archivo
      $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();

      $table->foreign('record_id')->references('id')->on('medical_records')->cascadeOnDelete();
      $table->foreign('encounter_id')->references('id')->on('encounters')->nullOnDelete();
    });
  }
  public function down(): void { Schema::dropIfExists('documents'); }
};
