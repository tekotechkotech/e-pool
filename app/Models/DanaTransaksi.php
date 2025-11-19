<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DanaTransaksi extends Model
{
    protected $fillable = [
        'tanggal',
        'dana_id',
        'jenis',
        'nominal',
        'uraian',
        'referensi_type',
        'referensi_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'integer',
        ];
    }

    public function dana(): BelongsTo
    {
        return $this->belongsTo(Dana::class);
    }

    /**
     * Polymorphic: bisa dari Penyaluran atau model lain
     */
    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }
}
