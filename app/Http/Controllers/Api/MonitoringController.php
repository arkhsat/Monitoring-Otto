<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MonitoringResource;
use App\Models\MonitoringData;
use App\Models\Lokasi;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Mengambil data rekonsiliasi monitoring
     */
    public function index(Request $request)
    {
        $kode = (int) $request->query('kode', 1);
        $date = $request->query('date');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $month = $request->query('month');
        $year = $request->query('year');

        $query = MonitoringData::where('Kode', $kode);

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereDate('Tanggal', '>=', $startDate)
                  ->whereDate('Tanggal', '<=', $endDate);
        } elseif (!empty($startDate)) {
            $query->whereDate('Tanggal', '>=', $startDate);
        } elseif (!empty($endDate)) {
            $query->whereDate('Tanggal', '<=', $endDate);
        } elseif (!empty($date)) {
            $query->whereDate('Tanggal', $date);
        } elseif (!empty($month) && !empty($year)) {
            $query->whereYear('Tanggal', $year)
                  ->whereMonth('Tanggal', $month);
        } elseif (!empty($year)) {
            $query->whereYear('Tanggal', $year);
        } elseif (!empty($month)) {
            $query->whereMonth('Tanggal', $month);
        }

        $records = $query->orderBy('Tanggal', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data Monitoring Berhasil Diambil',
            'data'    => MonitoringResource::collection($records),
        ]);
    }

    /**
     * Mengambil daftar lokasi untuk filter dropdown
     */
    public function lokasi()
    {
        $dbLocations = Lokasi::orderBy('id', 'asc')->get();

        if ($dbLocations->isNotEmpty()) {
            $locations = $dbLocations->map(function ($loc) {
                return [
                    'id' => $loc->id,
                    'kode' => $loc->id,
                    'name' => $loc->nama_lokasi,
                    'address' => $loc->keterangan_alamat ?: '-',
                    'batas_waktu_henti' => $loc->batas_waktu_henti ?? 60,
                ];
            })->toArray();
        } else {
            $locations = config('monitoring.default_locations', []);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi Berhasil Diambil',
            'data'    => $locations,
        ]);
    }
}
