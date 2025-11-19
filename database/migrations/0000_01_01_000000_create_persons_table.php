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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();

            $table->string('nik')->unique()->nullable();
            $table->string('nomor_kk')->nullable()->index();

            $table->string('nama')->index();

            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();

            $table->string('telepon')->nullable();
            $table->string('handphone')->nullable();
            $table->string('email')->nullable();

            $table->text('alamat')->nullable();
            $table->string('id_provinsi', 2)->nullable();
            $table->string('id_kabupaten', 5)->nullable();
            $table->string('id_kecamatan', 8)->nullable();
            $table->string('id_kelurahan', 13)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
