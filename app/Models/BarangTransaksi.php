<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BarangTransaksi extends Model
{
    protected $fillable = [
        'barang_id',
        'tanggal',
        'jenis',
        'jumlah',
        'harga_satuan',
        'harga_total',
        'nomor_dokumen',
        'uraian',
        'referensi_type',
        'referensi_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'      => 'date',
            'jumlah'       => 'integer',
            'harga_satuan' => 'integer',
            'harga_total'  => 'integer',
        ];
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
