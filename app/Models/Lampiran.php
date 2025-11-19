<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lampiran extends Model
{
    protected $fillable = [
        'name',
        'category',
        'file_path',
        'lampiranable_id',
        'lampiranable_type',
    ];

    /**
     * Get the parent lampiranable model (e.g., user, post, etc.).
     */
    public function lampiranable()
    {
        return $this->morphTo();
    }
}
