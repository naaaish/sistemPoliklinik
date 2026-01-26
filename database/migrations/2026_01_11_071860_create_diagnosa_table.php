<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnosa', function (Blueprint $table) {
            $table->string('id_diagnosa', 20)->primary();
            $table->text('diagnosa');
            $table->boolean('is_active')->default(true);
        
            $table->string('id_nb', 10)->nullable(); 
            $table->index('id_nb');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosa');
    }
};