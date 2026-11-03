<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MonitoringPingRequest;
use App\Services\OnlineMonitoringService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineMonitoringApiController extends Controller
{
    public function __construct(
        protected OnlineMonitoringService $monitoringService
    ) {}

    /**
     * Retrieve all onsite online monitoring data, summary counters, and server time.
     *
     * GET /api/online-monitoring
     */
    public function index(): JsonResponse
    {
        try {
            $result = $this->monitoringService->getAllMonitoringData();

            return response()->json([
                'success' => true,
                'message' => 'Data online monitoring berhasil diambil',
                'server_time' => $result['server_time'],
                'summary' => $result['summary'],
                'data' => $result['items'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data monitoring: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retrieve monitoring details for a specific location.
     *
     * GET /api/online-monitoring/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->monitoringService->getLocationDetail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail monitoring lokasi berhasil diambil',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi monitoring tidak ditemukan: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Retrieve monitoring history logs with optional date range filters.
     *
     * GET /api/online-monitoring/{id}/history
     */
    public function history(Request $request, int $id): JsonResponse
    {
        try {
            $date = $request->query('date');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $limit = (int) $request->query('limit', 50);

            $result = $this->monitoringService->getLocationHistory(
                lokasiId: $id,
                date: $date,
                startDate: $startDate,
                endDate: $endDate,
                limit: $limit
            );

            return response()->json([
                'success' => true,
                'message' => 'Riwayat monitoring lokasi berhasil diambil',
                'location' => $result['location'],
                'metrics' => $result['metrics'],
                'data' => $result['records'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat monitoring: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Retrieve downtime information and breakdown for a specific location.
     *
     * GET /api/online-monitoring/{id}/downtime
     */
    public function downtime(Request $request, int $id): JsonResponse
    {
        try {
            $detail = $this->monitoringService->getLocationDetail($id);

            return response()->json([
                'success' => true,
                'message' => 'Informasi downtime lokasi berhasil diambil',
                'data' => [
                    'lokasi_id' => $detail['lokasi_id'],
                    'location' => $detail['location'],
                    'status' => $detail['status'],
                    'downtime_seconds' => $detail['downtime_seconds'],
                    'uptime_seconds' => $detail['uptime_seconds'],
                    'downtime_formatted' => $detail['downtime_formatted'],
                    'uptime_percentage' => $detail['uptime_percentage'],
                    'downtime_percentage' => $detail['downtime_percentage'],
                    'monitored_period_seconds' => $detail['monitored_period_seconds'],
                    'is_incomplete_period' => $detail['is_incomplete_period'],
                    'monitored_hours' => $detail['monitored_hours'],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data downtime: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Ingest heartbeat/ping update from onsite device or probe.
     *
     * POST /api/online-monitoring/ping
     */
    public function ping(MonitoringPingRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $device = $this->monitoringService->recordPing((int) $validated['lokasi_id'], $validated);
            $detail = $this->monitoringService->getLocationDetail($device->lokasi_id);

            return response()->json([
                'success' => true,
                'message' => 'Heartbeat monitoring berhasil dicatat',
                'data' => $detail,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses ping: ' . $e->getMessage(),
            ], 422);
        }
    }
}

