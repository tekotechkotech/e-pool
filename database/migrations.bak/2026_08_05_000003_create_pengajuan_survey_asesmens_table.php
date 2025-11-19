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
        Schema::create('pengajuan_survey_asesmens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_survey_id')->constrained('pengajuan_surveys')->cascadeOnDelete();

            // Bagian 1: Data Awal & Identitas
            $table->string('nama_surveyor')->nullable();
            $table->string('jenis_program')->nullable();
            $table->string('sub_program')->nullable();
            $table->string('nama_mustahik')->nullable();
            $table->string('nkk', 16)->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 50)->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('desa')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('nohp')->nullable();
            $table->string('bersedia_disurvei', 50)->nullable()->default('YA');

            // Bagian 2: Keluarga & Ekonomi
            $table->string('agama')->nullable();
            $table->integer('jumlah_tanggungan')->nullable();
            $table->string('jumlah_sepeda')->nullable();
            $table->string('jumlah_sepeda_motor')->nullable();
            $table->string('jumlah_mobil')->nullable();
            $table->string('total_penghasilan_perbulan')->nullable();

            // Bagian 3: Aset & Kondisi Rumah
            $table->string('status_tanah')->nullable();
            $table->string('status_rumah')->nullable();
            $table->string('bukti_dokumen', 50)->nullable();
            $table->string('kondisi_tempat_tinggal')->nullable();
            $table->string('foto_tempat_tinggal')->nullable();
            $table->string('kondisi_atap')->nullable();
            $table->string('foto_atap')->nullable();
            $table->string('kondisi_lantai')->nullable();
            $table->string('foto_lantai')->nullable();
            $table->string('kondisi_dinding')->nullable();
            $table->string('foto_dinding')->nullable();
            $table->string('kondisi_dapur')->nullable();
            $table->string('foto_dapur')->nullable();
            $table->string('kondisi_kamar_mandi')->nullable();
            $table->string('foto_kamar_mandi')->nullable();
            $table->string('sumber_air_bersih')->nullable();
            $table->string('daya_listrik')->nullable();

            // Bagian 4: Usaha, Kesehatan & Hasil
            $table->string('punya_usaha', 50)->nullable();
            $table->string('foto_usaha')->nullable();
            $table->string('lama_usaha')->nullable();
            $table->string('hasil_akhir_permohonan')->nullable();
            $table->string('bpjs_kesehatan', 50)->nullable();
            $table->string('status_bpjs_kesehatan', 50)->nullable();
            $table->text('riwayat_penyakit')->nullable();

            // Bagian 5: Penilaian (Khusus Surveyor)
            $table->integer('skor_penilaian')->nullable(); // 1 - 5
            $table->string('hasil_survei', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_survey_asesmens');
    }
};
