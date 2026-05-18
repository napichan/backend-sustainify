<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasTransportasi extends Model
{
protected $table = 'aktivitas_transportasi';

    protected $fillable = [
        'user_id',
        'kendaraan_id',
        'jarak_km',
        'emisi_karbon',
        'tanggal'
    ];

    public function kendaraan() {
        return $this->hasOne(AktivitasTransportasi::class, 'kendaraan_id');
    }

}
