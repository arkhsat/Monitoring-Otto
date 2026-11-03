<?php

namespace Database\Seeders;

use App\Models\Lokasi;
use App\Models\OnlineMonitoring;
use App\Models\OnlineMonitoringHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OnlineMonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Lokasi 1: True Blue (Online - updated 2 minutes ago)
        $this->seedLocation(
            lokasiId: 1,
            fallbackName: 'True Blue',
            jaringan: '192.168.10.1 / Fiber Biznet 100Mbps',
            ipAddress: '192.168.10.1',
            lastUpdate: $now->copy()->subMinutes(2),
            firstMonitoredAt: $now->copy()->subDays(30),
            histories: [
                [
                    'previous_status' => 'Online',
                    'current_status' => 'Online',
                    'downtime_start' => $now->copy()->subHours(20),
                    'downtime_end' => $now->copy()->subHours(20)->addMinutes(5),
                    'duration_seconds' => 300,
                    'recovery_time' => $now->copy()->subHours(20)->addMinutes(5),
                    'recorded_at' => $now->copy()->subHours(20)->addMinutes(5),
                    'notes' => 'Gangguan singkat paket drop (RTO), koneksi stabil kembali.',
                ],
                [
                    'previous_status' => 'Gangguan',
                    'current_status' => 'Online',
                    'downtime_start' => $now->copy()->subDays(3)->setTime(2, 0),
                    'downtime_end' => $now->copy()->subDays(3)->setTime(2, 10),
                    'duration_seconds' => 600,
                    'recovery_time' => $now->copy()->subDays(3)->setTime(2, 10),
                    'recorded_at' => $now->copy()->subDays(3)->setTime(2, 10),
                    'notes' => 'Pemeliharaan rutin switch core onsite.',
                ],
            ]
        );

        // 2. Lokasi 2: Gelael Tebet (Gangguan - updated 7 minutes ago)
        $this->seedLocation(
            lokasiId: 2,
            fallbackName: 'Gelael Tebet',
            jaringan: '192.168.20.1 / Indihome 50Mbps',
            ipAddress: '192.168.20.1',
            lastUpdate: $now->copy()->subMinutes(7),
            firstMonitoredAt: $now->copy()->subDays(30),
            histories: [
                [
                    'previous_status' => 'Online',
                    'current_status' => 'Gangguan',
                    'downtime_start' => $now->copy()->subHours(5),
                    'downtime_end' => $now->copy()->subHours(5)->addMinutes(25),
                    'duration_seconds' => 1500,
                    'recovery_time' => $now->copy()->subHours(5)->addMinutes(25),
                    'recorded_at' => $now->copy()->subHours(5)->addMinutes(25),
                    'notes' => 'Flapping kabel fiber optik menuju router utama.',
                ],
                [
                    'previous_status' => 'Offline',
                    'current_status' => 'Online',
                    'downtime_start' => $now->copy()->subDays(2)->setTime(14, 0),
                    'downtime_end' => $now->copy()->subDays(2)->setTime(14, 30),
                    'duration_seconds' => 1800,
                    'recovery_time' => $now->copy()->subDays(2)->setTime(14, 30),
                    'recorded_at' => $now->copy()->subDays(2)->setTime(14, 30),
                    'notes' => 'Pemadaman listrik sementara di area gedung.',
                ],
            ]
        );

        // 3. Lokasi 3: Intermark BSD (Online - updated 1 minute ago)
        $this->seedLocation(
            lokasiId: 3,
            fallbackName: 'Intermark BSD',
            jaringan: '192.168.30.1 / MyRepublic 100Mbps',
            ipAddress: '192.168.30.1',
            lastUpdate: $now->copy()->subMinute(),
            firstMonitoredAt: $now->copy()->subDays(30),
            histories: [
                [
                    'previous_status' => 'Online',
                    'current_status' => 'Online',
                    'downtime_start' => null,
                    'downtime_end' => null,
                    'duration_seconds' => 0,
                    'recovery_time' => null,
                    'recorded_at' => $now->copy()->subDays(1),
                    'notes' => 'Koneksi prima tanpa gangguan (100% SLA).',
                ],
            ]
        );

        // 4. Lokasi 4: Ciracas Center (Offline - updated 20 minutes ago)
        $this->seedLocation(
            lokasiId: 4,
            fallbackName: 'Ciracas Center',
            jaringan: '192.168.40.1 / FirstMedia 75Mbps',
            ipAddress: '192.168.40.1',
            lastUpdate: $now->copy()->subMinutes(20),
            firstMonitoredAt: $now->copy()->subDays(30),
            histories: [
                [
                    'previous_status' => 'Online',
                    'current_status' => 'Offline',
                    'downtime_start' => $now->copy()->subHours(12),
                    'downtime_end' => $now->copy()->subHours(12)->addHours(2)->addMinutes(10),
                    'duration_seconds' => 7800,
                    'recovery_time' => $now->copy()->subHours(12)->addHours(2)->addMinutes(10),
                    'recorded_at' => $now->copy()->subHours(12)->addHours(2)->addMinutes(10),
                    'notes' => 'Kabel FO terputus proyek galian utilitas. Splicing selesai.',
                ],
                [
                    'previous_status' => 'Gangguan',
                    'current_status' => 'Online',
                    'downtime_start' => $now->copy()->subDays(4)->setTime(10, 0),
                    'downtime_end' => $now->copy()->subDays(4)->setTime(10, 45),
                    'duration_seconds' => 2700,
                    'recovery_time' => $now->copy()->subDays(4)->setTime(10, 45),
                    'recorded_at' => $now->copy()->subDays(4)->setTime(10, 45),
                    'notes' => 'Gangguan upstream ISP FirstMedia di wilayah Jakarta Timur.',
                ],
            ]
        );

        // 5. Lokasi 8: The Peak Sudirman (Online - updated 3 minutes ago)
        $this->seedLocation(
            lokasiId: 8,
            fallbackName: 'The Peak Sudirman',
            jaringan: '192.168.50.1 / Astinet 50Mbps',
            ipAddress: '192.168.50.1',
            lastUpdate: $now->copy()->subMinutes(3),
            firstMonitoredAt: $now->copy()->subDays(10),
            histories: [
                [
                    'previous_status' => 'Gangguan',
                    'current_status' => 'Online',
                    'downtime_start' => $now->copy()->subDays(1)->setTime(23, 10),
                    'downtime_end' => $now->copy()->subDays(1)->setTime(23, 18),
                    'duration_seconds' => 480,
                    'recovery_time' => $now->copy()->subDays(1)->setTime(23, 18),
                    'recorded_at' => $now->copy()->subDays(1)->setTime(23, 18),
                    'notes' => 'Restart router mikrotik onsite.',
                ],
            ]
        );

        // 6. Lokasi 9: FKS Tallasa Makassar (Offline - never updated, incomplete period 4 hours)
        $this->seedLocation(
            lokasiId: 9,
            fallbackName: 'FKS Tallasa Makassar',
            jaringan: '192.168.60.1 / Telkom Orbit 30Mbps',
            ipAddress: '192.168.60.1',
            lastUpdate: null,
            firstMonitoredAt: $now->copy()->subHours(4),
            histories: [
                [
                    'previous_status' => 'Offline',
                    'current_status' => 'Offline',
                    'downtime_start' => $now->copy()->subHours(4),
                    'downtime_end' => null,
                    'duration_seconds' => 14400,
                    'recovery_time' => null,
                    'recorded_at' => $now->copy()->subHours(4),
                    'notes' => 'Perangkat onsite belum mengirimkan heartbeat / data monitoring.',
                ],
            ]
        );
    }

    /**
     * Helper to seed a single location and its history.
     */
    protected function seedLocation(
        int $lokasiId,
        string $fallbackName,
        string $jaringan,
        string $ipAddress,
        ?Carbon $lastUpdate,
        Carbon $firstMonitoredAt,
        array $histories
    ): void {
        $lokasi = Lokasi::find($lokasiId);
        if (!$lokasi) {
            $lokasi = Lokasi::create([
                'id' => $lokasiId,
                'nama_lokasi' => $fallbackName,
                'keterangan_alamat' => 'Lokasi Onsite Parking',
                'batas_waktu_henti' => 60,
            ]);
        }

        $monitoring = OnlineMonitoring::updateOrCreate(
            ['lokasi_id' => $lokasi->id],
            [
                'jaringan' => $jaringan,
                'ip_address' => $ipAddress,
                'last_update' => $lastUpdate,
                'first_monitored_at' => $firstMonitoredAt,
            ]
        );

        // Clear existing histories for idempotent seeding
        OnlineMonitoringHistory::where('online_monitoring_id', $monitoring->id)->delete();

        foreach ($histories as $h) {
            OnlineMonitoringHistory::create([
                'online_monitoring_id' => $monitoring->id,
                'lokasi_id' => $lokasi->id,
                'jaringan' => $jaringan,
                'previous_status' => $h['previous_status'],
                'current_status' => $h['current_status'],
                'downtime_start' => $h['downtime_start'],
                'downtime_end' => $h['downtime_end'],
                'duration_seconds' => $h['duration_seconds'],
                'recovery_time' => $h['recovery_time'],
                'recorded_at' => $h['recorded_at'],
                'notes' => $h['notes'],
            ]);
        }
    }
}

