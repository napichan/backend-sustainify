<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasRumahTangga;
use Illuminate\Support\Facades\Auth;

class AktivitasRumahTanggaController extends Controller
{
    public function index()
    {
        $data = AktivitasRumahTangga::where('user_id', Auth::id())->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $faktors = [
            'ac' => 0.4,
            'lampu' => 0.01,
            'tv' => 0.05,
            'kulkas' => 0.1,
        ];

        $emisi = $request->durasi_jam * ($faktors[$request->jenis_aktivitas] ?? 0);

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

    $faktors = [
        'ac' => 0.4,
        'lampu' => 0.01,
        'tv' => 0.05,
        'kulkas' => 0.1,
    ];

    $emisi = $request->durasi_jam * ($faktors[$request->jenis_aktivitas] ?? 0);

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