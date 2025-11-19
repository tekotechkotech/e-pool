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
        Schema::create('pengajuan_lpj_bas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_id')->unique()->constrained('pengajuan_bantuans')->cascadeOnDelete();

            $table->date('tanggal_lpj');

            $table->unsignedBigInteger('nominal_realisasi')->nullable();
            $table->unsignedBigInteger('nominal_sisa')->nullable();
            $table->boolean('sisa_dikembalikan')->default(false);

            $table->text('catatan')->nullable();
            $table->string('file_lpj')->nullable();
            $table->string('file_ba')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_lpj_bas');
    }
};
