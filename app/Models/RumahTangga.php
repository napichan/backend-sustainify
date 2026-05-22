<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RumahTangga extends Model
{
    protected $table = 'rumah_tangga';

    protected $fillable = [
        'nama_aktivitas',
        'faktor_emisi',
    ];
}