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
        Schema::create('pengajuan_penyalurans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_id')->constrained('pengajuan_bantuans')->cascadeOnDelete();

            $table->date('tanggal_penyaluran');

            $table->unsignedBigInteger('nominal_penyaluran')->default(0);

            $table->enum('metode_penyaluran', ['Tunai', 'Transfer', 'Barang'])->default('Tunai');

            $table->text('catatan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_penyalurans');
    }
};
