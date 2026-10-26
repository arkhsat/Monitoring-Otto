<?php

namespace App\Services;

use App\Models\Lokasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LocationService
{
    /**
     * Get locations from database table `lokasis`, with fallback to config.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLocations(): array
    {
        $dbLocations = Lokasi::orderBy('id', 'asc')->get();

        if ($dbLocations->isNotEmpty()) {
            return $dbLocations->map(function ($loc) {
                return [
                    'id' => $loc->id,
                    'kode' => $loc->id,
                    'name' => $loc->nama_lokasi,
                    'address' => $loc->keterangan_alamat ?: '-',
                    'batas_waktu_henti' => $loc->batas_waktu_henti ?? 60,
                ];
            })->toArray();
        }

        return config('monitoring.default_locations', []);
    }

    /**
     * Format a database row from the `data` table into a clean UI array.
     *
     * @param object $row
     * @return array<string, mixed>
     */
    public function formatRecord(object $row): array
    {
        $rawDate = $row->Tanggal ?? null;
        $admin = (int) ($row->Admin ?? 0);
        $mdd = (int) ($row->Settlement ?? 0);
        $report = (int) ($row->Lokal ?? 0);
        $kode = $row->Kode ?? null;

        $dateFormatted = '-';
        $isoDate = '';
        if ($rawDate) {
            $carbon = Carbon::parse($rawDate)->locale('id');
            $dateFormatted = $carbon->format('d/m/Y');
            $isoDate = $carbon->format('Y-m-d');
        }

        $isMatch = ($admin === $mdd && $mdd === $report);
        $status = $isMatch ? 'Cocok' : 'Tidak Cocok';

        return [
            'date' => $dateFormatted,
            'iso_date' => $isoDate,
            'admin' => number_format($admin, 0, ',', '.'),
            'mdd' => number_format($mdd, 0, ',', '.'),
            'report' => number_format($report, 0, ',', '.'),
            'status' => $status,
            'is_match' => $isMatch,
            'kode' => $kode,
        ];
    }

    /**
     * Fetch and format reconciliation records for a location code and optional date.
     *
     * @param int $kode
     * @param string|null $date
     * @return array<int, array<string, mixed>>
     */
    public function getRecordsByLocation(int $kode, ?string $date = null): array
    {
        $query = DB::table('data')->where('Kode', $kode);

        if (!empty($date)) {
            $query->whereDate('Tanggal', $date);
        }

        $rawRecords = $query->orderBy('Tanggal', 'desc')->get();

        $records = [];
        foreach ($rawRecords as $row) {
            $records[] = $this->formatRecord($row);
        }

        return $records;
    }
}
