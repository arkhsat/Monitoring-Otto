<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    protected RemoteLokasiService $lokasiService;
    protected string $defaultBaseUrl;
    protected string $defaultToken;

    public function __construct(RemoteLokasiService $lokasiService)
    {
        $this->lokasiService = $lokasiService;
        $baseUrl = rtrim((string) config('services.monitoring_api.base_url'), '/');
        // Default URL transaksi ke /transactions.php di domain
        $this->defaultBaseUrl = preg_replace('/\/lokasi\.php$/', '', $baseUrl) . '/transactions.php';
        $this->defaultToken = (string) config('services.monitoring_api.token');
    }

    /**
     * Mengambil dan merekonsiliasi data transaksi dari 3 database (Admin Otto, Settlement MDD, Report Lokasi)
     *
     * @param int $locationId
     * @param array $filter
     * @return array<int, array<string, mixed>>
     */
    public function getReconciliationData(int $locationId, array $filter = []): array
    {
        $lokasi = $this->lokasiService->getById($locationId);
        if (!$lokasi) {
            return [];
        }

        // 1. Tentukan URL & Token untuk masing-masing sumber
        $ottoConfig = [
            'url'   => !empty($lokasi['api_url_otto']) ? $lokasi['api_url_otto'] : $this->defaultBaseUrl,
            'token' => !empty($lokasi['api_token_otto']) ? $lokasi['api_token_otto'] : $this->defaultToken,
        ];

        $mddConfig = [
            'url'   => !empty($lokasi['api_url_mdd']) ? $lokasi['api_url_mdd'] : $this->defaultBaseUrl,
            'token' => !empty($lokasi['api_token_mdd']) ? $lokasi['api_token_mdd'] : $this->defaultToken,
        ];

        $lokasiConfig = [
            'url'   => !empty($lokasi['api_url_lokasi']) ? $lokasi['api_url_lokasi'] : $this->defaultBaseUrl,
            'token' => !empty($lokasi['api_token_lokasi']) ? $lokasi['api_token_lokasi'] : $this->defaultToken,
        ];

        // 2. Siapkan parameter query tanggal (dengan mode=summary untuk agregasi cepat)
        $queryParams = $this->buildQueryParams($filter);

        // 3. Ambil data transaksi dari 3 sumber secara paralel via Http::pool()
        $rawDatasets = $this->fetchAllSourcesParallel(
            $ottoConfig,
            $mddConfig,
            $lokasiConfig,
            $locationId,
            $queryParams
        );

        // 4. Agregasi nominal per tanggal (mendukung output summary maupun raw rows)
        $ottoAgg   = $this->aggregateDailySums($rawDatasets['otto']);
        $mddAgg    = $this->aggregateDailySums($rawDatasets['mdd']);
        $lokasiAgg = $this->aggregateDailySums($rawDatasets['lokasi']);

        // 5. Kumpulkan seluruh tanggal unik lalu urutkan terbaru lebih dulu
        $allDates = $this->extractAllDates($filter, $ottoAgg['has_data'], $mddAgg['has_data'], $lokasiAgg['has_data']);

        if (empty($allDates)) {
            return [];
        }

        rsort($allDates);

        // 6. Susun record tabel rekonsiliasi per tanggal
        $records = [];
        foreach ($allDates as $isoDate) {
            $ottoSum   = $ottoAgg['sums'][$isoDate] ?? 0;
            $mddSum    = $mddAgg['sums'][$isoDate] ?? 0;
            $lokasiSum = $lokasiAgg['sums'][$isoDate] ?? 0;

            $hasOttoData   = !empty($ottoAgg['has_data'][$isoDate]);
            $hasMddData    = !empty($mddAgg['has_data'][$isoDate]) || $mddSum > 0;
            $hasLokasiData = !empty($lokasiAgg['has_data'][$isoDate]);

            $hasAnyTransactions = ($hasOttoData || $hasMddData || $hasLokasiData || $ottoSum > 0 || $lokasiSum > 0);
            if (!$hasAnyTransactions && empty($filter['date'])) {
                continue;
            }

            // $isMatch = ($hasMddData && $ottoSum === $mddSum && $mddSum === $lokasiSum);
            $qris = $this->getQris($ottoSum, $isoDate);
            $shouldAddQris = ($mddSum !== $lokasiSum);
            $mddAdjustedSum = $shouldAddQris
                ? $mddSum + $qris
                : $mddSum;
            $isMatch = 
            (
                $hasMddData &&
                $ottoSum === $mddAdjustedSum &&
                $mddAdjustedSum === $lokasiSum
            );
            $status  = $isMatch ? 'Cocok' : 'Tidak Cocok';

            $dateFormatted = Carbon::parse($isoDate)->locale('id')->format('d/m/Y');

            $records[] = [
                'kode'       => $locationId,
                'date'       => $dateFormatted,
                'iso_date'   => $isoDate,
                'admin'      => number_format($ottoSum, 0, ',', '.'),
                'mdd'        => $hasMddData ? number_format($mddSum, 0, ',', '.') : '-',
                'settlement' => $hasMddData ? number_format($mddSum, 0, ',', '.') : '-',
                'report'     => number_format($lokasiSum, 0, ',', '.'),
                'lokal'      => number_format($lokasiSum, 0, ',', '.'),
                'status'     => $status,
                'is_match'   => $isMatch,
            ];
        }

        return $records;
    }

    /**
     * Ambil data 3 sumber secara paralel menggunakan Http::pool(), dengan fallback sekuensial.
     *
     * @return array{otto: array, mdd: array, lokasi: array}
     */
    protected function fetchAllSourcesParallel(
        array $ottoConfig,
        array $mddConfig,
        array $lokasiConfig,
        int $locationId,
        array $queryParams
    ): array {
        $ottoParams   = array_merge($queryParams, ['source' => 'otto', 'sumber_id' => 1, 'location_id' => $locationId]);
        $mddParams    = array_merge($queryParams, ['source' => 'mdd', 'sumber_id' => 2, 'location_id' => $locationId]);
        $lokasiParams = array_merge($queryParams, ['source' => 'lokasi', 'sumber_id' => 1, 'location_id' => $locationId]);

        try {
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->as('otto')->timeout(12)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$ottoConfig['token']}",
                    'X-API-TOKEN' => $ottoConfig['token'],
                ])->get($ottoConfig['url'], $ottoParams),
                $pool->as('mdd')->timeout(12)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$mddConfig['token']}",
                    'X-API-TOKEN' => $mddConfig['token'],
                ])->get($mddConfig['url'], $mddParams),
                $pool->as('lokasi')->timeout(12)->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$lokasiConfig['token']}",
                    'X-API-TOKEN' => $lokasiConfig['token'],
                ])->get($lokasiConfig['url'], $lokasiParams),
            ]);

            return [
                'otto'   => $this->extractPoolResponse($responses, 'otto', $ottoConfig['url'], 'Admin Otto'),
                'mdd'    => $this->extractPoolResponse($responses, 'mdd', $mddConfig['url'], 'Settlement MDD'),
                'lokasi' => $this->extractPoolResponse($responses, 'lokasi', $lokasiConfig['url'], 'Report Lokasi'),
            ];
        } catch (\Throwable $e) {
            Log::warning('Reconciliation Http::pool() gagal, fallback ke mode sekuensial: ' . $e->getMessage());

            return [
                'otto'   => $this->fetchRawTransactions($ottoConfig['url'], $ottoConfig['token'], $ottoParams, 'Admin Otto'),
                'mdd'    => $this->fetchRawTransactions($mddConfig['url'], $mddConfig['token'], $mddParams, 'Settlement MDD'),
                'lokasi' => $this->fetchRawTransactions($lokasiConfig['url'], $lokasiConfig['token'], $lokasiParams, 'Report Lokasi'),
            ];
        }
    }

    /**
     * Ekstrak array data dari hasil Http::pool().
     */
    protected function extractPoolResponse(array $responses, string $key, string $url, string $sourceLabel): array
    {
        $response = $responses[$key] ?? null;

        if ($response instanceof \Throwable) {
            Log::warning("Reconciliation API [{$sourceLabel}] pool error ({$url}): " . $response->getMessage());
            return [];
        }

        if ($response instanceof Response && $response->successful()) {
            $json = $response->json();
            if (isset($json['data']) && is_array($json['data'])) {
                return $json['data'];
            }
        }

        return [];
    }

    /**
     * Agregasi nominal transaksi harian dari format summary (date, total_cost) maupun raw (dateout, cost).
     *
     * @return array{sums: array<string, int>, has_data: array<string, bool>}
     */
    protected function aggregateDailySums(array $rows): array
    {
        $sums = [];
        $hasData = [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (!empty($row['date']) && array_key_exists('total_cost', $row)) {
                $isoDate = substr((string) $row['date'], 0, 10);
                $amount = (int) $row['total_cost'];
                $rowHasRecords = ((int) ($row['total_records'] ?? 1) > 0) || $amount > 0;
            } elseif (!empty($row['dateout'])) {
                $isoDate = substr((string) $row['dateout'], 0, 10);
                $amount = (int) ($row['cost'] ?? 0);
                $rowHasRecords = true;
            } else {
                continue;
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $isoDate)) {
                continue;
            }

            $sums[$isoDate] = ($sums[$isoDate] ?? 0) + $amount;
            if ($rowHasRecords) {
                $hasData[$isoDate] = true;
            }
        }

        return [
            'sums'     => $sums,
            'has_data' => $hasData,
        ];
    }

    /**
     * Memanggil REST API untuk mengambil transaksi (fallback sekuensial)
     *
     * @param string $url
     * @param string $token
     * @param array $params
     * @param string $sourceLabel
     * @return array
     */
    protected function fetchRawTransactions(string $url, string $token, array $params, string $sourceLabel): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer {$token}",
                    'X-API-TOKEN' => $token,
                ])
                ->get($url, $params);

            if ($response->successful()) {
                $json = $response->json();
                if (isset($json['data']) && is_array($json['data'])) {
                    return $json['data'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Reconciliation API [{$sourceLabel}] error ({$url}): " . $e->getMessage());
        }

        return [];
    }

    /**
     * Bangun parameter query untuk request API transaksi
     */
    protected function buildQueryParams(array $filter): array
    {
        $params = [
            'mode' => 'summary',
        ];

        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $params['start_date'] = $filter['start_date'];
            $params['end_date']   = $filter['end_date'];
        } elseif (!empty($filter['date'])) {
            $params['date'] = $filter['date'];
        } elseif (!empty($filter['month']) && !empty($filter['year'])) {
            $params['month'] = $filter['month'];
            $params['year']  = $filter['year'];
        } elseif (!empty($filter['year'])) {
            $params['year'] = $filter['year'];
        } elseif (!empty($filter['month'])) {
            $params['month'] = $filter['month'];
            $params['year']  = date('Y');
        } else {
            // Default "Semua Tanggal": ambil data hingga 1 tahun terakhir (365 hari) agar transaksi yang tersimpan
            // (misal data Maret 2026) langsung tampil. Karena mode=summary, query di DB sangat cepat & ringan.
            $params['start_date'] = Carbon::now()->subDays(365)->format('Y-m-d');
            $params['end_date']   = Carbon::now()->format('Y-m-d');
        }

        return $params;
    }

    /**
     * Helper sementara untuk QRIS agar tidak memicu fatal error method undefined.
     */
    protected function getQris(int $ottoSum, string $isoDate): int
    {
        return 0;
    }

    /**
     * Ekstrak seluruh tanggal unik dari filter atau hasil agregasi
     */
    protected function extractAllDates(array $filter, array $ottoDates, array $mddDates, array $lokasiDates): array
    {
        $dates = [];

        foreach ([$ottoDates, $mddDates, $lokasiDates] as $map) {
            foreach (array_keys($map) as $isoDate) {
                $dates[$isoDate] = true;
            }
        }

        if (!empty($filter['date'])) {
            $dates[$filter['date']] = true;
        }

        return array_keys($dates);
    }
}

