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
        Schema::create('pengajuan_persetujuans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengajuan_id')
                ->constrained('pengajuan_bantuans')
                ->cascadeOnDelete();

            $table->date('tanggal_persetujuan');

            $table->enum('status', [
                'disetujui',
                'ditolak',
                'tidak_dicairkan'
            ])->index();

            $table->unsignedBigInteger('nominal_disetujui')->default(0);

            // Referensi sumber dana (snapshot)
            // Contoh:
            // [
            //     {"dana":"Dana Sosial BRI","persentase":70},
            //     {"dana":"Dana Infak BMT","persentase":30}
            // ]
            $table->json('referensi_dana')->nullable();

            $table->text('catatan')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'pengajuan_id',
                'status'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_persetujuans');
    }
};
