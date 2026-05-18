<?php

namespace App\Http\Controllers;

use App\Models\AktivitasTransportasi;
use App\Models\Kendaraan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AktivitasTransportasiController extends Controller
{
    public function create()
    {
        $kendaraan =  Kendaraan::all();
        return view('transportasi.create', compact('kendaraan'));
    }

    public function store(Request $request)
    {
        $kendaraan = Kendaraan::find($request->kendaraan_id, 'id');

        $emisi = $request->jarak_km * $kendaraan->faktor_emisi;

        $aktivitas = AktivitasTransportasi::create([
            'user_id' => $request->user() ? $request->user()->id : 1, // Fallback to 1 if not logged in for testing, though auth middleware will protect it
            'kendaraan_id' => $request->kendaraan_id,
            'jarak_km' => $request->jarak_km,
            'emisi_karbon' => $emisi,
            'tanggal' => now()
        ]);

        return response()->json([
            'message' => 'Data berhasil disimpan!',
            'data' => $aktivitas
        ], 201);
    }

    
    public function index(Request $request)
    {
        $user_id = $request->user() ? $request->user()->id : 1;
        $data = AktivitasTransportasi::with('kendaraan')->where('user_id', $user_id)->get();
        return response()->json([
            'data'=> $data
        ]);
    }

    public function edit($id)
    {
        $data = AktivitasTransportasi::find($id);
        $kendaraan = Kendaraan::all();

        return response()->json(['data' => $data, 'kendaraan' => $kendaraan]);
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
            'tanggal' => $request->tanggal ?? now()
        ]);

        return response()->json([
            'message' => 'Data berhasil diupdate!',
            'data' => $data
        ]);
    }

  
    public function destroy($id)
    {
        $data = AktivitasTransportasi::find($id);
        $data->delete();

        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}