<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MonitoringData;
use App\Models\Lokasi;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get locations from database table `lokasis`, with fallback to defaults.
     */
    protected function getLocations(): array
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
                'address' => 'Jl. Tebet Barat Dalam Raya No.1, RT.1/RW.4, Tebet Barat, Jakarta Selatan, DKI Jakarta 12810',
            ],
            [
                'id' => 3,
                'kode' => 3,
                'name' => 'Intermark BSD',
                'address' => 'Jl. Lingkar Timur No.9, Rawa Mekar Jaya, Serpong, Kota Tangerang Selatan, Banten 15310',
            ],
            [
                'id' => 4,
                'kode' => 4,
                'name' => 'Ciracas Center',
                'address' => 'Jl. Raya Ciracas No.12, RT.6/RW.3, Ciracas, Kota Jakarta Timur, DKI Jakarta 13740',
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

    /**
     * Display the Online Monitoring page.
     */
    public function onlineMonitoring(Request $request)
    {
        $locations = $this->getLocations();

        // Hardcoded online monitoring data for known locations (IDs 1-4)
        $configuredNetworks = [
            1 => [
                'jaringan' => '192.168.10.1 / Fiber Biznet 100Mbps',
                'downtime' => '0m (0%)',
                'status' => 'Online',
                'update' => 'Baru saja (26/10/2026 11:20)',
                'history' => [
                    ['date' => '26/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '25/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '24/10/2026', 'downtime' => '5m', 'status' => 'Gangguan Singkat'],
                    ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ]
            ],
            2 => [
                'jaringan' => '192.168.20.1 / Indihome 50Mbps',
                'downtime' => '25m (1.7%)',
                'status' => 'Gangguan',
                'update' => '5 menit lalu (26/10/2026 11:15)',
                'history' => [
                    ['date' => '26/10/2026', 'downtime' => '25m', 'status' => 'Down'],
                    ['date' => '25/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '24/10/2026', 'downtime' => '12m', 'status' => 'RTO Berkala'],
                    ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ]
            ],
            3 => [
                'jaringan' => '192.168.30.1 / MyRepublic 100Mbps',
                'downtime' => '0m (0%)',
                'status' => 'Online',
                'update' => '1 menit lalu (26/10/2026 11:19)',
                'history' => [
                    ['date' => '26/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '25/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '24/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ]
            ],
            4 => [
                'jaringan' => '192.168.40.1 / FirstMedia 75Mbps',
                'downtime' => '2h 10m (9.0%)',
                'status' => 'Offline',
                'update' => '15 menit lalu (26/10/2026 11:05)',
                'history' => [
                    ['date' => '26/10/2026', 'downtime' => '2h 10m', 'status' => 'Offline'],
                    ['date' => '25/10/2026', 'downtime' => '45m', 'status' => 'Down'],
                    ['date' => '24/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '23/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                    ['date' => '22/10/2026', 'downtime' => '0m', 'status' => 'Normal'],
                ]
            ],
        ];

        // Build network locations dynamically from all locations in database
        $networkLocations = [];
        $countOnline = 0;
        $countGangguan = 0;
        $countOffline = 0;

        foreach ($locations as $loc) {
            $id = $loc['id'];
            if (isset($configuredNetworks[$id])) {
                $net = $configuredNetworks[$id];
                $networkLocations[] = [
                    'id' => $id,
                    'name' => $loc['name'],
                    'address' => $loc['address'],
                    'jaringan' => $net['jaringan'],
                    'downtime' => $net['downtime'],
                    'status' => $net['status'],
                    'update' => $net['update'],
                    'history' => $net['history'],
                ];

                if ($net['status'] === 'Online') $countOnline++;
                elseif ($net['status'] === 'Gangguan') $countGangguan++;
                else $countOffline++;
            } else {
                // Newly added location from "Lokasi" menu: default attributes to "-" until manually configured
                $networkLocations[] = [
                    'id' => $id,
                    'name' => $loc['name'],
                    'address' => $loc['address'],
                    'jaringan' => '-',
                    'downtime' => '-',
                    'status' => '-',
                    'update' => '-',
                    'history' => [],
                ];
            }
        }

        $summary = [
            'total_locations' => count($networkLocations),
            'online' => $countOnline,
            'gangguan' => $countGangguan,
            'offline' => $countOffline,
        ];

        return view('dashboard.online', compact('locations', 'networkLocations', 'summary'));
    }
}
