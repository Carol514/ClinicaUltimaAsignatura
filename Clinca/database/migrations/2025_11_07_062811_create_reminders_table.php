<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('reminders', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('appointment_id');                 // -> appointments.id
      $table->enum('channel',['sms','email','push']);
      $table->timestamp('send_at');
      $table->boolean('sent')->default(false);
      $table->string('result')->nullable();
      $table->timestamps();

      $table->foreign('appointment_id')->references('id')->on('appointments')->cascadeOnDelete();
    });
  }
  public function down(): void { Schema::dropIfExists('reminders'); }
};
