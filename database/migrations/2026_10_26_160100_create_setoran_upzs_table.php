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
        Schema::create('setoran_upzs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_setoran')->unique();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->date('tanggal_setor');
            $table->string('periode');
            $table->unsignedBigInteger('total_zakat')->default(0);
            $table->unsignedBigInteger('total_infak')->default(0);
            $table->unsignedBigInteger('total_fitrah')->default(0);
            $table->unsignedBigInteger('total_titipan')->default(0);
            $table->unsignedBigInteger('total_jumlah')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('bukti_setor')->nullable();
            $table->string('status')->default('Terverifikasi');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setoran_upzs');
    }
};
