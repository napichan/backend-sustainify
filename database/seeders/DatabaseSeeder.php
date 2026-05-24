<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kendaraan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun default
        User::updateOrCreate(
            ['email' => 'raina@sustainify.com'],
            [
                'name' => 'Raina',
                'password' => Hash::make('123456')
            ]
        );

        // 2. Daftar Kendaraan (berdasarkan yang ada di frontend)
        $kendaraans = [
            ['nama_kendaraan' => 'Mobil Bensin', 'faktor_emisi' => 0.02],
            ['nama_kendaraan' => 'Motor', 'faktor_emisi' => 0.01],
            ['nama_kendaraan' => 'Bus', 'faktor_emisi' => 0.005],
            ['nama_kendaraan' => 'Kendaraan Listrik', 'faktor_emisi' => 0.003],
            ['nama_kendaraan' => 'Mengendarai montor', 'faktor_emisi' => 0.02],
        ];

        foreach ($kendaraans as $k) {
            Kendaraan::updateOrCreate(
                ['nama_kendaraan' => $k['nama_kendaraan']],
                $k
            );
        }

        // 3. Data Rumah Tangga
        $this->call(RumahTanggaSeeder::class);
    }
}