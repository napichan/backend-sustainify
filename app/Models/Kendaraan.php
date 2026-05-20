<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    use HasFactory;

    protected $table = 'kendaraan';

    protected $fillable = [
        'nama_kendaraan',
        'faktor_emisi'
    ];

    protected $appends = ['nama'];

    public function getNamaAttribute()
    {
        return $this->nama_kendaraan;
    }
}