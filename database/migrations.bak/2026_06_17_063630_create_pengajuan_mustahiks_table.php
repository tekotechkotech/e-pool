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
        Schema::create('pengajuan_mustahiks', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('pengajuan_id')->constrained('pengajuan_bantuans')->cascadeOnDelete();
            $table->foreignId('mustahik_id')->constrained('mustahiks')->cascadeOnDelete();
            $table->foreignId('asnaf_id')->nullable()->constrained('asnafs')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs');
            $table->foreignId('subprogram_id')->nullable()->constrained('subprograms');
            $table->foreignId('sumber_dana_id')->nullable()->constrained('danas');
            $table->unsignedBigInteger('nominal')->nullable(); // Diisi saat disposisi
            $table->date('tanggal_penyaluran')->nullable();
            $table->enum('metode_penyaluran', ['Tunai', 'Transfer', 'Barang'])->nullable();
            $table->text('catatan_penyaluran')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_mustahiks');
    }
};
