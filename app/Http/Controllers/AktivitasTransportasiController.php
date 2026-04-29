<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\AktivitasTransportasi;
use Illuminate\Http\Request;

class AktivitasTransportasiController extends Controller
{
    public function create()
    {
        $kendaraan = Kendaraan::all();
        return view('transportasi.create', compact('kendaraan'));
    }

    public function store(Request $request)
    {
        $kendaraan = Kendaraan::find($request->kendaraan_id);

        $emisi = $request->jarak_km * $kendaraan->faktor_emisi;

        AktivitasTransportasi::create([
            'user_id' => 1,
            'kendaraan_id' => $request->kendaraan_id,
            'jarak_km' => $request->jarak_km,
            'emisi_karbon' => $emisi,
            'tanggal' => $request->tanggal
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }
}