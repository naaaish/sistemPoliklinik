<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diagnosa_k3', function (Blueprint $table) {
            $table->string('id_nb', 10)->primary();

            // tambahan untuk struktur bertingkat
            $table->enum('tipe', ['kategori','penyakit'])->default('penyakit');
            $table->string('parent_id', 10)->nullable();

            $table->text('nama_penyakit');
            $table->string('kategori_penyakit');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tipe']);
            $table->index(['parent_id']);
        });
    }

  public function down(): void
  {
    Schema::table('diagnosa_k3', function (Blueprint $table) {
      $table->dropForeign(['parent_id']);
      $table->dropIndex(['tipe']);
      $table->dropIndex(['parent_id']);
      $table->dropColumn(['tipe','parent_id']);
    });
  }
};
