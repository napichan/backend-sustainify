<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AktivitasTransportasi;
use App\Models\AktivitasRumahTangga;

class AdminController extends Controller
{
    // ── Get semua user ───────────────────────────────────────
    public function getUsers()
    {
        $users = User::all()->map(function ($user) {
            $emisiTransportasi = AktivitasTransportasi::where('user_id', $user->id)
                ->sum('emisi_karbon');
            $emisiRumahTangga = AktivitasRumahTangga::where('user_id', $user->id)
                ->sum('emisi_karbon');

            return [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'emisi' => round($emisiTransportasi + $emisiRumahTangga, 2),
            ];
        });

        return response()->json(['data' => $users]);
    }

    // ── Update user ──────────────────────────────────────────
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->update([
            'name'  => $request->name  ?? $user->name,
            'email' => $request->email ?? $user->email,
        ]);

        return response()->json([
            'message' => 'User berhasil diupdate!',
            'data'    => $user,
        ]);
    }

    // ── Hapus user ───────────────────────────────────────────
    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus']);
    }

    // ── Get semua aktivitas semua user ───────────────────────
    public function getAllAktivitas()
    {
        $transportasi = AktivitasTransportasi::with(['user', 'kendaraan'])->get()
            ->map(function ($item) {
                return [
                    'id'        => $item->id,
                    'nama'      => $item->user->name ?? '-',
                    'tanggal'   => $item->tanggal,
                    'aktivitas' => 'Transportasi',
                    'detail'    => $item->kendaraan->nama_kendaraan ?? '-',
                    'jumlah'    => $item->jarak_km . ' km',
                    'emisi'     => round($item->emisi_karbon, 2),
                ];
            });

        $rumahTangga = AktivitasRumahTangga::with('user')->get()
            ->map(function ($item) {
                $labelMap = [
                    'ac'         => 'Penggunaan AC',
                    'lampu'      => 'Lampu',
                    'tv'         => 'TV',
                    'kulkas'     => 'Kulkas',
                    'ricecooker' => 'Rice Cooker',
                    'kipas'      => 'Kipas Angin',
                ];
                return [
                    'id'        => 'RT' . $item->id,
                    'nama'      => $item->user->name ?? '-',
                    'tanggal'   => $item->tanggal,
                    'aktivitas' => 'Rumah Tangga',
                    'detail'    => $labelMap[$item->jenis_aktivitas] ?? $item->jenis_aktivitas,
                    'jumlah'    => $item->durasi_jam . ' jam',
                    'emisi'     => round($item->emisi_karbon, 2),
                ];
            });

        $combined = $transportasi->concat($rumahTangga)
            ->sortByDesc('tanggal')
            ->values();

        return response()->json(['data' => $combined]);
    }

    // ── Get statistik untuk dashboard admin ──────────────────
    public function getStats()
    {
        $totalUser   = User::count();
        $totalEmisiT = AktivitasTransportasi::sum('emisi_karbon');
        $totalEmisiR = AktivitasRumahTangga::sum('emisi_karbon');
        $totalEmisi  = round($totalEmisiT + $totalEmisiR, 2);

        $emisiPerBulan = AktivitasTransportasi::selectRaw('MONTH(tanggal) as bulan, SUM(emisi_karbon) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->map(fn($i) => ['bulan' => $i->bulan, 'emisi' => round($i->total, 2)]);

        $emisiRPerBulan = AktivitasRumahTangga::selectRaw('MONTH(tanggal) as bulan, SUM(emisi_karbon) as total')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $bulanMap = [];
        foreach ($emisiPerBulan as $item) {
            $bulanMap[$item['bulan']] = ($bulanMap[$item['bulan']] ?? 0) + $item['emisi'];
        }
        foreach ($emisiRPerBulan as $item) {
            $bulanMap[$item->bulan] = ($bulanMap[$item->bulan] ?? 0) + round($item->total, 2);
        }

        $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $chartData = collect($bulanMap)->map(fn($emisi, $bulan) => [
            'bulan' => $namaBulan[$bulan],
            'emisi' => round($emisi, 2),
        ])->values();

        return response()->json([
            'total_user'  => $totalUser,
            'total_emisi' => $totalEmisi,
            'chart_data'  => $chartData,
        ]);
    }
}