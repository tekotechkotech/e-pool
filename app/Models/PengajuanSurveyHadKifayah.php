<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanSurveyHadKifayah extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_survey_had_kifayahs';

    protected $fillable = [
        'pengajuan_survey_id',
        'opz',
        'nama_petugas',
        'nama_mustahik',
        'alamat_mustahik',
        'propinsi',
        'pendapatan_keluarga',
        'pendapatan_anggotakel',
        'pengeluaran_kk',
        'pengeluaran_ibu',
        'pengeluaran_anakDewasaBekerja',
        'pengeluaran_anakDewasaTidakBekerja',
        'pengeluaran_sma',
        'pengeluaran_smp',
        'pengeluaran_sd',
        'pengeluaran_bayi',
        'pengeluaran_disabilitas',
        'pengeluaran_ibuMenyusui',
        'pengeluaran_ibuHamil',
        'pengeluaran_janda',
        'pengeluaran_pt',
        'tempat_rumah',
        'tempat_listrik',
        'pemerintah_bansos',
        'pemerintah_bpjs',
        'asset_motor',
        'asset_tabungan',
        'beban_hutang',
        'link_had_kifayah',
        'status_kelayakan',
        'kategori_prioritas',
        'pendapatan_txt',
        'pengeluaran_txt',
        'selisih_txt',
        'files_code',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurvey::class, 'pengajuan_survey_id');
    }
}
