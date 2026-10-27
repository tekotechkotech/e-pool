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
        Schema::create('lembagas', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_upz')->default(false);
            $table->string('nama');
            
            $table->string('ketua_nama')->nullable();
            $table->string('ketua_nik')->nullable();

            $table->text('jenis')->nullable();

            $table->integer('jumlah_anggota')->nullable();

            $table->text('keterangan')->nullable();

            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('handphone')->nullable();
            $table->string('email')->nullable();

            $table->string('id_provinsi', 2)->nullable();
            $table->string('id_kabupaten', 5)->nullable();
            $table->string('id_kecamatan', 8)->nullable();
            $table->string('id_kelurahan', 13)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembagas');
    }
};
