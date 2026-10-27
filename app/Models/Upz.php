<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Upz extends Lembaga
{
    protected $table = 'lembagas';

    protected static function booted(): void
    {
        static::addGlobalScope('upzOnly', function (Builder $builder) {
            $builder->where('is_upz', true);
        });

        static::creating(function ($model) {
            $model->is_upz = true;
        });
    }

    public function penguruses()
    {
        return $this->hasMany(Pengurus::class, 'lembaga_id');
    }

    public function mustahiks()
    {
        return $this->belongsToMany(Mustahik::class, 'lembaga_mustahiks', 'lembaga_id', 'mustahik_id')->withTimestamps();
    }

    public function setorans()
    {
        return $this->hasMany(SetoranUpz::class, 'lembaga_id');
    }
}
