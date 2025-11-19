<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'satuan',
        'stok',
        'harga_satuan_terakhir',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
            'harga_satuan_terakhir' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(BarangTransaksi::class)->orderBy('tanggal', 'desc')->orderBy('id', 'desc');
    }

    public function transaksiMasuk(): HasMany
    {
        return $this->hasMany(BarangTransaksi::class)->where('jenis', 'Masuk');
    }

    public function transaksiKeluar(): HasMany
    {
        return $this->hasMany(BarangTransaksi::class)->where('jenis', 'Keluar');
    }

    public function pengadaans(): HasMany
    {
        return $this->hasMany(PengadaanBarang::class)->orderBy('tanggal_ba', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('nama', 'asc');
    }
}
