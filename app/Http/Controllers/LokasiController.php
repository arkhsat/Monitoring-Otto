<?php

namespace App\Http\Controllers;

use App\Http\Requests\LokasiRequest;
use App\Models\Lokasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LokasiController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index(): View
    {
        $lokasis = Lokasi::orderBy('created_at', 'desc')->get();

        return view('pages.lokasi.index', compact('lokasis'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(LokasiRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Remove empty keys if any so model boot/defaults kick in
        if (empty($validated['uuid_lokasi'])) {
            unset($validated['uuid_lokasi']);
        }
        if (empty($validated['api_lokasi'])) {
            unset($validated['api_lokasi']);
        }

        Lokasi::create($validated);

        return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi baru berhasil ditambahkan! UUID dan API Lokasi otomatis di-hash.');
    }

    /**
     * Update the specified location in storage.
     */
    public function update(LokasiRequest $request, int $id): RedirectResponse
    {
        $lokasi = Lokasi::findOrFail($id);
        $validated = $request->validated();

        // Do not overwrite existing hash if user left the fields blank in edit form
        if (empty($validated['uuid_lokasi'])) {
            unset($validated['uuid_lokasi']);
        }
        if (empty($validated['api_lokasi'])) {
            unset($validated['api_lokasi']);
        }

        $lokasi->update($validated);

        return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();

        return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi berhasil dihapus!');
    }
}
