<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonitoringDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            1 => [ // True Blue
                'base' => 900000,
                'variance' => [
                    ['date' => '2026-10-01 10:00:00', 'admin' => 900000, 'mdd' => 960000, 'lokal' => 900000],
                    ['date' => '2026-10-02 11:30:00', 'admin' => 900000, 'mdd' => 900000, 'lokal' => 900000],
                    ['date' => '2026-10-03 14:15:00', 'admin' => 900000, 'mdd' => 900000, 'lokal' => 900000],
                    ['date' => '2026-10-04 16:45:00', 'admin' => 900000, 'mdd' => 960000, 'lokal' => 900000],
                    ['date' => '2026-10-05 09:20:00', 'admin' => 900000, 'mdd' => 900000, 'lokal' => 900000],
                    ['date' => '2026-10-06 17:00:00', 'admin' => 900000, 'mdd' => 900000, 'lokal' => 900000],
                    ['date' => '2026-10-07 18:10:00', 'admin' => 900000, 'mdd' => 960000, 'lokal' => 900000],
                    ['date' => '2026-10-08 12:00:00', 'admin' => 900000, 'mdd' => 900000, 'lokal' => 900000],
                    ['date' => '2026-10-09 15:40:00', 'admin' => 920000, 'mdd' => 920000, 'lokal' => 920000],
                    ['date' => '2026-10-10 19:25:00', 'admin' => 950000, 'mdd' => 980000, 'lokal' => 950000],
                    ['date' => '2026-10-11 13:10:00', 'admin' => 950000, 'mdd' => 950000, 'lokal' => 950000],
                    ['date' => '2026-10-12 11:00:00', 'admin' => 910000, 'mdd' => 910000, 'lokal' => 910000],
                ],
            ],
            2 => [ // Gelael Tebet
                'base' => 650000,
                'variance' => [
                    ['date' => '2026-10-01 10:15:00', 'admin' => 650000, 'mdd' => 650000, 'lokal' => 650000],
                    ['date' => '2026-10-02 11:20:00', 'admin' => 670000, 'mdd' => 670000, 'lokal' => 670000],
                    ['date' => '2026-10-03 14:40:00', 'admin' => 700000, 'mdd' => 750000, 'lokal' => 700000],
                    ['date' => '2026-10-04 16:10:00', 'admin' => 680000, 'mdd' => 680000, 'lokal' => 680000],
                    ['date' => '2026-10-05 09:50:00', 'admin' => 620000, 'mdd' => 620000, 'lokal' => 620000],
                    ['date' => '2026-10-06 17:30:00', 'admin' => 640000, 'mdd' => 690000, 'lokal' => 640000],
                    ['date' => '2026-10-07 18:00:00', 'admin' => 650000, 'mdd' => 650000, 'lokal' => 650000],
                ],
            ],
            3 => [ // Intermark BSD
                'base' => 1200000,
                'variance' => [
                    ['date' => '2026-10-01 08:30:00', 'admin' => 1200000, 'mdd' => 1200000, 'lokal' => 1200000],
                    ['date' => '2026-10-02 10:15:00', 'admin' => 1250000, 'mdd' => 1250000, 'lokal' => 1250000],
                    ['date' => '2026-10-03 13:45:00', 'admin' => 1400000, 'mdd' => 1450000, 'lokal' => 1400000],
                    ['date' => '2026-10-04 15:20:00', 'admin' => 1380000, 'mdd' => 1380000, 'lokal' => 1380000],
                    ['date' => '2026-10-05 11:10:00', 'admin' => 1190000, 'mdd' => 1190000, 'lokal' => 1190000],
                    ['date' => '2026-10-06 16:00:00', 'admin' => 1310000, 'mdd' => 1310000, 'lokal' => 1310000],
                ],
            ],
            4 => [ // Ciracas Center
                'base' => 450000,
                'variance' => [
                    ['date' => '2026-10-01 09:00:00', 'admin' => 450000, 'mdd' => 450000, 'lokal' => 450000],
                    ['date' => '2026-10-02 11:00:00', 'admin' => 480000, 'mdd' => 510000, 'lokal' => 480000],
                    ['date' => '2026-10-03 13:30:00', 'admin' => 520000, 'mdd' => 520000, 'lokal' => 520000],
                    ['date' => '2026-10-04 15:00:00', 'admin' => 490000, 'mdd' => 490000, 'lokal' => 490000],
                    ['date' => '2026-10-05 12:15:00', 'admin' => 430000, 'mdd' => 430000, 'lokal' => 430000],
                ],
            ],
        ];

        foreach ($locations as $kode => $info) {
            foreach ($info['variance'] as $item) {
                // Avoid inserting duplicates
                $exists = DB::table('data')
                    ->where('Kode', $kode)
                    ->where('Tanggal', $item['date'])
                    ->exists();

                if (!$exists) {
                    DB::table('data')->insert([
                        'Tanggal' => $item['date'],
                        'Admin' => $item['admin'],
                        'Settlement' => $item['mdd'],
                        'Lokal' => $item['lokal'],
                        'Kode' => $kode,
                    ]);
                }
            }
        }
    }
}
