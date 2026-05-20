<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasTransportasi;
use App\Models\Kendaraan;
use Illuminate\Support\Facades\Auth;


class AktivitasTransportasiController extends Controller
{
    public function create()
    {
        $kendaraan = Kendaraan::all();

        return view('transportasi.create', compact('kendaraan'));
    }

    public function index(Request $request)
    {
        $user_id = Auth::id();

        $data = AktivitasTransportasi::with('kendaraan')
            ->where('user_id', $user_id)
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $kendaraan = Kendaraan::find($request->kendaraan_id);

        if (!$kendaraan) {
            return response()->json([
                'message' => 'Kendaraan tidak ditemukan'
            ], 404);
        }

        $emisi = $request->jarak_km * $kendaraan->faktor_emisi;

        $aktivitas = AktivitasTransportasi::create([
            'user_id' => Auth::id(),
            'kendaraan_id' => $request->kendaraan_id,
            'jarak_km' => $request->jarak_km,
            'emisi_karbon' => $emisi,
            'tanggal' => now()
        ]);

        return response()->json([
            'message' => 'Data berhasil disimpan!',
            'data' => $aktivitas->load('kendaraan')
        ], 201);
    }

    public function edit($id)
    {
        $data = AktivitasTransportasi::with('kendaraan')->where('user_id', Auth::id())->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $kendaraan = Kendaraan::all();

        return response()->json([
            'data' => $data,
            'kendaraan' => $kendaraan
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = AktivitasTransportasi::where('user_id', Auth::id())->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $kendaraan = Kendaraan::find($request->kendaraan_id);

        if (!$kendaraan) {
            return response()->json([
                'message' => 'Kendaraan tidak ditemukan'
            ], 404);
        }

        $emisi = $request->jarak_km * $kendaraan->faktor_emisi;

        $data->update([
            'kendaraan_id' => $request->kendaraan_id,
            'jarak_km' => $request->jarak_km,
            'emisi_karbon' => $emisi,
            'tanggal' => $request->tanggal ?? now()
        ]);

        return response()->json([
            'message' => 'Data berhasil diupdate!',
            'data' => $data->load('kendaraan')
        ]);
    }

    public function destroy($id)
    {
        $data = AktivitasTransportasi::where('user_id', Auth::id())->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus!'
        ]);
    }
}