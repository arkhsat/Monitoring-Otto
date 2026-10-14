<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MonitoringData;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Predefined locations with their associated code (Kode in table `data`).
     */
    protected function getLocations(): array
    {
        return [
            [
                'id' => 1,
                'kode' => 1,
                'name' => 'True Blue',
                'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            ],
            [
                'id' => 2,
                'kode' => 2,
                'name' => 'Gelael Tebet',
                'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            ],
            [
                'id' => 3,
                'kode' => 3,
                'name' => 'Intermark BSD',
                'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            ],
            [
                'id' => 4,
                'kode' => 4,
                'name' => 'Ciracas Center',
                'address' => 'Jl. Raden Saleh Raya No.39, RT.1/RW.4, Cikini, Kec. Menteng, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10330',
            ],
        ];
    }

    /**
     * Format a database row from the `data` table.
     */
    protected function formatRecord($row): array
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
            $dateFormatted = $carbon->translatedFormat('l, j F Y');
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
     * Display the main dashboard with records from table `data`.
     */
    public function index(Request $request)
    {
        $selectedKode = (int) $request->input('kode', $request->input('location', 1));
        if ($selectedKode < 1) {
            $selectedKode = 1;
        }

        $locations = $this->getLocations();
        $selectedLocation = null;

        foreach ($locations as &$loc) {
            $loc['selected'] = ($loc['kode'] === $selectedKode);
            if ($loc['selected']) {
                $selectedLocation = $loc;
            }
        }
        unset($loc);

        if (!$selectedLocation && count($locations) > 0) {
            $locations[0]['selected'] = true;
            $selectedLocation = $locations[0];
            $selectedKode = $locations[0]['kode'];
        }

        $rawRecords = DB::table('data')
            ->where('Kode', $selectedKode)
            ->orderBy('Tanggal', 'desc')
            ->get();

        $records = [];
        foreach ($rawRecords as $row) {
            $records[] = $this->formatRecord($row);
        }

        return view('dashboard.main', compact('locations', 'records', 'selectedLocation', 'selectedKode'));
    }

    /**
     * Return JSON data for location / date filtering via AJAX without page reload.
     */
    public function getData(Request $request)
    {
        $selectedKode = (int) $request->input('kode', $request->input('location', 1));

        $query = DB::table('data')->where('Kode', $selectedKode);

        // Optional date filter
        if ($request->filled('date')) {
            $query->whereDate('Tanggal', $request->input('date'));
        }

        $rawRecords = $query->orderBy('Tanggal', 'desc')->get();

        $records = [];
        foreach ($rawRecords as $row) {
            $records[] = $this->formatRecord($row);
        }

        return response()->json([
            'success' => true,
            'kode' => $selectedKode,
            'total' => count($records),
            'records' => $records,
        ]);
    }
}
