<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    /**
     * Laporan Transportasi per Kendaraan
     * Menggunakan: JOIN, GROUP BY, HAVING, Subquery
     *
     * Query ini menggabungkan aktivitas_transportasi dengan tabel kendaraan (JOIN),
     * mengelompokkan per jenis kendaraan (GROUP BY),
     * hanya menampilkan kendaraan yang totalnya > rata-rata user (HAVING + Subquery)
     */
    public function laporanTransportasi(Request $request)
    {
        $userId = Auth::id();

        // Subquery: rata-rata emisi keseluruhan user ini
        $rataRataSubquery = DB::table('aktivitas_transportasi')
            ->where('user_id', $userId)
            ->avg('emisi_karbon');

        $rataRata = round($rataRataSubquery ?? 0, 4);

        // Query utama: JOIN + GROUP BY + HAVING
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
            // HAVING: hanya tampilkan kendaraan yang rata-rata emisinya >= rata-rata keseluruhan
            ->havingRaw('AVG(at.emisi_karbon) >= ?', [$rataRata])
            ->orderByDesc('total_emisi')
            ->get();

        // Subquery: total emisi transportasi user
        $totalEmisi = DB::table('aktivitas_transportasi')
            ->where('user_id', $userId)
            ->sum('emisi_karbon');

        return response()->json([
            'success'      => true,
            'rata_rata_keseluruhan' => $rataRata,
            'total_emisi_transportasi' => round($totalEmisi, 2),
            'data'         => $data,
            'keterangan'   => 'Hanya menampilkan kendaraan dengan rata-rata emisi ≥ rata-rata keseluruhan',
        ]);
    }

    /**
     * Laporan Rumah Tangga per Jenis Aktivitas
     * Menggunakan: JOIN, GROUP BY, HAVING, Subquery
     *
     * JOIN ke tabel rumah_tangga untuk dapat faktor_emisi referensi,
     * GROUP BY jenis_aktivitas,
     * HAVING hanya yang frekuensinya >= 2x,
     * Subquery untuk total emisi rumah tangga sebagai konteks
     */
    public function laporanRumahTangga(Request $request)
    {
        $userId = Auth::id();

        // Subquery: total emisi rumah tangga user
        $totalEmisiSubquery = DB::table('aktivitas_rumah_tangga')
            ->where('user_id', $userId)
            ->sum('emisi_karbon');

        $totalEmisi = round($totalEmisiSubquery ?? 0, 2);

        // Subquery: rata-rata emisi per aktivitas rumah tangga user
        $rataRataSubquery = DB::table('aktivitas_rumah_tangga')
            ->where('user_id', $userId)
            ->avg('emisi_karbon');

        $rataRata = round($rataRataSubquery ?? 0, 4);

        // Query utama: JOIN + GROUP BY + HAVING
        $data = DB::table('aktivitas_rumah_tangga as art')
            ->join('rumah_tangga as rt', 'art.jenis_aktivitas', '=', 'rt.nama_aktivitas')
            ->select([
                'art.jenis_aktivitas',
                DB::raw('COUNT(art.id) as frekuensi'),
                DB::raw('ROUND(SUM(art.durasi_jam), 1) as total_durasi'),
                DB::raw('ROUND(SUM(art.emisi_karbon), 2) as total_emisi'),
                DB::raw('ROUND(AVG(art.emisi_karbon), 2) as rata_emisi'),
                DB::raw('ROUND(MAX(art.emisi_karbon), 2) as maks_emisi'),
                DB::raw('ROUND(rt.faktor_emisi, 3) as faktor_emisi'),
                // Subquery inline: persentase kontribusi terhadap total emisi rumah tangga
                DB::raw(
                    $totalEmisi > 0
                        ? "ROUND((SUM(art.emisi_karbon) / {$totalEmisi}) * 100, 1) as persen_kontribusi"
                        : "0 as persen_kontribusi"
                ),
            ])
            ->where('art.user_id', $userId)
            ->groupBy('art.jenis_aktivitas', 'rt.faktor_emisi')
            // HAVING: hanya tampilkan aktivitas yang dilakukan minimal 2x
            ->having('frekuensi', '>=', 2)
            ->orderByDesc('total_emisi')
            ->get();

        return response()->json([
            'success'      => true,
            'rata_rata_keseluruhan' => $rataRata,
            'total_emisi_rumah_tangga' => $totalEmisi,
            'data'         => $data,
            'keterangan'   => 'Hanya menampilkan aktivitas yang dilakukan minimal 2 kali',
        ]);
    }

    /**
     * Laporan Ringkasan Gabungan
     * Menggunakan: UNION, Subquery, GROUP BY
     *
     * Menggabungkan data transportasi dan rumah tangga dalam satu laporan ringkasan
     */
    public function laporanRingkasan(Request $request)
    {
        $userId = Auth::id();

        // Subquery transportasi
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
            ->groupBy(DB::raw('1'));

        // UNION dengan rumah tangga
        $ringkasan = DB::table('aktivitas_rumah_tangga as art')
            ->join('rumah_tangga as rt', 'art.jenis_aktivitas', '=', 'rt.nama_aktivitas')
            ->select([
                DB::raw('"Rumah Tangga" as kategori'),
                DB::raw('COUNT(art.id) as total_aktivitas'),
                DB::raw('ROUND(SUM(art.emisi_karbon), 2) as total_emisi'),
                DB::raw('ROUND(AVG(art.emisi_karbon), 2) as rata_emisi'),
                DB::raw('ROUND(MAX(art.emisi_karbon), 2) as maks_emisi'),
                DB::raw('ROUND(MIN(art.emisi_karbon), 2) as min_emisi'),
            ])
            ->where('art.user_id', $userId)
            ->groupBy(DB::raw('1'))
            ->union($transportasi)
            ->get();

        // Subquery: total emisi gabungan (dua query terpisah lalu dijumlah)
        $totalTransportasi = DB::table('aktivitas_transportasi')
            ->where('user_id', $userId)
            ->sum('emisi_karbon');
        $totalRumahTangga = DB::table('aktivitas_rumah_tangga')
            ->where('user_id', $userId)
            ->sum('emisi_karbon');
        $totalGabungan = round(($totalTransportasi ?? 0) + ($totalRumahTangga ?? 0), 2);

        return response()->json([
            'success'        => true,
            'total_gabungan' => round($totalGabungan, 2),
            'data'           => $ringkasan,
        ]);
    }
}