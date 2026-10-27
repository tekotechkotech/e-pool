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
        Schema::create('danas', function (Blueprint $table) {
            $table->id();

            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('kategori')->index(); // Zakat, Infak/Sedekah, CSR, DSKL, Dana Titipan
            $table->boolean('via_upz')->default(false);
            $table->integer('urutan')->default(0);
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danas');
    }
};
