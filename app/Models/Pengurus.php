<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengurus extends Model
{
    protected $table = 'penguruses';

    protected $fillable = [
        'jenis_pengurus',
        'lembaga_id',
        'person_id',
        'pengurus_jabatan_id',
        'no_sk',
        'file_sk',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function upz(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class, 'lembaga_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function pengurusJabatan(): BelongsTo
    {
        return $this->belongsTo(PengurusJabatan::class);
    }
}
