<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SettlementController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService
    ) {}

    /**
     * Ambil data file settlement (.txt & .ok) berdasarkan lokasi_id dan tanggal_settlement.
     *
     * GET /api/settlements
     */
    public function index(Request $request): JsonResponse
    {
        $lokasiId = $request->query('lokasi_id', $request->query('kode'));
        $tanggal = $request->query('tanggal_settlement', $request->query('date'));

        if (!empty($lokasiId) && !empty($tanggal)) {
            $record = $this->settlementService->findByLocationAndDate((int) $lokasiId, (string) $tanggal);

            return response()->json([
                'success' => true,
                'message' => $record
                    ? 'Data file settlement berhasil diambil.'
                    : 'File settlement belum tersedia untuk lokasi dan tanggal ini.',
                'data' => $record,
            ]);
        }

        $filters = array_filter([
            'lokasi_id' => $lokasiId,
            'tanggal_settlement' => $tanggal,
        ]);

        $records = $this->settlementService->list($filters);

        return response()->json([
            'success' => true,
            'total' => count($records),
            'data' => $records,
        ]);
    }

    /**
     * Terima upload file settlement (.txt dan/atau .ok) melalui RESTful API.
     * Mendukung multipart/form-data (file_txt, file_ok) maupun JSON (file_txt_content, file_ok_content).
     *
     * POST /api/settlements
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lokasi_id' => ['required', 'integer', 'min:1'],
            'tanggal_settlement' => ['required', 'date_format:Y-m-d'],
            'file_txt' => ['nullable', 'file', 'max:10240'],
            'file_ok' => ['nullable', 'file', 'max:10240'],
            'file_txt_name' => ['nullable', 'string', 'max:255'],
            'file_txt_path' => ['nullable', 'string', 'max:500'],
            'file_txt_content' => ['nullable', 'string'],
            'file_ok_name' => ['nullable', 'string', 'max:255'],
            'file_ok_path' => ['nullable', 'string', 'max:500'],
            'file_ok_content' => ['nullable', 'string'],
        ], [
            'lokasi_id.required' => 'lokasi_id wajib diisi.',
            'tanggal_settlement.required' => 'tanggal_settlement wajib diisi (format YYYY-MM-DD).',
            'tanggal_settlement.date_format' => 'Format tanggal_settlement harus YYYY-MM-DD.',
        ]);

        $hasTxt = $request->hasFile('file_txt') || !empty($validated['file_txt_content']) || !empty($validated['file_txt_path']);
        $hasOk = $request->hasFile('file_ok') || !empty($validated['file_ok_content']) || !empty($validated['file_ok_path']);

        if (!$hasTxt && !$hasOk) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal kirim file .txt atau file .ok.',
            ], 422);
        }

        $result = $this->settlementService->store(
            $validated,
            $request->file('file_txt'),
            $request->file('file_ok')
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data' => $result['data'],
        ], $result['status']);
    }

    /**
     * Unduh atau tampilkan isi file .txt / .ok dari database atau storage.
     *
     * GET /api/settlements/download?lokasi_id=1&tanggal_settlement=2026-11-10&type=txt
     */
    public function download(Request $request): Response
    {
        $lokasiId = (int) $request->query('lokasi_id', $request->query('kode', 0));
        $tanggal = (string) $request->query('tanggal_settlement', $request->query('date', ''));
        $type = strtolower((string) $request->query('type', 'txt'));

        if ($lokasiId <= 0 || $tanggal === '' || !in_array($type, ['txt', 'ok'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter lokasi_id, tanggal_settlement, dan type (txt/ok) wajib valid.',
            ], 422);
        }

        $record = $this->settlementService->findByLocationAndDate($lokasiId, $tanggal);
        $fileData = $record[$type === 'txt' ? 'file_txt' : 'file_ok'] ?? null;

        if (!$record || !$fileData || empty($fileData['exists'])) {
            return response()->json([
                'success' => false,
                'message' => "File .{$type} tidak ditemukan untuk lokasi {$lokasiId} pada {$tanggal}.",
            ], 404);
        }

        $filename = basename((string) ($fileData['name'] ?: "settlement_{$tanggal}.{$type}"));
        $disposition = $request->boolean('inline') ? 'inline' : 'attachment';

        if ($fileData['content'] !== null) {
            return response((string) $fileData['content'], 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            ]);
        }

        $path = (string) ($fileData['path'] ?? '');
        if ($path !== '' && preg_match('/^https?:\/\//i', $path)) {
            try {
                $remoteRes = Http::timeout(10)->get($path);
                if ($remoteRes->successful()) {
                    return response($remoteRes->body(), 200, [
                        'Content-Type' => 'text/plain; charset=UTF-8',
                        'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
                    ]);
                }
            } catch (\Throwable $e) {
                //
            }
        }

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            $content = Storage::disk('public')->get($path);
            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => "Konten file .{$type} belum tersedia.",
        ], 404);
    }
}

