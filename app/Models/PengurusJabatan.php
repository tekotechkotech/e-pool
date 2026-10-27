<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengurusJabatan extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'tingkat',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function penguruses()
    {
        return $this->hasMany(Pengurus::class);
    }
}
