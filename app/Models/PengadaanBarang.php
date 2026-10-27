<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PengadaanBarang extends Model
{
    protected $fillable = [
        'nomor_ba',
        'tanggal_ba',
        'pengajuan_id',
        'pengajuan_mustahik_id',
        'barang_id',
        'jumlah',
        'harga_satuan',
        'harga_total',
        'penyedia',
        'sumber_dana_id',
        'file_ba',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_ba'   => 'date',
            'jumlah'       => 'integer',
            'harga_satuan' => 'integer',
            'harga_total'  => 'integer',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanBantuan::class, 'pengajuan_id');
    }

    public function pengajuanMustahik(): BelongsTo
    {
        return $this->belongsTo(PengajuanMustahik::class, 'pengajuan_mustahik_id');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(Dana::class, 'sumber_dana_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengajuanMustahiks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PengajuanMustahik::class, 'pengadaan_barang_id');
    }

    public function transaksiMasuk(): MorphOne
    {
        return $this->morphOne(BarangTransaksi::class, 'referensi');
    }
}
