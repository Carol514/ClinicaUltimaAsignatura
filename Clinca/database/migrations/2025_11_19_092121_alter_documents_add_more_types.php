<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE documents 
            MODIFY COLUMN doc_type 
            ENUM('radiografia', 'analisis', 'otro', 'receta', 'referencia') 
            NOT NULL
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE documents 
            MODIFY COLUMN doc_type 
            ENUM('radiografia', 'analisis', 'otro') 
            NOT NULL
        ");
    }
};
