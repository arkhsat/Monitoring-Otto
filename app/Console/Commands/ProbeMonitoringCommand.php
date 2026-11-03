<?php

namespace App\Console\Commands;

use App\Models\Lokasi;
use App\Services\OnlineMonitoringService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProbeMonitoringCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:probe 
                            {lokasi_id? : ID Lokasi yang ingin di-ping/diperiksa}
                            {--all : Ping semua lokasi onsite yang ada}
                            {--status=online : Status yang disimulasikan: online, gangguan, offline}
                            {--notes= : Catatan insiden atau pemeliharaan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa status atau kirim heartbeat ping untuk monitoring onsite network';

    /**
     * Execute the console command.
     */
    public function handle(OnlineMonitoringService $service): int
    {
        $lokasiId = $this->argument('lokasi_id');
        $probeAll = $this->option('all');
        $simulatedStatus = strtolower($this->option('status') ?: 'online');
        $notes = $this->option('notes');

        // Mode 1: Probe all locations
        if ($probeAll) {
            $locations = Lokasi::all();
            $this->info("Mengirim heartbeat ping ke seluruh (" . $locations->count() . ") lokasi onsite...");

            foreach ($locations as $loc) {
                $service->recordPing($loc->id, [
                    'notes' => $notes ?: 'Heartbeat berkala dari probe scheduler',
                ]);
                $this->line(" - Lokasi [{$loc->id}] {$loc->nama_lokasi}: Ping berhasil.");
            }

            $this->info("Seluruh lokasi berhasil di-ping.");
            return self::SUCCESS;
        }

        // Mode 2: Probe a specific location
        if ($lokasiId !== null) {
            $lokasi = Lokasi::find($lokasiId);
            if (!$lokasi) {
                $this->error("Lokasi dengan ID {$lokasiId} tidak ditemukan!");
                return self::FAILURE;
            }

            $timestamp = Carbon::now();
            if ($simulatedStatus === 'gangguan') {
                $timestamp = Carbon::now()->subMinutes(6);
            } elseif ($simulatedStatus === 'offline') {
                $timestamp = Carbon::now()->subMinutes(15);
            }

            $service->recordPing($lokasi->id, [
                'timestamp' => $timestamp,
                'notes' => $notes ?: "Manual probe ({$simulatedStatus})",
            ]);

            $detail = $service->getLocationDetail($lokasi->id);
            $this->info("Heartbeat berhasil dikirim untuk [{$lokasi->id}] {$lokasi->nama_lokasi}!");
            $this->line("Status Terkini: " . $detail['status']);
            $this->line("Downtime:       " . $detail['downtime_formatted']);
            $this->line("Update:         " . $detail['last_update_absolute'] . " (" . $detail['last_update_relative'] . ")");

            return self::SUCCESS;
        }

        // Mode 3: Display current monitoring dashboard table in CLI
        $data = $service->getAllMonitoringData();
        $this->info("=== STATUS MONITORING ONSITE NETWORK ===");
        $this->line("Server Time: " . $data['server_time']);
        $this->line(sprintf("Summary: Total %d | Online: %d | Gangguan: %d | Offline: %d",
            $data['summary']['total_locations'],
            $data['summary']['online'],
            $data['summary']['gangguan'],
            $data['summary']['offline']
        ));

        $rows = [];
        foreach ($data['items'] as $item) {
            $rows[] = [
                $item['lokasi_id'],
                $item['location'],
                $item['network'],
                $item['status'],
                $item['downtime_formatted'],
                $item['last_update_relative'],
            ];
        }

        $this->table(['ID', 'Lokasi', 'Jaringan', 'Status', 'Downtime', 'Update Terakhir'], $rows);

        return self::SUCCESS;
    }
}

