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
        Schema::create('pengajuan_survey_had_kifayahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_survey_id')->constrained('pengajuan_surveys')->cascadeOnDelete();

            $table->string('opz')->nullable();
            $table->string('nama_petugas')->nullable();
            $table->string('nama_mustahik')->nullable();
            $table->text('alamat_mustahik')->nullable();
            $table->string('propinsi')->nullable();

            $table->string('pendapatan_keluarga')->nullable();
            $table->string('pendapatan_anggotakel')->nullable();
            $table->string('pengeluaran_kk')->nullable();
            $table->string('pengeluaran_ibu')->nullable();
            $table->string('pengeluaran_anakDewasaBekerja')->nullable();
            $table->string('pengeluaran_anakDewasaTidakBekerja')->nullable();
            $table->string('pengeluaran_sma')->nullable();
            $table->string('pengeluaran_smp')->nullable();
            $table->string('pengeluaran_sd')->nullable();
            $table->string('pengeluaran_bayi')->nullable();
            $table->string('pengeluaran_disabilitas')->nullable();
            $table->string('pengeluaran_ibuMenyusui')->nullable();
            $table->string('pengeluaran_ibuHamil')->nullable();
            $table->string('pengeluaran_janda')->nullable();
            $table->string('pengeluaran_pt')->nullable();
            $table->string('tempat_rumah')->nullable();
            $table->string('tempat_listrik')->nullable();
            $table->string('pemerintah_bansos')->nullable();
            $table->string('pemerintah_bpjs')->nullable();
            $table->string('asset_motor')->nullable();
            $table->string('asset_tabungan')->nullable();
            $table->string('beban_hutang')->nullable();

            // Hasil dari API BAZNAS
            $table->string('link_had_kifayah')->nullable();
            $table->string('status_kelayakan')->nullable();
            $table->string('kategori_prioritas')->nullable();
            $table->string('pendapatan_txt')->nullable();
            $table->string('pengeluaran_txt')->nullable();
            $table->string('selisih_txt')->nullable();
            $table->string('files_code')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_survey_had_kifayahs');
    }
};
