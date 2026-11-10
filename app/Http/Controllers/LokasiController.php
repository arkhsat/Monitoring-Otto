<?php

namespace App\Http\Controllers;

use App\Http\Requests\LokasiRequest;
use App\Models\Lokasi;
use App\Services\RemoteLokasiService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LokasiController extends Controller
{
    protected RemoteLokasiService $remoteService;

    public function __construct(RemoteLokasiService $remoteService)
    {
        $this->remoteService = $remoteService;
    }

    /**
     * Display a listing of locations.
     */
    public function index(): View
    {
        // Ambil data lokasi langsung dari API domain remote
        $result = $this->remoteService->getAll();
        $items = $result['data'] ?? [];

        $lokasis = collect($items)->map(function ($item) {
            return (object) $item;
        });

        return view('pages.lokasi.index', compact('lokasis'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(LokasiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['uuid_lokasi'])) {
            unset($validated['uuid_lokasi']);
        }
        if (empty($validated['api_lokasi'])) {
            unset($validated['api_lokasi']);
        }

        try {
            $this->remoteService->create($validated);
            return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi baru berhasil ditambahkan ke database domain!');
        } catch (Exception $e) {
            return redirect()->route('dashboard.lokasi')->with('error', 'Gagal menambahkan lokasi: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified location in storage.
     */
    public function update(LokasiRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        if (empty($validated['uuid_lokasi'])) {
            unset($validated['uuid_lokasi']);
        }
        if (empty($validated['api_lokasi'])) {
            unset($validated['api_lokasi']);
        }

        try {
            $this->remoteService->update($id, $validated);
            return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi berhasil diperbarui di database domain!');
        } catch (Exception $e) {
            return redirect()->route('dashboard.lokasi')->with('error', 'Gagal memperbarui lokasi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $this->remoteService->delete($id);
            return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi berhasil dihapus dari database domain!');
        } catch (Exception $e) {
            return redirect()->route('dashboard.lokasi')->with('error', 'Gagal menghapus lokasi: ' . $e->getMessage());
        }
    }
}
