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
        Schema::create('detail_resep', function (Blueprint $table) {
            $table->id();
            $table->integer('jumlah');
            $table->string('satuan');
            $table->decimal('subtotal',12,2);
            $table->string('id_obat',20);
            $table->string('id_resep',20);

            $table->foreign('id_obat')->references('id_obat')->on('obat');
            $table->foreign('id_resep')->references('id_resep')->on('resep');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_resep');
    }
};
