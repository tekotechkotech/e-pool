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
        Schema::create('pengajuan_bantuans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            
            $table->string('jenis_permohonan', 50)->index();

            $table->foreignId('lembaga_id')->nullable()->constrained('lembagas');
            $table->foreignId('pic_lembaga_id')->nullable()->constrained('penguruses');
            //untuk jenis_permohonan = UPZ/Lembaga

            $table->foreignId('mustahik_id')->nullable()->constrained('mustahiks'); 
            //untuk jenis_permohonan = Individu

            $table->string('mustahik_nama_snapshot')->nullable();
            $table->string('mustahik_nik_snapshot')->nullable();
            $table->string('mustahik_kontak_snapshot')->nullable();
            $table->string('mustahik_alamat_snapshot')->nullable();
            
            $table->string('lembaga_nama_snapshot')->nullable();
            $table->string('lembaga_kontak_snapshot')->nullable();
            $table->string('lembaga_alamat_snapshot')->nullable();

            //bisa menjadi pic upz atau mustahik, tergantung jenis_permohonan
            $table->string('pic_nama_snapshot')->nullable();
            $table->string('pic_kontak_snapshot')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->string('judul_surat')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->date('tanggal_surat')->nullable();
            $table->date('tanggal_pelaksanaan')->nullable();
            $table->date('tanggal_diterima')->index();


            // output penerima
            $table->integer('jumlah_orang')->default(0); 
            $table->integer('jumlah_kk')->default(0);
            $table->integer('jumlah_unit')->default(0); //bisa unit barang atau unit rumah tergantung pengajuan

            $table->boolean('is_rekomendasi')->default(false);

            $table->text('uraian_permohonan');

            $table->timestamp('ditolak_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_bantuans');
    }
};




