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
        Schema::create('dana_transaksis', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');

            $table->foreignId('dana_id')
                ->constrained()
                ->restrictOnDelete();

            $table->enum('jenis', [
                'Masuk',
                'Keluar',
            ])->index();

            $table->unsignedBigInteger('nominal')->default(0);

            $table->text('uraian')->nullable();

            $table->nullableMorphs('referensi');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dana_transaksis');
    }
};
