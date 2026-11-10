<?php

namespace App\Services;

use App\Models\Lokasi;
use App\Models\OnlineMonitoring;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RemoteLokasiService
{
    protected string $baseUrl;
    protected string $token;
    protected string $endpoint;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.monitoring_api.base_url'), '/');
        $this->token = (string) config('services.monitoring_api.token');

        // Pastikan endpoint mengarah ke file /lokasi.php di domain
        $this->endpoint = str_ends_with($this->baseUrl, '.php')
            ? $this->baseUrl
            : "{$this->baseUrl}/lokasi.php";
    }

    /**
     * Helper HTTP client dengan Bearer Token dan Timeout
     */
    protected function client()
    {
        return Http::timeout(10)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->token}",
                'X-API-TOKEN' => $this->token,
            ]);
    }

    /**
     * Mengambil seluruh data lokasi dari RESTful API domain
     * dan menyinkronkannya ke database lokal.
     *
     * @param string|null $search
     * @return array{success: bool, message: string, source: string, data: array}
     */
    public function getAll(?string $search = null): array
    {
        try {
            $params = [];
            if (!empty($search)) {
                $params['search'] = $search;
            }

            $response = $this->client()->get($this->endpoint, $params);

            if ($response->successful()) {
                $json = $response->json();

                // Pastikan format response valid
                if (isset($json['success']) && $json['success'] && isset($json['data'])) {
                    $items = (array) $json['data'];

                    // Sinkronkan ke database lokal
                    $this->syncToLocal($items);

                    return [
                        'success' => true,
                        'message' => $json['message'] ?? 'Data lokasi berhasil diambil dari domain remote.',
                        'source' => 'remote',
                        'data' => $items,
                    ];
                }
            }

            Log::warning("Remote Lokasi API returned non-success: Status {$response->status()} - Body: " . $response->body());
            return $this->fallbackLocal($search, 'Gagal terhubung ke database domain (' . $response->status() . '). Menampilkan data cache lokal.');

        } catch (Exception $e) {
            Log::error("Remote Lokasi API Connection Error: " . $e->getMessage());
            return $this->fallbackLocal($search, 'Gagal terhubung ke API domain: ' . $e->getMessage());
        }
    }

    /**
     * Tambah lokasi baru ke RESTful API domain
     *
     * @param array $data
     * @return array{success: bool, message: string, data: mixed}
     */
    public function create(array $data): array
    {
        try {
            $response = $this->client()->post($this->endpoint, $data);
            $json = $response->json();

            if ($response->successful() && (!isset($json['success']) || $json['success'] === true)) {
                $remoteId = $json['id'] ?? ($json['data']['id'] ?? null);

                // Buat juga di database lokal
                $localData = $data;
                if ($remoteId) {
                    $localData['id'] = $remoteId;
                }
                $lokasi = Lokasi::create($localData);

                // Inisialisasi online monitoring untuk lokasi ini
                OnlineMonitoring::firstOrCreate(
                    ['lokasi_id' => $lokasi->id],
                    [
                        'jaringan' => '192.168.' . ($lokasi->id * 10) . '.1 / DHCP Network',
                        'first_monitored_at' => Carbon::now(),
                    ]
                );

                return [
                    'success' => true,
                    'message' => $json['message'] ?? 'Lokasi baru berhasil ditambahkan ke database domain!',
                    'data' => $lokasi,
                ];
            }

            $errMsg = $json['message'] ?? ($json['error'] ?? 'Gagal menambahkan lokasi ke server domain.');
            throw new Exception($errMsg);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Perbarui data lokasi di RESTful API domain
     *
     * @param int $id
     * @param array $data
     * @return array{success: bool, message: string, data: mixed}
     */
    public function update(int $id, array $data): array
    {
        try {
            $url = "{$this->endpoint}?id={$id}";
            $response = $this->client()->put($url, $data);
            $json = $response->json();

            if ($response->successful() && (!isset($json['success']) || $json['success'] === true)) {
                // Perbarui juga di database lokal
                $lokasi = Lokasi::find($id);
                if ($lokasi) {
                    $lokasi->update($data);
                }

                return [
                    'success' => true,
                    'message' => $json['message'] ?? 'Lokasi berhasil diperbarui di database domain!',
                    'data' => $lokasi,
                ];
            }

            $errMsg = $json['message'] ?? ($json['error'] ?? 'Gagal memperbarui lokasi di server domain.');
            throw new Exception($errMsg);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Hapus lokasi dari RESTful API domain
     *
     * @param int $id
     * @return array{success: bool, message: string}
     */
    public function delete(int $id): array
    {
        try {
            $url = "{$this->endpoint}?id={$id}";
            $response = $this->client()->delete($url);
            $json = $response->json();

            if ($response->successful() && (!isset($json['success']) || $json['success'] === true)) {
                // Hapus dari database lokal
                $lokasi = Lokasi::find($id);
                if ($lokasi) {
                    $lokasi->delete();
                }

                return [
                    'success' => true,
                    'message' => $json['message'] ?? 'Lokasi berhasil dihapus dari database domain!',
                ];
            }

            $errMsg = $json['message'] ?? ($json['error'] ?? 'Gagal menghapus lokasi dari server domain.');
            throw new Exception($errMsg);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Sinkronkan data dari API remote ke database lokal
     *
     * @param array $items
     */
    protected function syncToLocal(array $items): void
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (empty($item['id'])) {
                continue;
            }

            $id = (int) $item['id'];
            $lokasi = Lokasi::find($id);

            $attributes = [
                'nama_lokasi' => $item['nama_lokasi'] ?? '',
                'keterangan_alamat' => $item['keterangan_alamat'] ?? null,
                'batas_waktu_henti' => (int) ($item['batas_waktu_henti'] ?? 60),
            ];

            if (!$lokasi) {
                $attributes['id'] = $id;
                if (!empty($item['uuid_lokasi'])) {
                    $attributes['uuid_lokasi'] = $item['uuid_lokasi'];
                }
                if (!empty($item['api_lokasi'])) {
                    $attributes['api_lokasi'] = $item['api_lokasi'];
                }
                $lokasi = Lokasi::create($attributes);
            } else {
                $lokasi->update($attributes);
            }

            // Pastikan entri online monitoring selalu tersedia untuk relasi dashboard
            OnlineMonitoring::firstOrCreate(
                ['lokasi_id' => $lokasi->id],
                [
                    'jaringan' => '192.168.' . ($lokasi->id * 10) . '.1 / DHCP Network',
                    'first_monitored_at' => Carbon::now(),
                ]
            );
        }
    }

    /**
     * Fallback membaca data lokal jika API remote sedang down/error
     *
     * @param string|null $search
     * @param string $message
     * @return array
     */
    protected function fallbackLocal(?string $search, string $message): array
    {
        $query = Lokasi::orderBy('id', 'asc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lokasi', 'LIKE', "%{$search}%")
                  ->orWhere('keterangan_alamat', 'LIKE', "%{$search}%");
            });
        }

        $lokasis = $query->get()->map(function ($loc) {
            return [
                'id' => $loc->id,
                'nama_lokasi' => $loc->nama_lokasi,
                'uuid_lokasi' => $loc->uuid_lokasi,
                'api_lokasi' => $loc->api_lokasi,
                'keterangan_alamat' => $loc->keterangan_alamat ?: '-',
                'batas_waktu_henti' => (int) $loc->batas_waktu_henti,
            ];
        })->toArray();

        return [
            'success' => true,
            'message' => $message,
            'source' => 'local_fallback',
            'data' => $lokasis,
        ];
    }
}

