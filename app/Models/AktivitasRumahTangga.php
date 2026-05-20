<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasRumahTangga extends Model
{
    protected $table = 'aktivitas_rumah_tangga';

    protected $fillable = [
        'user_id',
        'jenis_aktivitas',
        'durasi_jam',
        'emisi_karbon',
        'tanggal',
    ];
}