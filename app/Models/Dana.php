<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dana extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'via_upz',
        'urutan',
        'keterangan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'via_upz'   => 'boolean',
            'urutan'    => 'integer',
        ];
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(DanaTransaksi::class);
    }

    public function transaksiMasuk(): HasMany
    {
        return $this->hasMany(DanaTransaksi::class)->where('jenis', 'Masuk');
    }

    public function transaksiKeluar(): HasMany
    {
        return $this->hasMany(DanaTransaksi::class)->where('jenis', 'Keluar');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('kode', 'asc');
    }
}
