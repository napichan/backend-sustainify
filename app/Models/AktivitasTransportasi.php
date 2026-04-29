<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasTransportasi extends Model
{
    protected $fillable = [
    'user_id',
    'kendaraan_id',
    'jarak_km',
    'emisi_karbon',
    'tanggal'
];
}
