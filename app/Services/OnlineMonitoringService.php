<?php

namespace App\Services;

use App\Models\Lokasi;
use App\Models\OnlineMonitoring;
use App\Models\OnlineMonitoringHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OnlineMonitoringService
{
    /**
     * Thresholds in seconds.
     */
    public const ONLINE_THRESHOLD_SECONDS = 300;     // 5 minutes
    public const GANGGUAN_THRESHOLD_SECONDS = 600;   // 10 minutes
    public const FULL_PERIOD_SECONDS = 86400;        // 24 hours

    /**
     * Dapatkan konfigurasi URL dan Token untuk Remote API
     */
    protected function getApiConfig(): array
    {
        $baseUrl = rtrim((string) config('services.monitoring_api.base_url'), '/');
        $token = (string) config('services.monitoring_api.token');
        $endpoint = str_ends_with($baseUrl, '.php') ? $baseUrl : "{$baseUrl}/online_monitoring.php";

        return [
            'base_url' => $baseUrl,
            'token' => $token,
            'endpoint' => $endpoint,
        ];
    }

    /**
     * Helper client HTTP Laravel
     */
    protected function httpClient()
    {
        $config = $this->getApiConfig();

        return Http::timeout(8)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$config['token']}",
                'X-API-TOKEN' => $config['token'],
            ]);
    }

    /**
     * Determine network status from last update timestamp relative to server time and threshold.
     */
    public function calculateStatus(?Carbon $lastUpdate, ?Carbon $serverNow = null, int $thresholdSeconds = 60): array
    {
        $serverNow = $serverNow ?: Carbon::now();

        if ($lastUpdate === null) {
            return [
                'status' => 'Offline',
                'code' => 'offline',
                'diff_seconds' => null,
            ];
        }

        $diffSeconds = (int) $lastUpdate->diffInSeconds($serverNow, false);
        if ($diffSeconds < 0) {
            $diffSeconds = 0;
        }

        $threshold = $thresholdSeconds > 0 ? $thresholdSeconds : 60;

        if ($diffSeconds <= $threshold) {
            return [
                'status' => 'Online',
                'code' => 'online',
                'diff_seconds' => $diffSeconds,
            ];
        }

        if ($diffSeconds <= self::GANGGUAN_THRESHOLD_SECONDS && $threshold < self::GANGGUAN_THRESHOLD_SECONDS) {
            return [
                'status' => 'Gangguan',
                'code' => 'gangguan',
                'diff_seconds' => $diffSeconds,
            ];
        }

        return [
            'status' => 'Offline',
            'code' => 'offline',
            'diff_seconds' => $diffSeconds,
        ];
    }

    /**
     * Calculate downtime variable: time now - last online time.
     * RESET SETIAP JAM 00:00 (Dihitung per hari berjalan mulai dari 00:00:00).
     */
    public function calculateDowntime(?OnlineMonitoring $device, ?Carbon $serverNow = null, int $thresholdSeconds = 60): array
    {
        $serverNow = $serverNow ?: Carbon::now();
        $lastUpdate = $device?->last_update;
        $threshold = $thresholdSeconds > 0 ? $thresholdSeconds : 60;

        // Reset harian: Periode dimulai dari jam 00:00:00 hari ini
        $startOfDay = $serverNow->copy()->startOfDay();
        $elapsedToday = max(1, (int) $startOfDay->diffInSeconds($serverNow));

        if (!$device || !$lastUpdate) {
            return [
                'downtime_seconds' => $elapsedToday,
                'uptime_seconds' => 0,
                'downtime_formatted' => $this->formatDuration($elapsedToday) . ' (100%)',
                'uptime_percentage' => 0.0,
                'downtime_percentage' => 100.0,
                'monitored_period_seconds' => $elapsedToday,
                'is_incomplete_period' => false,
                'monitored_hours' => round($elapsedToday / 3600, 1),
            ];
        }

        $diffSeconds = (int) max(0, $lastUpdate->diffInSeconds($serverNow, false));

        // Jika Online (masih di dalam batas waktu henti)
        if ($diffSeconds <= $threshold) {
            return [
                'downtime_seconds' => 0,
                'uptime_seconds' => $elapsedToday,
                'downtime_formatted' => '0m (0%)',
                'uptime_percentage' => 100.0,
                'downtime_percentage' => 0.0,
                'monitored_period_seconds' => $elapsedToday,
                'is_incomplete_period' => false,
                'monitored_hours' => round($elapsedToday / 3600, 1),
            ];
        }

        // Tentukan titik mulai offline
        $downtimeStart = $lastUpdate->copy()->addSeconds($threshold);

        // Jika offline terjadi sebelum jam 00:00 hari ini, maka downtime hari ini dihitung sejak jam 00:00 (reset)
        if ($downtimeStart->lt($startOfDay)) {
            $downtimeToday = $elapsedToday;
        } else {
            $downtimeToday = max(0, min($elapsedToday, (int) $downtimeStart->diffInSeconds($serverNow)));
        }

        $uptimeToday = max(0, $elapsedToday - $downtimeToday);
        $uptimePct = round(($uptimeToday / $elapsedToday) * 100, 1);
        $downtimePct = round(($downtimeToday / $elapsedToday) * 100, 1);
        $downtimeFormatted = $this->formatDuration($downtimeToday) . " ({$downtimePct}%)";

        return [
            'downtime_seconds' => $downtimeToday,
            'uptime_seconds' => $uptimeToday,
            'downtime_formatted' => $downtimeFormatted,
            'uptime_percentage' => $uptimePct,
            'downtime_percentage' => $downtimePct,
            'monitored_period_seconds' => $elapsedToday,
            'is_incomplete_period' => false,
            'monitored_hours' => round($elapsedToday / 3600, 1),
        ];
    }

    /**
     * Format seconds into readable duration string (e.g. 0m, 25m, 2h 10m).
     */
    public function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0m';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Retrieve all online monitoring data with calculated status and downtime.
     * Mengambil dari RESTful API domain remote terlebih dahulu, lalu sinkronkan ke DB lokal.
     */
    public function getAllMonitoringData(): array
    {
        $config = $this->getApiConfig();

        // 1. Ambil dari Remote RESTful API domain
        try {
            $response = $this->httpClient()->get($config['endpoint']);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['success']) && isset($json['data'])) {
                    $items = $json['data'];

                    // Sinkronkan ke database lokal
                    $this->syncRemoteMonitoringToLocal($items);

                    return [
                        'items' => $items,
                        'summary' => $json['summary'] ?? [
                            'total_locations' => count($items),
                            'online' => 0,
                            'gangguan' => 0,
                            'offline' => 0,
                        ],
                        'server_time' => $json['server_time'] ?? Carbon::now()->toIso8601String(),
                        'source' => 'remote',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Remote Online Monitoring API failed: " . $e->getMessage() . ". Using local fallback.");
        }

        // 2. Fallback ke database lokal jika remote API belum merespons
        return $this->getLocalMonitoringData();
    }

    /**
     * Fallback lokal untuk getAllMonitoringData (dengan reset downtime harian 00:00)
     */
    protected function getLocalMonitoringData(): array
    {
        $serverNow = Carbon::now();
        $locations = collect([]);

        try {
            $locations = Lokasi::with('onlineMonitoring')->orderBy('id', 'asc')->get();
        } catch (\Throwable $e) {
            // Database lokal offline
            $locations = collect([]);
        }

        $items = [];
        $countOnline = 0;
        $countGangguan = 0;
        $countOffline = 0;

        foreach ($locations as $lokasi) {
            $device = $lokasi->onlineMonitoring;
            $lastUpdate = $device?->last_update;
            $threshold = (int) ($lokasi->batas_waktu_henti ?? 60);
            if ($threshold <= 0) {
                $threshold = 60;
            }

            $statusData = $this->calculateStatus($lastUpdate, $serverNow, $threshold);
            $downtimeData = $this->calculateDowntime($device, $serverNow, $threshold);

            if ($statusData['code'] === 'online') {
                $countOnline++;
            } elseif ($statusData['code'] === 'gangguan') {
                $countGangguan++;
            } else {
                $countOffline++;
            }

            $lastUpdateAbsolute = '-';
            $lastUpdateRelative = 'Belum pernah update';
            $lastUpdateIso = null;

            if ($lastUpdate) {
                $lastUpdateIso = $lastUpdate->toIso8601String();
                $lastUpdateAbsolute = $lastUpdate->locale('id')->isoFormat('D MMMM Y, HH:mm:ss');
                $lastUpdateRelative = $lastUpdate->locale('id')->diffForHumans($serverNow, [
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                    'parts' => 1,
                ]);
            }

            $items[] = [
                'id' => $device?->id ?? $lokasi->id,
                'lokasi_id' => $lokasi->id,
                'location' => $lokasi->nama_lokasi,
                'address' => $lokasi->keterangan_alamat ?: '-',
                'network' => $device?->jaringan ?: 'Dynamic / DHCP Network',
                'ip_address' => $device?->ip_address,
                'status' => $statusData['status'],
                'status_code' => $statusData['code'],
                'last_update' => $lastUpdateIso,
                'last_update_absolute' => $lastUpdateAbsolute,
                'last_update_relative' => $lastUpdateRelative,
                'diff_seconds' => $statusData['diff_seconds'],
                'downtime_seconds' => $downtimeData['downtime_seconds'],
                'uptime_seconds' => $downtimeData['uptime_seconds'],
                'downtime_formatted' => $downtimeData['downtime_formatted'],
                'uptime_percentage' => $downtimeData['uptime_percentage'],
                'downtime_percentage' => $downtimeData['downtime_percentage'],
                'monitored_period_seconds' => $downtimeData['monitored_period_seconds'],
                'is_incomplete_period' => false,
                'monitored_hours' => $downtimeData['monitored_hours'],
            ];
        }

        $summary = [
            'total_locations' => count($items),
            'online' => $countOnline,
            'gangguan' => $countGangguan,
            'offline' => $countOffline,
        ];

        return [
            'items' => $items,
            'summary' => $summary,
            'server_time' => $serverNow->toIso8601String(),
            'source' => 'local_fallback',
        ];
    }

    /**
     * Sinkronkan data monitoring dari remote API ke database lokal
     */
    protected function syncRemoteMonitoringToLocal(array $items): void
    {
        try {
            foreach ($items as $item) {
                $lokasiId = $item['lokasi_id'] ?? null;
                if (!$lokasiId) continue;

                $device = OnlineMonitoring::firstOrNew(['lokasi_id' => $lokasiId]);
                $device->jaringan = $item['network'] ?? ($device->jaringan ?: 'Dynamic / DHCP Network');
                if (isset($item['ip_address'])) {
                    $device->ip_address = $item['ip_address'];
                }
                $device->status = $item['status'] ?? 'Offline';
                if (!empty($item['last_update'])) {
                    $device->last_update = Carbon::parse($item['last_update']);
                }
                $device->save();
            }
        } catch (\Throwable $e) {
            // Database lokal tidak terhubung / offline
        }
    }

    /**
     * Get detailed monitoring state for a specific location.
     */
    public function getLocationDetail(int $lokasiId): array
    {
        $all = $this->getAllMonitoringData();
        foreach ($all['items'] as $item) {
            if ($item['lokasi_id'] == $lokasiId) {
                return $item;
            }
        }

        $lokasi = Lokasi::with('onlineMonitoring')->findOrFail($lokasiId);
        $device = $lokasi->onlineMonitoring;
        $serverNow = Carbon::now();
        $threshold = (int) ($lokasi->batas_waktu_henti ?? 60);

        $statusData = $this->calculateStatus($device?->last_update, $serverNow, $threshold);
        $downtimeData = $this->calculateDowntime($device, $serverNow, $threshold);

        return [
            'id' => $device?->id ?? $lokasi->id,
            'lokasi_id' => $lokasi->id,
            'location' => $lokasi->nama_lokasi,
            'address' => $lokasi->keterangan_alamat ?: '-',
            'network' => $device?->jaringan ?: 'Dynamic / DHCP Network',
            'ip_address' => $device?->ip_address,
            'status' => $statusData['status'],
            'status_code' => $statusData['code'],
            'last_update' => $device?->last_update?->toIso8601String(),
            'last_update_absolute' => $device?->last_update?->locale('id')->isoFormat('D MMMM Y, HH:mm:ss') ?: '-',
            'last_update_relative' => $device?->last_update?->locale('id')->diffForHumans($serverNow) ?: 'Belum pernah update',
            'diff_seconds' => $statusData['diff_seconds'],
            'downtime_seconds' => $downtimeData['downtime_seconds'],
            'uptime_seconds' => $downtimeData['uptime_seconds'],
            'downtime_formatted' => $downtimeData['downtime_formatted'],
            'uptime_percentage' => $downtimeData['uptime_percentage'],
            'downtime_percentage' => $downtimeData['downtime_percentage'],
            'monitored_period_seconds' => $downtimeData['monitored_period_seconds'],
            'is_incomplete_period' => false,
            'monitored_hours' => $downtimeData['monitored_hours'],
        ];
    }

    /**
     * Retrieve monitoring history logs with optional date range filters.
     * Mengambil dari remote API domain, dengan fallback ke lokal.
     */
    public function getLocationHistory(
        int $lokasiId,
        ?string $date = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 50
    ): array {
        $config = $this->getApiConfig();

        try {
            $params = [
                'action' => 'history',
                'lokasi_id' => $lokasiId,
                'limit' => $limit,
            ];
            if ($date) $params['date'] = $date;
            if ($startDate) $params['start_date'] = $startDate;
            if ($endDate) $params['end_date'] = $endDate;

            $response = $this->httpClient()->get($config['endpoint'], $params);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['success']) && isset($json['records'])) {
                    return [
                        'location' => $json['location'],
                        'metrics' => $json['metrics'],
                        'records' => $json['records'],
                        'source' => 'remote',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Remote Location History API failed: " . $e->getMessage());
        }

        // Fallback local DB
        return $this->getLocalLocationHistory($lokasiId, $date, $startDate, $endDate, $limit);
    }

    /**
     * Fallback riwayat lokal
     */
    protected function getLocalLocationHistory(
        int $lokasiId,
        ?string $date = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 50
    ): array {
        $lokasi = Lokasi::with('onlineMonitoring')->findOrFail($lokasiId);
        $device = $lokasi->onlineMonitoring;

        $query = OnlineMonitoringHistory::where('lokasi_id', $lokasiId);

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('recorded_at', '>=', $startDate)
                  ->whereDate('recorded_at', '<=', $endDate);
        } elseif (!empty($startDate)) {
            $query->whereDate('recorded_at', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->whereDate('recorded_at', '<=', $endDate);
        } elseif (!empty($date)) {
            $query->whereDate('recorded_at', $date);
        }

        $histories = $query->orderBy('recorded_at', 'desc')->limit($limit)->get();

        $totalDowntimeSeconds = 0;
        $totalIncidents = 0;
        $records = [];

        foreach ($histories as $h) {
            $totalDowntimeSeconds += (int) $h->duration_seconds;
            if (in_array(strtolower($h->current_status), ['gangguan', 'offline'])) {
                $totalIncidents++;
            }

            $records[] = [
                'id' => $h->id,
                'location_name' => $lokasi->nama_lokasi,
                'network' => $h->jaringan,
                'recorded_at' => $h->recorded_at?->toIso8601String(),
                'recorded_at_formatted' => $h->recorded_at?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
                'previous_status' => $h->previous_status,
                'current_status' => $h->current_status,
                'downtime_start' => $h->downtime_start?->toIso8601String(),
                'downtime_start_formatted' => $h->downtime_start?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
                'downtime_end' => $h->downtime_end?->toIso8601String(),
                'downtime_end_formatted' => $h->downtime_end?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
                'duration_seconds' => (int) $h->duration_seconds,
                'duration_formatted' => $this->formatDuration((int) $h->duration_seconds),
                'recovery_time' => $h->recovery_time?->toIso8601String(),
                'recovery_time_formatted' => $h->recovery_time?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
                'notes' => $h->notes ?: '-',
            ];
        }

        $periodSeconds = !empty($startDate) && !empty($endDate)
            ? max(86400, Carbon::parse($startDate)->diffInSeconds(Carbon::parse($endDate)->endOfDay()))
            : (30 * 86400);

        $uptimeSeconds = max(0, $periodSeconds - $totalDowntimeSeconds);
        $uptimePercentage = round(($uptimeSeconds / $periodSeconds) * 100, 1);

        return [
            'location' => [
                'id' => $lokasi->id,
                'name' => $lokasi->nama_lokasi,
                'network' => $device?->jaringan ?: '-',
                'address' => $lokasi->keterangan_alamat ?: '-',
            ],
            'metrics' => [
                'total_downtime_seconds' => $totalDowntimeSeconds,
                'total_downtime_formatted' => $this->formatDuration($totalDowntimeSeconds),
                'uptime_percentage' => $uptimePercentage,
                'total_incidents' => $totalIncidents,
                'total_records' => count($records),
            ],
            'records' => $records,
        ];
    }

    /**
     * Ingest heartbeat/ping from an onsite device or automated probe.
     * Dicatat di lokal dan diteruskan ke remote API domain.
     */
    public function recordPing(int $lokasiId, array $data = []): OnlineMonitoring
    {
        $lokasi = Lokasi::findOrFail($lokasiId);
        $now = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : Carbon::now();

        $device = OnlineMonitoring::firstOrNew(['lokasi_id' => $lokasiId]);
        $threshold = (int) ($lokasi->batas_waktu_henti ?? 60);
        $previousStatus = $this->calculateStatus($device->last_update, $now, $threshold);

        if (!$device->exists) {
            $device->first_monitored_at = $now;
        }

        if (!empty($data['jaringan'])) {
            $device->jaringan = $data['jaringan'];
        } elseif (empty($device->jaringan)) {
            $device->jaringan = 'Dynamic / DHCP Network';
        }

        if (isset($data['ip_address'])) {
            $device->ip_address = $data['ip_address'];
        }

        $device->status = 'Online';
        $device->last_update = $now;
        $device->save();

        if ($previousStatus['code'] !== 'online' && $device->wasRecentlyCreated === false) {
            $downtimeStart = $device->last_update
                ? $device->last_update->copy()->addSeconds($threshold)
                : $now->copy()->subMinutes(10);

            $duration = max(0, $now->diffInSeconds($downtimeStart));

            OnlineMonitoringHistory::create([
                'online_monitoring_id' => $device->id,
                'lokasi_id' => $lokasiId,
                'jaringan' => $device->jaringan,
                'previous_status' => $previousStatus['status'],
                'current_status' => 'Online',
                'downtime_start' => $downtimeStart,
                'downtime_end' => $now,
                'duration_seconds' => $duration,
                'recovery_time' => $now,
                'recorded_at' => $now,
                'notes' => $data['notes'] ?? 'Koneksi pulih (Heartbeat diterima dari onsite probe)',
            ]);
        }

        // Teruskan ping ke Remote API domain
        try {
            $config = $this->getApiConfig();
            $this->httpClient()->post("{$config['endpoint']}?action=ping", array_merge($data, [
                'lokasi_id' => $lokasiId,
                'status' => 'Online',
                'jaringan' => $device->jaringan,
                'ip_address' => $device->ip_address,
            ]));
        } catch (\Throwable $e) {
            Log::debug("Forwarding ping to remote API error: " . $e->getMessage());
        }

        return $device;
    }

    /**
     * CRON JOB: Pengecekan status Online/Offline berkala (setiap menit).
     * Jika (now - last_update) > batas_waktu_henti, status langsung diubah menjadi Offline.
     * Memicu cron di database domain dan menyinkronkannya ke lokal.
     */
    public function syncAllStatuses(): array
    {
        $config = $this->getApiConfig();
        $remoteChanges = 0;

        // 1. Eksekusi cron di remote database domain
        try {
            $response = $this->httpClient()->get("{$config['endpoint']}?action=cron");
            if ($response->successful()) {
                $remoteData = $response->json();
                $remoteChanges = $remoteData['summary']['changes'] ?? 0;
            }
        } catch (\Throwable $e) {
            Log::warning("Remote Cron Execution failed: " . $e->getMessage());
        }

        // 2. Evaluasi status di lokal dan update tabel lokal
        $serverNow = Carbon::now();
        $locations = Lokasi::with('onlineMonitoring')->orderBy('id', 'asc')->get();

        $countOnline = 0;
        $countOffline = 0;
        $countChanges = $remoteChanges;
        $details = [];

        foreach ($locations as $lokasi) {
            $device = $lokasi->onlineMonitoring;
            if (!$device) {
                $device = OnlineMonitoring::create([
                    'lokasi_id' => $lokasi->id,
                    'jaringan' => 'Dynamic / DHCP Network',
                    'status' => 'Offline',
                    'first_monitored_at' => $serverNow,
                ]);
            }

            $threshold = (int) ($lokasi->batas_waktu_henti ?? 60);
            if ($threshold <= 0) $threshold = 60;

            $lastUpdate = $device->last_update;
            $oldStatus = $device->status ?? 'Offline';
            $diffSeconds = $lastUpdate ? (int) max(0, $lastUpdate->diffInSeconds($serverNow, false)) : null;

            // Jika melebihi batas waktu henti atau belum pernah update => Offline
            if ($lastUpdate === null || $diffSeconds > $threshold) {
                $newStatus = 'Offline';
            } else {
                $newStatus = 'Online';
            }

            $isChanged = ($oldStatus !== $newStatus);

            if ($isChanged || empty($device->status)) {
                $device->status = $newStatus;
                $device->save();
                $countChanges++;

                // Catat log transisi riwayat jika status berubah
                OnlineMonitoringHistory::create([
                    'online_monitoring_id' => $device->id,
                    'lokasi_id' => $lokasi->id,
                    'jaringan' => $device->jaringan ?: 'Dynamic / DHCP Network',
                    'previous_status' => $oldStatus,
                    'current_status' => $newStatus,
                    'downtime_start' => ($newStatus === 'Offline') ? ($lastUpdate ? $lastUpdate->copy()->addSeconds($threshold) : $serverNow) : null,
                    'downtime_end' => ($newStatus === 'Online') ? $serverNow : null,
                    'duration_seconds' => ($diffSeconds && $diffSeconds > $threshold) ? ($diffSeconds - $threshold) : 0,
                    'recovery_time' => ($newStatus === 'Online') ? $serverNow : null,
                    'recorded_at' => $serverNow,
                    'notes' => ($newStatus === 'Offline')
                        ? "Otomatis diubah menjadi Offline oleh Cron Job (melebihi batas waktu henti {$threshold} detik)"
                        : "Koneksi pulih menjadi Online",
                ]);
            }

            if ($newStatus === 'Online') {
                $countOnline++;
            } else {
                $countOffline++;
            }

            $details[] = [
                'lokasi_id' => $lokasi->id,
                'nama_lokasi' => $lokasi->nama_lokasi,
                'threshold_seconds' => $threshold,
                'last_update' => $lastUpdate?->toIso8601String(),
                'diff_seconds' => $diffSeconds,
                'previous_status' => $oldStatus,
                'status' => $newStatus,
                'is_changed' => $isChanged,
            ];
        }

        return [
            'total' => count($details),
            'online' => $countOnline,
            'offline' => $countOffline,
            'changes' => $countChanges,
            'details' => $details,
        ];
    }
}
