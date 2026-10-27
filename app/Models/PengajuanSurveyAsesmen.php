<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanSurveyAsesmen extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_survey_asesmens';

    protected $fillable = [
        'pengajuan_survey_id',

        // Bagian 1
        'nama_surveyor',
        'jenis_program',
        'sub_program',
        'nama_mustahik',
        'nkk',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat_lengkap',
        'id_provinsi',
        'id_kabupaten',
        'id_kecamatan',
        'id_kelurahan',
        'kode_pos',
        'desa',
        'kecamatan',
        'nohp',
        'bersedia_disurvei',

        // Bagian 2
        'agama',
        'jumlah_tanggungan',
        'jumlah_sepeda',
        'jumlah_sepeda_motor',
        'jumlah_mobil',
        'total_penghasilan_perbulan',

        // Bagian 3
        'status_tanah',
        'status_rumah',
        'bukti_dokumen',
        'kondisi_tempat_tinggal',
        'foto_tempat_tinggal',
        'kondisi_atap',
        'foto_atap',
        'kondisi_lantai',
        'foto_lantai',
        'kondisi_dinding',
        'foto_dinding',
        'kondisi_dapur',
        'foto_dapur',
        'kondisi_kamar_mandi',
        'foto_kamar_mandi',
        'sumber_air_bersih',
        'daya_listrik',

        // Bagian 4
        'punya_usaha',
        'foto_usaha',
        'lama_usaha',
        'hasil_akhir_permohonan',
        'bpjs_kesehatan',
        'status_bpjs_kesehatan',
        'riwayat_penyakit',

        // Bagian 5
        'skor_penilaian',
        'hasil_survei',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function getAlamatFormattedAttribute(): string
    {
        $parts = [];
        if ($this->alamat_lengkap) $parts[] = $this->alamat_lengkap;
        $desa = $this->kelurahan?->nama ?: $this->desa;
        if ($desa) $parts[] = 'Desa/Kel. ' . $desa;
        $kec = $this->kecamatanRelation?->nama ?: $this->kecamatan;
        if ($kec) $parts[] = 'Kec. ' . $kec;
        if ($this->kabupaten) $parts[] = $this->kabupaten->nama;
        if ($this->provinsi) $parts[] = 'Prov. ' . $this->provinsi->nama;
        if ($this->kode_pos) $parts[] = $this->kode_pos;
        
        return count($parts) > 0 ? implode(', ', $parts) : ($this->alamat_lengkap ?? '-');
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurvey::class, 'pengajuan_survey_id');
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'id_provinsi', 'id_wilayah');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'id_kabupaten', 'id_wilayah');
    }

    public function kecamatanRelation(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'id_kecamatan', 'id_wilayah');
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'id_kelurahan', 'id_wilayah');
    }
}
