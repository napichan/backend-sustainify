<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AktivitasRumahTangga;
use App\Models\RumahTangga;
use Illuminate\Support\Facades\Auth;

class AktivitasRumahTanggaController extends Controller
{
    public function index()
    {
        $data = AktivitasRumahTangga::with('rumahTangga')
                    ->where('user_id', Auth::id())->get();
        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'aktivitas_id' => 'required|exists:rumah_tangga,id',
            'durasi_jam'   => 'required|numeric|min:0.1|max:24',
        ]);

        $rumahTangga = RumahTangga::findOrFail($request->aktivitas_id);
        $emisi = $request->durasi_jam * $rumahTangga->faktor_emisi;

        $aktivitas = AktivitasRumahTangga::create([
            'user_id'      => Auth::id(),
            'aktivitas_id' => $request->aktivitas_id,
            'durasi_jam'   => $request->durasi_jam,
            'emisi_karbon' => $emisi,
            'tanggal'      => now(),
        ]);

        return response()->json(['message' => 'Data berhasil disimpan!', 'data' => $aktivitas], 201);
    }

    public function update(Request $request, $id)
    {
        $data = AktivitasRumahTangga::where('user_id', Auth::id())->find($id);
        if (!$data) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $request->validate([
            'aktivitas_id' => 'required|exists:rumah_tangga,id',
            'durasi_jam'   => 'required|numeric|min:0.1|max:24',
        ]);

        $rumahTangga = RumahTangga::findOrFail($request->aktivitas_id);
        $emisi = $request->durasi_jam * $rumahTangga->faktor_emisi;

        $data->update([
            'aktivitas_id' => $request->aktivitas_id,
            'durasi_jam'   => $request->durasi_jam,
            'emisi_karbon' => $emisi,
            'tanggal'      => $request->tanggal ?? now(),
        ]);

        return response()->json(['message' => 'Data berhasil diupdate!', 'data' => $data]);
    }

    public function destroy($id)
    {
        $data = AktivitasRumahTangga::where('user_id', Auth::id())->find($id);
        if (!$data) return response()->json(['message' => 'Data tidak ditemukan'], 404);
        $data->delete();
        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}