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
        Schema::create('barang_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis', 20); // Masuk, Keluar
            $table->unsignedInteger('jumlah');
            $table->unsignedBigInteger('harga_satuan')->nullable();
            $table->unsignedBigInteger('harga_total')->nullable();
            $table->string('nomor_dokumen')->nullable();
            $table->text('uraian')->nullable();
            $table->nullableMorphs('referensi');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_transaksis');
    }
};
