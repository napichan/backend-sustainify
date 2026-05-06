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
            
            'kendaraan_id' => $request->kendaraan_id,
            'jarak_km' => $request->jarak_km,
            'emisi_karbon' => $emisi,
            'tanggal' => now()
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan!');
    }

    
    public function index()
    {
        $data = AktivitasTransportasi::with('kendaraan')->get();
        return view('transportasi.index', compact('data'));
    }

    public function edit($id)
    {
        $data = AktivitasTransportasi::find($id);
        $kendaraan = Kendaraan::all();

        return view('transportasi.edit', compact('data', 'kendaraan'));
    }


    public function update(Request $request, $id)
    {
        $data = AktivitasTransportasi::find($id);

        $kendaraan = Kendaraan::find($request->kendaraan_id);
        $emisi = $request->jarak_km * $kendaraan->faktor_emisi;

        $data->update([
            'kendaraan_id' => $request->kendaraan_id,
            'jarak_km' => $request->jarak_km,
            'emisi_karbon' => $emisi,
            'tanggal' => $request->tanggal
        ]);

        return redirect('/transportasi/riwayat')->with('success', 'Data berhasil diupdate!');
    }

  
    public function destroy($id)
    {
        $data = AktivitasTransportasi::find($id);
        $data->delete();

        return redirect('/transportasi/riwayat')->with('success', 'Data berhasil dihapus!');
    }
}