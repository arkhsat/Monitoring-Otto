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
        Lokasi::create($request->validated());

        return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi baru berhasil ditambahkan!');
    }

    /**
     * Update the specified location in storage.
     */
    public function update(LokasiRequest $request, int $id): RedirectResponse
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->update($request->validated());

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
