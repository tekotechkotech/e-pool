<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Muzakki extends Model
{
    protected $fillable = [
        'person_id',
        'lembaga_id',
        'nrm',
        'npwz',
        'npwp',
        'nip',
        'pekerjaan',
        'alamat_kantor',
        'alamat_korespondensi',
        'jenis_zakat',
        'tanggal_registrasi',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_registrasi' => 'date',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function upz(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function setoranItems(): HasMany
    {
        return $this->hasMany(SetoranUpzItem::class, 'muzakki_id');
    }
}
