<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SetoranUpz extends Model
{
    use HasFactory;

    protected $table = 'setoran_upzs';

    protected $fillable = [
        'nomor_setoran',
        'lembaga_id',
        'tanggal_setor',
        'periode',
        'total_zakat',
        'total_infak',
        'total_fitrah',
        'total_titipan',
        'total_jumlah',
        'keterangan',
        'bukti_setor',
        'status',
        'user_id',
        'verified_by',
        'verified_at',
        'catatan_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_setor' => 'date',
            'verified_at' => 'datetime',
            'total_zakat' => 'integer',
            'total_infak' => 'integer',
            'total_fitrah' => 'integer',
            'total_titipan' => 'integer',
            'total_jumlah' => 'integer',
        ];
    }

    public function upz(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SetoranUpzItem::class, 'setoran_upz_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
