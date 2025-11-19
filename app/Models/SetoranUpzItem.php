<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetoranUpzItem extends Model
{
    use HasFactory;

    protected $table = 'setoran_upz_items';

    protected $fillable = [
        'setoran_upz_id',
        'muzakki_id',
        'npwz',
        'nama',
        'nominal_zakat',
        'nominal_infak',
        'nominal_fitrah',
        'nominal_titipan',
        'jumlah',
        'keterangan',
        'periode',
    ];

    protected function casts(): array
    {
        return [
            'nominal_zakat' => 'integer',
            'nominal_infak' => 'integer',
            'nominal_fitrah' => 'integer',
            'nominal_titipan' => 'integer',
            'jumlah' => 'integer',
        ];
    }

    public function setoranUpz(): BelongsTo
    {
        return $this->belongsTo(SetoranUpz::class, 'setoran_upz_id');
    }

    public function muzakki(): BelongsTo
    {
        return $this->belongsTo(Muzakki::class, 'muzakki_id');
    }
}
