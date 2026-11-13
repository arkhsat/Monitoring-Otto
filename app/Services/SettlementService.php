<?php

namespace App\Services;

use App\Models\Settlement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettlementService
{
    protected string $endpoint;
    protected string $token;

    public function __construct()
    {
        $baseUrl = rtrim((string) config('services.monitoring_api.base_url'), '/');
        $this->endpoint = preg_replace('/\/lokasi\.php$/', '', $baseUrl) . '/settlement.php';
        $this->token = (string) config('services.monitoring_api.token');
    }

    /**
     * Ambil daftar settlement dari API domain dengan fallback DB lokal.
     */
    public function list(array $filters = []): array
    {
        try {
            $response = $this->httpClient()->get($this->endpoint, $filters);

            if ($response->successful()) {
                $json = $response->json();
                if (!empty($json['success']) && isset($json['data'])) {
                    $items = is_array($json['data']) ? $json['data'] : [];
                    // Jika endpoint mengembalikan associative single object, bungkus menjadi list
                    if ($items !== [] && !array_is_list($items)) {
                        $items = [$items];
                    }

                    return array_map(fn (array $row) => $this->formatRecord($row), $items);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Remote settlement GET error: ' . $e->getMessage());
        }

        // Fallback ke tabel lokal `settlements` bila DB lokal aktif
        try {
            $query = Settlement::query();

            if (!empty($filters['lokasi_id'])) {
                $query->where('lokasi_id', (int) $filters['lokasi_id']);
            }
            if (!empty($filters['tanggal_settlement'])) {
                $query->whereDate('tanggal_settlement', $filters['tanggal_settlement']);
            }

            return $query->orderBy('tanggal_settlement', 'desc')
                ->get()
                ->map(fn (Settlement $item) => $this->formatRecord($item->toArray()))
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Ambil 1 data settlement berdasarkan lokasi_id dan tanggal (YYYY-MM-DD).
     */
    public function findByLocationAndDate(int $lokasiId, string $tanggalSettlement): ?array
    {
        $items = $this->list([
            'lokasi_id' => $lokasiId,
            'tanggal_settlement' => $tanggalSettlement,
        ]);

        return $items[0] ?? null;
    }

    /**
     * Simpan / kirim file settlement (.txt & .ok) ke API domain dan/atau DB lokal.
     */
    public function store(array $input, ?UploadedFile $txtFile = null, ?UploadedFile $okFile = null): array
    {
        $lokasiId = (int) ($input['lokasi_id'] ?? $input['location_id'] ?? 0);
        $tanggal = (string) ($input['tanggal_settlement'] ?? $input['tanggal'] ?? $input['date'] ?? '');

        $payload = [
            'lokasi_id' => $lokasiId,
            'tanggal_settlement' => $tanggal,
            'file_txt_name' => $input['file_txt_name'] ?? null,
            'file_txt_path' => $input['file_txt_path'] ?? null,
            'file_txt_content' => $input['file_txt_content'] ?? null,
            'file_ok_name' => $input['file_ok_name'] ?? null,
            'file_ok_path' => $input['file_ok_path'] ?? null,
            'file_ok_content' => $input['file_ok_content'] ?? null,
        ];

        if ($txtFile) {
            $payload['file_txt_name'] = $txtFile->getClientOriginalName();
            $payload['file_txt_content'] = file_get_contents($txtFile->getRealPath()) ?: '';
        }

        if ($okFile) {
            $payload['file_ok_name'] = $okFile->getClientOriginalName();
            $payload['file_ok_content'] = file_get_contents($okFile->getRealPath()) ?: '';
        }

        // 1. Kirim ke RESTful API domain
        try {
            $response = $this->httpClient()->post($this->endpoint, $payload);
            $json = $response->json();

            if ($response->successful() && (!isset($json['success']) || $json['success'] === true)) {
                $record = isset($json['data']) && is_array($json['data'])
                    ? $this->formatRecord($json['data'])
                    : $this->formatRecord($payload);

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'message' => $json['message'] ?? 'File settlement berhasil disimpan ke domain.',
                    'data' => $record,
                ];
            }

            if ($response->status() >= 400 && $response->status() < 500) {
                return [
                    'success' => false,
                    'status' => $response->status(),
                    'message' => $json['message'] ?? 'Gagal menyimpan settlement di API domain.',
                    'data' => null,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Remote settlement POST error: ' . $e->getMessage());
        }

        // 2. Fallback simpan ke database & storage lokal jika API domain tidak aktif/lokal DB aktif
        try {
            if ($txtFile) {
                $payload['file_txt_path'] = $txtFile->store("settlements/{$lokasiId}", 'public');
            } elseif (!empty($payload['file_txt_content']) && empty($payload['file_txt_path'])) {
                $txtName = $payload['file_txt_name'] ?: "settlement_{$tanggal}.txt";
                $path = "settlements/{$lokasiId}/{$tanggal}_{$txtName}";
                Storage::disk('public')->put($path, (string) $payload['file_txt_content']);
                $payload['file_txt_path'] = $path;
            }

            if ($okFile) {
                $payload['file_ok_path'] = $okFile->store("settlements/{$lokasiId}", 'public');
            } elseif (!empty($payload['file_ok_content']) && empty($payload['file_ok_path'])) {
                $okName = $payload['file_ok_name'] ?: "settlement_{$tanggal}.ok";
                $path = "settlements/{$lokasiId}/{$tanggal}_{$okName}";
                Storage::disk('public')->put($path, (string) $payload['file_ok_content']);
                $payload['file_ok_path'] = $path;
            }

            $existing = Settlement::where('lokasi_id', $lokasiId)
                ->whereDate('tanggal_settlement', $tanggal)
                ->first();

            $updateFields = [];
            foreach (['file_txt_name', 'file_txt_path', 'file_txt_content', 'file_ok_name', 'file_ok_path', 'file_ok_content'] as $col) {
                if ($payload[$col] !== null && $payload[$col] !== '') {
                    $updateFields[$col] = $payload[$col];
                }
            }

            if ($existing) {
                $existing->update($updateFields);
                $model = $existing->fresh();
            } else {
                $model = Settlement::create(array_merge([
                    'lokasi_id' => $lokasiId,
                    'tanggal_settlement' => $tanggal,
                ], $updateFields));
            }

            return [
                'success' => true,
                'status' => 201,
                'message' => 'File settlement berhasil disimpan ke database lokal.',
                'data' => $this->formatRecord($model->toArray()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'status' => 500,
                'message' => 'Gagal menyimpan file settlement: ' . $e->getMessage(),
                'data' => null,
            ];
        }
    }

    /**
     * Format struktur standar response Settlement untuk UI Modal & API.
     */
    public function formatRecord(array $row): array
    {
        $lokasiId = (int) ($row['lokasi_id'] ?? 0);
        $tanggal = substr((string) ($row['tanggal_settlement'] ?? ''), 0, 10);

        $txtName = $row['file_txt_name'] ?? null;
        $txtPath = $row['file_txt_path'] ?? null;
        $txtContent = $row['file_txt_content'] ?? null;
        $hasTxt = !empty($txtName) || !empty($txtPath) || ($txtContent !== null && $txtContent !== '');

        $okName = $row['file_ok_name'] ?? null;
        $okPath = $row['file_ok_path'] ?? null;
        $okContent = $row['file_ok_content'] ?? null;
        $hasOk = !empty($okName) || !empty($okPath) || ($okContent !== null && $okContent !== '');

        $downloadBase = "/api/settlements/download?lokasi_id={$lokasiId}&tanggal_settlement={$tanggal}";

        return [
            'id' => $row['id'] ?? null,
            'lokasi_id' => $lokasiId,
            'tanggal_settlement' => $tanggal,
            'file_txt' => [
                'exists' => $hasTxt,
                'name' => $txtName ?: ($hasTxt ? "settlement_{$tanggal}.txt" : null),
                'path' => $txtPath,
                'content' => $txtContent,
                'download_url' => $hasTxt ? "{$downloadBase}&type=txt" : null,
            ],
            'file_ok' => [
                'exists' => $hasOk,
                'name' => $okName ?: ($hasOk ? "settlement_{$tanggal}.ok" : null),
                'path' => $okPath,
                'content' => $okContent,
                'download_url' => $hasOk ? "{$downloadBase}&type=ok" : null,
            ],
            'updated_at' => $row['updated_at'] ?? null,
        ];
    }

    protected function httpClient()
    {
        return Http::timeout(12)->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
            'X-API-TOKEN' => $this->token,
        ]);
    }
}

