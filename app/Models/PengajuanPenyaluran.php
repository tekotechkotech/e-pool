<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PengajuanPenyaluran extends Model
{
    protected $table = 'pengajuan_penyalurans';

    protected $fillable = [
        'pengajuan_id',
        'tanggal_penyaluran',
        'nominal_penyaluran',
        'metode_penyaluran',
        'bukti_penyaluran',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penyaluran' => 'date',
            'nominal_penyaluran' => 'integer',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanBantuan::class, 'pengajuan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function danaTransaksis(): MorphMany
    {
        return $this->morphMany(DanaTransaksi::class, 'referensi');
    }

    public function buktiPenyaluran(): MorphMany
    {
        return $this->morphMany(Lampiran::class, 'lampiranable')->where('category', 'bukti_penyaluran');
    }
}
