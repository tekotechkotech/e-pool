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
        Schema::create('pengajuan_disposisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_mustahik_id')->constrained('pengajuan_mustahiks')->cascadeOnDelete();
            $table->foreignId('pimpinan_id')->constrained('users')->cascadeOnDelete();
            $table->enum('instruksi', ['Perlu Survey', 'Proses Langsung', 'Revisi', 'Tolak']);
            $table->string('diteruskan_kepada')->nullable();
            $table->json('isi_disposisi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_disposisis');
    }
};
