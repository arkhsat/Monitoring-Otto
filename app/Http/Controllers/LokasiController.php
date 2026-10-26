<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;

class LokasiController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index()
    {
        // Seed default locations if empty so the UI is immediately populated
        if (Lokasi::count() === 0) {
            $defaultLocations = [
                [
                    'nama_lokasi' => 'True Blue',
                    'keterangan_alamat' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, DKI Jakarta 10330',
                    'batas_waktu_henti' => 60,
                ],
                [
                    'nama_lokasi' => 'Gelael Tebet',
                    'keterangan_alamat' => 'Jl. Tebet Barat Dalam Raya No.1, RT.1/RW.4, Tebet Barat, Jakarta Selatan, DKI Jakarta 12810',
                    'batas_waktu_henti' => 120,
                ],
                [
                    'nama_lokasi' => 'Intermark BSD',
                    'keterangan_alamat' => 'Jl. Lingkar Timur No.9, Rawa Mekar Jaya, Serpong, Kota Tangerang Selatan, Banten 15310',
                    'batas_waktu_henti' => 45,
                ],
                [
                    'nama_lokasi' => 'Ciracas Center',
                    'keterangan_alamat' => 'Jl. Raya Ciracas No.12, RT.6/RW.3, Ciracas, Kota Jakarta Timur, DKI Jakarta 13740',
                    'batas_waktu_henti' => 90,
                ],
            ];

            foreach ($defaultLocations as $loc) {
                Lokasi::create($loc);
            }
        }

        $lokasis = Lokasi::orderBy('created_at', 'desc')->get();

        return view('dashboard.lokasi', compact('lokasis'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'keterangan_alamat' => 'nullable|string',
            'batas_waktu_henti' => 'required|integer|min:1',
        ]);

        Lokasi::create($validated);

        return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi baru berhasil ditambahkan!');
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'keterangan_alamat' => 'nullable|string',
            'batas_waktu_henti' => 'required|integer|min:1',
        ]);

        $lokasi = Lokasi::findOrFail($id);
        $lokasi->update($validated);

        return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi berhasil diperbarui!');
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();

        return redirect()->route('dashboard.lokasi')->with('success', 'Lokasi berhasil dihapus!');
    }
}

