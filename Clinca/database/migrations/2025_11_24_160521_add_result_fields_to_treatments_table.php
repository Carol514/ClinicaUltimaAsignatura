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
        Schema::table('treatments', function (Blueprint $table) {
            $table->text('instructions')->nullable()->after('route');
            $table->string('result_type')->nullable()->after('instructions');
            $table->date('result_date')->nullable()->after('result_type');
            $table->text('notes')->nullable()->after('result_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropColumn(['instructions', 'result_type', 'result_date', 'notes']);
        });
    }
};
