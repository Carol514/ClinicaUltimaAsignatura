<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('allergies', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('record_id');                         // -> medical_records.id
      $table->string('allergen');
      $table->string('reaction')->nullable();
      $table->enum('severity',['leve','moderada','severa'])->default('leve');
      $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamp('recorded_at')->useCurrent();
      $table->timestamps();

      $table->foreign('record_id')->references('id')->on('medical_records')->cascadeOnDelete();
    });
  }
  public function down(): void { Schema::dropIfExists('allergies'); }
};
