<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LokasiRequest;
use App\Http\Resources\LokasiResource;
use App\Models\Lokasi;
use App\Models\OnlineMonitoring;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LokasiApiController extends Controller
{
    /**
     * Display a listing of locations with optional search filter.
     *
     * GET /api/lokasi
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->query('search');

            $query = Lokasi::orderBy('id', 'asc');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lokasi', 'LIKE', "%{$search}%")
                      ->orWhere('keterangan_alamat', 'LIKE', "%{$search}%");
                });
            }

            $lokasis = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar lokasi berhasil diambil',
                'total' => $lokasis->count(),
                'data' => LokasiResource::collection($lokasis),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data lokasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created location.
     *
     * POST /api/lokasi
     */
    public function store(LokasiRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Let booted / cast handle empty uuid_lokasi and hashing
            if (empty($validated['uuid_lokasi'])) {
                unset($validated['uuid_lokasi']);
            }
            if (empty($validated['api_lokasi'])) {
                unset($validated['api_lokasi']);
            }

            $lokasi = Lokasi::create($validated);

            // Auto-initialize monitoring record for this location
            OnlineMonitoring::firstOrCreate(
                ['lokasi_id' => $lokasi->id],
                [
                    'jaringan' => '192.168.' . ($lokasi->id * 10) . '.1 / DHCP Network',
                    'first_monitored_at' => Carbon::now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Lokasi baru berhasil ditambahkan! UUID dan API Lokasi otomatis di-hash.',
                'data' => new LokasiResource($lokasi),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan lokasi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified location detail.
     *
     * GET /api/lokasi/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $lokasi = Lokasi::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail lokasi berhasil diambil',
                'data' => new LokasiResource($lokasi),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * Update the specified location in storage.
     *
     * PUT/PATCH /api/lokasi/{id}
     */
    public function update(LokasiRequest $request, int $id): JsonResponse
    {
        try {
            $lokasi = Lokasi::findOrFail($id);
            $validated = $request->validated();

            // Do not overwrite existing hash if left blank
            if (empty($validated['uuid_lokasi'])) {
                unset($validated['uuid_lokasi']);
            }
            if (empty($validated['api_lokasi'])) {
                unset($validated['api_lokasi']);
            }

            $lokasi->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lokasi berhasil diperbarui!',
                'data' => new LokasiResource($lokasi),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui lokasi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified location from storage.
     *
     * DELETE /api/lokasi/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $lokasi = Lokasi::findOrFail($id);
            $lokasi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lokasi berhasil dihapus!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus lokasi: ' . $e->getMessage(),
            ], 404);
        }
    }
}

