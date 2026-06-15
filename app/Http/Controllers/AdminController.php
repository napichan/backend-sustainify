<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Hanya tampilkan user biasa (bukan admin)
    public function getUsers()
    {
        $users = DB::table('users')
            ->where('role', '!=', 'admin')
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->get();

        return response()->json($users);
    }

    public function updateUser(Request $request, $id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $data = [];
        if ($request->name)  $data['name']  = $request->name;
        if ($request->email) $data['email'] = $request->email;

        DB::table('users')->where('id', $id)->update($data);

        return response()->json(['success' => true, 'message' => 'User berhasil diupdate']);
    }

    public function deleteUser($id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Jangan bisa hapus admin
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Admin tidak bisa dihapus'], 403);
        }

        DB::table('users')->where('id', $id)->delete();

        return response()->json(['success' => true, 'message' => 'User berhasil dihapus']);
    }

    public function getAllAktivitas()
    {
        $transportasi = DB::table('aktivitas_transportasi as at')
            ->join('kendaraan as k', 'at.kendaraan_id', '=', 'k.id')
            ->join('users as u', 'at.user_id', '=', 'u.id')
            ->select(
                'u.name as nama_user',
                'u.email',
                DB::raw('"Transportasi" as kategori'),
                'k.nama_kendaraan as aktivitas',
                'at.jarak_km',
                DB::raw('NULL as durasi_jam'),
                'at.emisi_karbon',
                'at.tanggal'
            )
            ->get();

        $rumahTangga = DB::table('aktivitas_rumah_tangga as art')
            ->join('rumah_tangga as rt', 'art.aktivitas_id', '=', 'rt.id')
            ->join('users as u', 'art.user_id', '=', 'u.id')
            ->select(
                'u.name as nama_user',
                'u.email',
                DB::raw('"Rumah Tangga" as kategori'),
                'rt.nama_aktivitas as aktivitas',
                DB::raw('NULL as jarak_km'),
                'art.durasi_jam', 
                'art.emisi_karbon',
                'art.tanggal'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transportasi->merge($rumahTangga)->sortByDesc('tanggal')->values()
        ]);
    }

    public function getStats()
    {
        $totalUsers = DB::table('users')->where('role', '!=', 'admin')->count();

        $totalEmisi = DB::table('aktivitas_transportasi')->sum('emisi_karbon')
            + DB::table('aktivitas_rumah_tangga')->sum('emisi_karbon');

        $emisiPerUser = DB::table('users')
            ->where('role', '!=', 'admin')
            ->select('users.id', 'users.name', 'users.email')
            ->get()
            ->map(function ($user) {
                $emisiTr = DB::table('aktivitas_transportasi')
                    ->where('user_id', $user->id)->sum('emisi_karbon');
                $emisiRt = DB::table('aktivitas_rumah_tangga')
                    ->where('user_id', $user->id)->sum('emisi_karbon');
                $user->total_emisi = round($emisiTr + $emisiRt, 2);
                return $user;
            });

        return response()->json([
            'success'       => true,
            'total_users'   => $totalUsers,
            'total_emisi'   => round($totalEmisi, 2),
            'emisi_per_user' => $emisiPerUser,
        ]);
    }
}