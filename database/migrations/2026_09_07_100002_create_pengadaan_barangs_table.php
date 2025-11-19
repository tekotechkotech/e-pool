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
        Schema::create('pengadaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_ba')->index();
            $table->date('tanggal_ba');
            
            // Add guards for tables that might be archived
            if (Schema::hasTable('pengajuan_bantuans')) {
                $table->foreignId('pengajuan_id')->nullable()->constrained('pengajuan_bantuans')->nullOnDelete();
            }
            
            if (Schema::hasTable('pengajuan_mustahiks')) {
                $table->foreignId('pengajuan_mustahik_id')->nullable()->constrained('pengajuan_mustahiks')->nullOnDelete();
            }
            
            if (Schema::hasTable('barangs')) {
                $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();
            }
            
            if (Schema::hasTable('danas')) {
                $table->foreignId('sumber_dana_id')->nullable()->constrained('danas')->nullOnDelete();
            }
            
            if (Schema::hasTable('users')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            
            $table->unsignedInteger('jumlah');
            $table->unsignedBigInteger('harga_satuan');
            $table->unsignedBigInteger('harga_total');
            $table->string('penyedia')->nullable();
            $table->string('file_ba')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengadaan_barangs');
    }
};
