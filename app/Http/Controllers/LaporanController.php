<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function laporanTransportasi(Request $request)
    {
        $userId = Auth::id();

        $rataRata = round(
            DB::table('aktivitas_transportasi')->where('user_id', $userId)->avg('emisi_karbon') ?? 0,
            4
        );

        $data = DB::table('aktivitas_transportasi as at')
            ->join('kendaraan as k', 'at.kendaraan_id', '=', 'k.id')
            ->select([
                'k.nama_kendaraan',
                DB::raw('COUNT(at.id) as jumlah_trip'),
                DB::raw('SUM(at.jarak_km) as total_jarak'),
                DB::raw('ROUND(SUM(at.emisi_karbon), 2) as total_emisi'),
                DB::raw('ROUND(AVG(at.emisi_karbon), 2) as rata_emisi'),
                DB::raw('ROUND(MAX(at.emisi_karbon), 2) as maks_emisi'),
            ])
            ->where('at.user_id', $userId)
            ->groupBy('k.id', 'k.nama_kendaraan')
            ->havingRaw('AVG(at.emisi_karbon) >= ?', [$rataRata])
            ->orderByDesc('total_emisi')
            ->get();

        $totalEmisi = round(
            DB::table('aktivitas_transportasi')->where('user_id', $userId)->sum('emisi_karbon') ?? 0,
            2
        );

        return response()->json([
            'success'                  => true,
            'rata_rata_keseluruhan'    => $rataRata,
            'total_emisi_transportasi' => $totalEmisi,
            'data'                     => $data,
            'keterangan'               => 'Hanya menampilkan kendaraan dengan rata-rata emisi ≥ rata-rata keseluruhan',
        ]);
    }

    public function laporanRumahTangga(Request $request)
    {
        $userId = Auth::id();

        $totalEmisi = round(
            DB::table('aktivitas_rumah_tangga')->where('user_id', $userId)->sum('emisi_karbon') ?? 0,
            2
        );

        $rataRata = round(
            DB::table('aktivitas_rumah_tangga')->where('user_id', $userId)->avg('emisi_karbon') ?? 0,
            4
        );

        // ✅ FIX: JOIN via aktivitas_id = rt.id (bukan via string jenis_aktivitas)
        $data = DB::table('aktivitas_rumah_tangga as art')
            ->join('rumah_tangga as rt', 'art.aktivitas_id', '=', 'rt.id')
            ->select([
                'rt.nama_aktivitas',
                DB::raw('COUNT(art.id) as frekuensi'),
                DB::raw('ROUND(SUM(art.durasi_jam), 1) as total_durasi'),
                DB::raw('ROUND(SUM(art.emisi_karbon), 2) as total_emisi'),
                DB::raw('ROUND(AVG(art.emisi_karbon), 2) as rata_emisi'),
                DB::raw('ROUND(MAX(art.emisi_karbon), 2) as maks_emisi'),
                DB::raw('ROUND(rt.faktor_emisi, 3) as faktor_emisi'),
                DB::raw(
                    $totalEmisi > 0
                        ? "ROUND((SUM(art.emisi_karbon) / {$totalEmisi}) * 100, 1) as persen_kontribusi"
                        : "0 as persen_kontribusi"
                ),
            ])
            ->where('art.user_id', $userId)
            ->groupBy('rt.id', 'rt.nama_aktivitas', 'rt.faktor_emisi')
            ->havingRaw('COUNT(art.id) >= 2')
            ->orderByDesc('total_emisi')
            ->get();

        return response()->json([
            'success'                  => true,
            'rata_rata_keseluruhan'    => $rataRata,
            'total_emisi_rumah_tangga' => $totalEmisi,
            'data'                     => $data,
            'keterangan'               => 'Hanya menampilkan aktivitas yang dilakukan minimal 2 kali',
        ]);
    }

    public function laporanRingkasan(Request $request)
    {
        $userId = Auth::id();

        // ✅ FIX: JOIN via aktivitas_id = rt.id
        $transportasi = DB::table('aktivitas_transportasi as at')
            ->join('kendaraan as k', 'at.kendaraan_id', '=', 'k.id')
            ->select([
                DB::raw('"Transportasi" as kategori'),
                DB::raw('COUNT(at.id) as total_aktivitas'),
                DB::raw('ROUND(SUM(at.emisi_karbon), 2) as total_emisi'),
                DB::raw('ROUND(AVG(at.emisi_karbon), 2) as rata_emisi'),
                DB::raw('ROUND(MAX(at.emisi_karbon), 2) as maks_emisi'),
                DB::raw('ROUND(MIN(at.emisi_karbon), 2) as min_emisi'),
            ])
            ->where('at.user_id', $userId)
            ->groupBy(DB::raw('"Transportasi"'));

        $ringkasan = DB::table('aktivitas_rumah_tangga as art')
            ->join('rumah_tangga as rt', 'art.aktivitas_id', '=', 'rt.id')
            ->select([
                DB::raw('"Rumah Tangga" as kategori'),
                DB::raw('COUNT(art.id) as total_aktivitas'),
                DB::raw('ROUND(SUM(art.emisi_karbon), 2) as total_emisi'),
                DB::raw('ROUND(AVG(art.emisi_karbon), 2) as rata_emisi'),
                DB::raw('ROUND(MAX(art.emisi_karbon), 2) as maks_emisi'),
                DB::raw('ROUND(MIN(art.emisi_karbon), 2) as min_emisi'),
            ])
            ->where('art.user_id', $userId)
            ->groupBy(DB::raw('"Rumah Tangga"'))
            ->union($transportasi)
            ->get();

        $totalTransportasi = DB::table('aktivitas_transportasi')
            ->where('user_id', $userId)->sum('emisi_karbon') ?? 0;

        $totalRumahTangga = DB::table('aktivitas_rumah_tangga')
            ->where('user_id', $userId)->sum('emisi_karbon') ?? 0;

        return response()->json([
            'success'        => true,
            'total_gabungan' => round($totalTransportasi + $totalRumahTangga, 2),
            'data'           => $ringkasan,
        ]);
    }
}