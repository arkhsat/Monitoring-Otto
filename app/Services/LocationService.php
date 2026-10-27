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
            'settlement' => number_format($mdd, 0, ',', '.'),
            'report' => number_format($report, 0, ',', '.'),
            'lokal' => number_format($report, 0, ',', '.'),
            'status' => $status,
            'is_match' => $isMatch,
            'kode' => $kode,
        ];
    }

    /**
     * Fetch and format reconciliation records for a location code and optional date/range/month/year.
     *
     * @param int $kode
     * @param string|null $date
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $month
     * @param int|null $year
     * @return array<int, array<string, mixed>>
     */
    public function getRecordsByLocation(
        int $kode,
        ?string $date = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $month = null,
        ?int $year = null
    ): array {
        $query = DB::table('data')->where('Kode', $kode);

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

        $rawRecords = $query->orderBy('Tanggal', 'desc')->get();

        $records = [];
        foreach ($rawRecords as $row) {
            $records[] = $this->formatRecord($row);
        }

        return $records;
    }
}
