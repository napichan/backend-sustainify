<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasRumahTangga;
use App\Models\RumahTangga;
use Illuminate\Support\Facades\Auth;

class AktivitasRumahTanggaController extends Controller
{
    // Ambil faktor emisi dari database
    private function getFaktorEmisi($jenis_aktivitas)
    {
        $rumahTangga = RumahTangga::where('nama_aktivitas', $jenis_aktivitas)->first();
        return $rumahTangga ? $rumahTangga->faktor_emisi : 0;
    }

    public function index()
    {
        $data = AktivitasRumahTangga::where('user_id', Auth::id())->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $emisi = $request->durasi_jam * $this->getFaktorEmisi($request->jenis_aktivitas);

        $aktivitas = AktivitasRumahTangga::create([
            'user_id' => Auth::id(),
            'jenis_aktivitas' => $request->jenis_aktivitas,
            'durasi_jam' => $request->durasi_jam,
            'emisi_karbon' => $emisi,
            'tanggal' => now(),
        ]);

        return response()->json([
            'message' => 'Data berhasil disimpan!',
            'data' => $aktivitas
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = AktivitasRumahTangga::where('user_id', Auth::id())->find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $emisi = $request->durasi_jam * $this->getFaktorEmisi($request->jenis_aktivitas);

        $data->update([
            'jenis_aktivitas' => $request->jenis_aktivitas,
            'durasi_jam' => $request->durasi_jam,
            'emisi_karbon' => $emisi,
            'tanggal' => $request->tanggal ?? now(),
        ]);

        return response()->json([
            'message' => 'Data berhasil diupdate!',
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = AktivitasRumahTangga::where('user_id', Auth::id())->find($id);

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