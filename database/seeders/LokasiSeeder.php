<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLocations = config('monitoring.default_locations', []);

        foreach ($defaultLocations as $loc) {
            Lokasi::updateOrCreate(
                ['nama_lokasi' => $loc['nama_lokasi']],
                [
                    'keterangan_alamat' => $loc['keterangan_alamat'] ?? $loc['address'] ?? '',
                    'batas_waktu_henti' => $loc['batas_waktu_henti'] ?? 60,
                ]
            );
        }
    }
}
