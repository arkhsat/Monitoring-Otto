<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lokasi;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $remoteUrl = rtrim((string) config('services.monitoring_api.base_url'), '/') . '/transactions.php';
        $token = (string) config('services.monitoring_api.token');

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Accept'        => 'application/json',
                ])
                ->get($remoteUrl, $request->all());

            if ($response->successful()) {
                return response()->json($response->json(), $response->status());
            }
        } catch (\Throwable $e) {
            Log::warning("Remote transactions GET error: " . $e->getMessage());
        }

        // Fallback local jika ada database lokal
        try {
            $query = Transaction::query();
            if ($request->filled('lokasi_id')) {
                $query->where('lokasi_id', $request->input('lokasi_id'));
            }
            if ($request->filled('date')) {
                $query->whereDate('dateout', $request->input('date'));
            }
            $data = $query->orderBy('dateout', 'desc')->get();
            return response()->json([
                'success'       => true,
                'total_records' => $data->count(),
                'data'          => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created transaction via RESTful API.
     */
    public function store(Request $request): JsonResponse
    {
        $remoteUrl = rtrim((string) config('services.monitoring_api.base_url'), '/') . '/transactions.php';
        $token = (string) config('services.monitoring_api.token');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ])
                ->post($remoteUrl, $request->all());

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            Log::error("Remote transactions POST error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungkan ke server API transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}

