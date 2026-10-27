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
        Schema::table('pengajuan_survey_asesmens', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_survey_asesmens', 'id_provinsi')) {
                $table->string('id_provinsi', 2)->nullable()->after('alamat_lengkap');
            }
            if (!Schema::hasColumn('pengajuan_survey_asesmens', 'id_kabupaten')) {
                $table->string('id_kabupaten', 5)->nullable()->after('id_provinsi');
            }
            if (!Schema::hasColumn('pengajuan_survey_asesmens', 'id_kecamatan')) {
                $table->string('id_kecamatan', 8)->nullable()->after('id_kabupaten');
            }
            if (!Schema::hasColumn('pengajuan_survey_asesmens', 'id_kelurahan')) {
                $table->string('id_kelurahan', 13)->nullable()->after('id_kecamatan');
            }
            if (!Schema::hasColumn('pengajuan_survey_asesmens', 'kode_pos')) {
                $table->string('kode_pos', 10)->nullable()->after('id_kelurahan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_survey_asesmens', function (Blueprint $table) {
            $cols = ['id_provinsi', 'id_kabupaten', 'id_kecamatan', 'id_kelurahan', 'kode_pos'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('pengajuan_survey_asesmens', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
