<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LokasiRequest;
use App\Http\Resources\LokasiResource;
use App\Models\Lokasi;
use App\Services\RemoteLokasiService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LokasiApiController extends Controller
{
    protected RemoteLokasiService $remoteService;

    public function __construct(RemoteLokasiService $remoteService)
    {
        $this->remoteService = $remoteService;
    }

    /**
     * Display a listing of locations with optional search filter.
     * Connects to Remote RESTful API on domain, with local sync.
     *
     * GET /api/lokasi
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $result = $this->remoteService->getAll($search);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'source'  => $result['source'] ?? 'remote',
            'total'   => count($result['data']),
            'data'    => $result['data'],
        ]);
    }

    /**
     * Store a newly created location on remote domain API.
     *
     * POST /api/lokasi
     */
    public function store(LokasiRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Biarkan kosong jika tidak diinput
            if (empty($validated['uuid_lokasi'])) {
                unset($validated['uuid_lokasi']);
            }
            if (empty($validated['api_lokasi'])) {
                unset($validated['api_lokasi']);
            }

            $result = $this->remoteService->create($validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Lokasi baru berhasil ditambahkan ke database domain!',
                'data' => $result['data'],
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
     * Update the specified location in storage on remote domain API.
     *
     * PUT/PATCH /api/lokasi/{id}
     */
    public function update(LokasiRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (empty($validated['uuid_lokasi'])) {
                unset($validated['uuid_lokasi']);
            }
            if (empty($validated['api_lokasi'])) {
                unset($validated['api_lokasi']);
            }

            $result = $this->remoteService->update($id, $validated);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Lokasi berhasil diperbarui di database domain!',
                'data' => $result['data'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui lokasi: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified location from remote domain API.
     *
     * DELETE /api/lokasi/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->remoteService->delete($id);

            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Lokasi berhasil dihapus dari database domain!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus lokasi: ' . $e->getMessage(),
            ], 404);
        }
    }
}
