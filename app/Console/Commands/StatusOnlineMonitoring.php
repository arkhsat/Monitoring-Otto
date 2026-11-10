<?php

namespace App\Console\Commands;

use App\Services\OnlineMonitoringService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StatusOnlineMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:status-online-monitoring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengecekan cronjob last online dan pembaruan status online/offline lokasi monitoring langsung ke database';

    /**
     * Execute the console command.
     */
    public function handle(OnlineMonitoringService $monitoringService): int
    {
        $startTime = Carbon::now();
        $startMicro = microtime(true);
        $this->info("[{$startTime->format('Y-m-d H:i:s')}] Memulai eksekusi cron job pengecekan status monitoring...");

        try {
            $result = $monitoringService->syncAllStatuses();

            $this->newLine();
            $this->line("========== RINGKASAN STATUS MONITORING ==========");
            $this->line("Total Lokasi    : {$result['total']}");
            $this->line("Status Online   : {$result['online']}");
            $this->line("Status Offline  : {$result['offline']}");
            $this->line("Perubahan Status: {$result['changes']}");
            $this->line("=================================================");
            $this->newLine();

            if (!empty($result['details'])) {
                $tableRows = array_map(function ($row) {
                    return [
                        $row['lokasi_id'],
                        $row['nama_lokasi'],
                        $row['status'],
                        $row['threshold_seconds'] . 's',
                        $row['diff_seconds'] !== null ? $row['diff_seconds'] . 's' : 'Belum Pernah',
                        $row['is_changed'] ? 'BERUBAH' : 'TETAP',
                    ];
                }, $result['details']);

                $this->table(
                    ['ID Lokasi', 'Nama Lokasi', 'Status Baru', 'Threshold', 'Selisih Last On', 'Perubahan'],
                    $tableRows
                );
            }

            $duration = round(microtime(true) - $startMicro, 2);
            $this->info("[{$startTime->format('Y-m-d H:i:s')}] Cron job selesai dijalankan dalam {$duration} detik.");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Gagal menjalankan cron job: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
