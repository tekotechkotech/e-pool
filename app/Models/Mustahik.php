<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mustahik extends Model
{
    protected $fillable = [
        'jenis_mustahik',
        'person_id',
        'lembaga_id',
        'pekerjaan',
        'nim',
        'keterangan',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function lembagas(): BelongsToMany
    {
        return $this->belongsToMany(Lembaga::class, 'lembaga_mustahiks')
                    ->withTimestamps();
    }

    public function upzs(): BelongsToMany
    {
        return $this->belongsToMany(Lembaga::class, 'lembaga_mustahiks')
                    ->withTimestamps();
    }
}
