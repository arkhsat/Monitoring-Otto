<?php

namespace App\Services;

use App\Models\Lokasi;
use App\Models\OnlineMonitoring;
use App\Models\OnlineMonitoringHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OnlineMonitoringService
{
    /**
     * Thresholds in seconds.
     */
    public const ONLINE_THRESHOLD_SECONDS = 300;     // 5 minutes
    public const GANGGUAN_THRESHOLD_SECONDS = 600;   // 10 minutes
    public const FULL_PERIOD_SECONDS = 86400;        // 24 hours

    /**
     * Determine network status from last update timestamp relative to server time.
     *
     * Rules:
     * - Online: last update < 5 minutes (300 seconds)
     * - Gangguan: 5 minutes <= last update < 10 minutes (300 - 599 seconds)
     * - Offline: last update >= 10 minutes (>= 600 seconds) or null
     *
     * @param Carbon|null $lastUpdate
     * @param Carbon|null $serverNow
     * @return array{status: string, code: string, diff_seconds: int|null}
     */
    public function calculateStatus(?Carbon $lastUpdate, ?Carbon $serverNow = null): array
    {
        $serverNow = $serverNow ?: Carbon::now();

        if ($lastUpdate === null) {
            return [
                'status' => 'Offline',
                'code' => 'offline',
                'diff_seconds' => null,
            ];
        }

        $diffSeconds = $lastUpdate->diffInSeconds($serverNow, false);
        if ($diffSeconds < 0) {
            $diffSeconds = 0;
        }

        if ($diffSeconds < self::ONLINE_THRESHOLD_SECONDS) {
            return [
                'status' => 'Online',
                'code' => 'online',
                'diff_seconds' => (int) $diffSeconds,
            ];
        }

        if ($diffSeconds < self::GANGGUAN_THRESHOLD_SECONDS) {
            return [
                'status' => 'Gangguan',
                'code' => 'gangguan',
                'diff_seconds' => (int) $diffSeconds,
            ];
        }

        return [
            'status' => 'Offline',
            'code' => 'offline',
            'diff_seconds' => (int) $diffSeconds,
        ];
    }

    /**
     * Calculate 24-hour downtime and uptime using interval union.
     *
     * Concept: Downtime = 24 Hours - Total Uptime
     * Accurately merges overlapping intervals, factors active outages,
     * and accounts for incomplete monitoring periods.
     *
     * @param OnlineMonitoring|null $device
     * @param Carbon|null $serverNow
     * @return array{
     *   downtime_seconds: int,
     *   uptime_seconds: int,
     *   downtime_formatted: string,
     *   uptime_percentage: float,
     *   downtime_percentage: float,
     *   monitored_period_seconds: int,
     *   is_incomplete_period: bool,
     *   monitored_hours: float
     * }
     */
    public function calculateDowntime(?OnlineMonitoring $device, ?Carbon $serverNow = null): array
    {
        $serverNow = $serverNow ?: Carbon::now();
        $windowStart = $serverNow->copy()->subHours(24);
        $windowEnd = $serverNow->copy();

        if (!$device) {
            return [
                'downtime_seconds' => self::FULL_PERIOD_SECONDS,
                'uptime_seconds' => 0,
                'downtime_formatted' => '24h 0m (100%)',
                'uptime_percentage' => 0.0,
                'downtime_percentage' => 100.0,
                'monitored_period_seconds' => self::FULL_PERIOD_SECONDS,
                'is_incomplete_period' => false,
                'monitored_hours' => 24.0,
            ];
        }

        // Determine effective monitoring period for this device
        $firstMonitored = $device->first_monitored_at ?: $device->created_at ?: $windowStart;
        if ($firstMonitored->gt($windowStart)) {
            $effectiveStart = $firstMonitored->copy();
            $monitoredSeconds = max(1, $windowEnd->diffInSeconds($effectiveStart));
            $monitoredSeconds = min(self::FULL_PERIOD_SECONDS, $monitoredSeconds);
            $isIncomplete = true;
        } else {
            $effectiveStart = $windowStart->copy();
            $monitoredSeconds = self::FULL_PERIOD_SECONDS;
            $isIncomplete = false;
        }

        // 1. Gather historical outage intervals within effective period
        $histories = OnlineMonitoringHistory::where('online_monitoring_id', $device->id)
            ->whereNotNull('downtime_start')
            ->where(function ($query) use ($effectiveStart) {
                $query->whereNull('downtime_end')
                      ->orWhere('downtime_end', '>=', $effectiveStart);
            })
            ->where('downtime_start', '<=', $windowEnd)
            ->get();

        $rawIntervals = [];

        foreach ($histories as $history) {
            $start = $history->downtime_start->copy();
            $end = $history->downtime_end ? $history->downtime_end->copy() : $windowEnd->copy();

            // Clip interval to effective window
            $clippedStart = $start->lt($effectiveStart) ? $effectiveStart->timestamp : $start->timestamp;
            $clippedEnd = $end->gt($windowEnd) ? $windowEnd->timestamp : $end->timestamp;

            if ($clippedEnd > $clippedStart) {
                $rawIntervals[] = [
                    'start' => $clippedStart,
                    'end' => $clippedEnd,
                ];
            }
        }

        // 2. Add ongoing outage if device is currently Gangguan or Offline
        $currentStatus = $this->calculateStatus($device->last_update, $serverNow);
        if ($currentStatus['code'] !== 'online') {
            if ($device->last_update) {
                // Outage started when it exceeded the 5-minute threshold
                $ongoingStart = $device->last_update->copy()->addSeconds(self::ONLINE_THRESHOLD_SECONDS);
            } else {
                // Never updated, down for entire effective monitored period
                $ongoingStart = $effectiveStart->copy();
            }

            $clippedStart = $ongoingStart->lt($effectiveStart) ? $effectiveStart->timestamp : $ongoingStart->timestamp;
            $clippedEnd = $windowEnd->timestamp;

            if ($clippedEnd > $clippedStart) {
                $rawIntervals[] = [
                    'start' => $clippedStart,
                    'end' => $clippedEnd,
                ];
            }
        }

        // 3. Interval Union Algorithm (merge overlapping/contiguous intervals)
        $mergedIntervals = $this->mergeIntervals($rawIntervals);

        $totalDowntimeSeconds = 0;
        foreach ($mergedIntervals as $interval) {
            $totalDowntimeSeconds += ($interval['end'] - $interval['start']);
        }

        // Bound downtime to monitored period
        $totalDowntimeSeconds = min($monitoredSeconds, max(0, $totalDowntimeSeconds));
        $totalUptimeSeconds = max(0, $monitoredSeconds - $totalDowntimeSeconds);

        $uptimePct = $monitoredSeconds > 0 ? round(($totalUptimeSeconds / $monitoredSeconds) * 100, 1) : 0.0;
        $downtimePct = $monitoredSeconds > 0 ? round(($totalDowntimeSeconds / $monitoredSeconds) * 100, 1) : 0.0;

        $downtimeFormatted = $this->formatDuration($totalDowntimeSeconds) . " ({$downtimePct}%)";

        return [
            'downtime_seconds' => $totalDowntimeSeconds,
            'uptime_seconds' => $totalUptimeSeconds,
            'downtime_formatted' => $downtimeFormatted,
            'uptime_percentage' => $uptimePct,
            'downtime_percentage' => $downtimePct,
            'monitored_period_seconds' => $monitoredSeconds,
            'is_incomplete_period' => $isIncomplete,
            'monitored_hours' => round($monitoredSeconds / 3600, 1),
        ];
    }

    /**
     * Merge overlapping or adjacent timestamp intervals.
     *
     * @param array<int, array{start: int, end: int}> $intervals
     * @return array<int, array{start: int, end: int}>
     */
    protected function mergeIntervals(array $intervals): array
    {
        if (empty($intervals)) {
            return [];
        }

        usort($intervals, function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });

        $merged = [];
        foreach ($intervals as $interval) {
            if (empty($merged)) {
                $merged[] = $interval;
            } else {
                $lastIndex = count($merged) - 1;
                if ($interval['start'] <= $merged[$lastIndex]['end']) {
                    $merged[$lastIndex]['end'] = max($merged[$lastIndex]['end'], $interval['end']);
                } else {
                    $merged[] = $interval;
                }
            }
        }

        return $merged;
    }

    /**
     * Format seconds into readable duration string (e.g. 0m, 25m, 2h 10m).
     *
     * @param int $seconds
     * @return string
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
     *
     * @return array{
     *   items: array<int, array<string, mixed>>,
     *   summary: array{total_locations: int, online: int, gangguan: int, offline: int},
     *   server_time: string
     * }
     */
    public function getAllMonitoringData(): array
    {
        $serverNow = Carbon::now();
        $locations = Lokasi::with('onlineMonitoring')->orderBy('id', 'asc')->get();

        $items = [];
        $countOnline = 0;
        $countGangguan = 0;
        $countOffline = 0;

        foreach ($locations as $lokasi) {
            $device = $lokasi->onlineMonitoring;
            $lastUpdate = $device?->last_update;

            $statusData = $this->calculateStatus($lastUpdate, $serverNow);
            $downtimeData = $this->calculateDowntime($device, $serverNow);

            if ($statusData['code'] === 'online') {
                $countOnline++;
            } elseif ($statusData['code'] === 'gangguan') {
                $countGangguan++;
            } else {
                $countOffline++;
            }

            // Format last update timestamps (Dual indicator: absolute + relative)
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
                'network' => $device?->jaringan ?: '-',
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
                'is_incomplete_period' => $downtimeData['is_incomplete_period'],
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
        ];
    }

    /**
     * Get detailed monitoring state for a specific location.
     *
     * @param int $lokasiId
     * @return array<string, mixed>
     */
    public function getLocationDetail(int $lokasiId): array
    {
        $lokasi = Lokasi::with('onlineMonitoring')->findOrFail($lokasiId);
        $device = $lokasi->onlineMonitoring;
        $serverNow = Carbon::now();

        $statusData = $this->calculateStatus($device?->last_update, $serverNow);
        $downtimeData = $this->calculateDowntime($device, $serverNow);

        $lastUpdateAbsolute = '-';
        $lastUpdateRelative = 'Belum pernah update';
        $lastUpdateIso = null;

        if ($device?->last_update) {
            $lastUpdateIso = $device->last_update->toIso8601String();
            $lastUpdateAbsolute = $device->last_update->locale('id')->isoFormat('D MMMM Y, HH:mm:ss');
            $lastUpdateRelative = $device->last_update->locale('id')->diffForHumans($serverNow);
        }

        return [
            'id' => $device?->id ?? $lokasi->id,
            'lokasi_id' => $lokasi->id,
            'location' => $lokasi->nama_lokasi,
            'address' => $lokasi->keterangan_alamat ?: '-',
            'network' => $device?->jaringan ?: '-',
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
            'is_incomplete_period' => $downtimeData['is_incomplete_period'],
            'monitored_hours' => $downtimeData['monitored_hours'],
        ];
    }

    /**
     * Retrieve monitoring history logs for a location with optional date filtering.
     *
     * @param int $lokasiId
     * @param string|null $date
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $limit
     * @return array<string, mixed>
     */
    public function getLocationHistory(
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

        // Aggregated period metrics
        $periodSeconds = !empty($startDate) && !empty($endDate)
            ? max(86400, Carbon::parse($startDate)->diffInSeconds(Carbon::parse($endDate)->endOfDay()))
            : (30 * 86400); // 30 days default context

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
     *
     * @param int $lokasiId
     * @param array{
     *   jaringan?: string|null,
     *   ip_address?: string|null,
     *   status?: string|null,
     *   timestamp?: string|Carbon|null,
     *   notes?: string|null
     * } $data
     * @return OnlineMonitoring
     */
    public function recordPing(int $lokasiId, array $data = []): OnlineMonitoring
    {
        $lokasi = Lokasi::findOrFail($lokasiId);
        $now = isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : Carbon::now();

        $device = OnlineMonitoring::firstOrNew(['lokasi_id' => $lokasiId]);

        $previousStatus = $this->calculateStatus($device->last_update, $now);

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

        $device->last_update = $now;
        $device->save();

        // If previously down or troubled, record recovery event
        if ($previousStatus['code'] !== 'online' && $device->wasRecentlyCreated === false) {
            $downtimeStart = $device->last_update
                ? $device->last_update->copy()->addSeconds(self::ONLINE_THRESHOLD_SECONDS)
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

        return $device;
    }
}

