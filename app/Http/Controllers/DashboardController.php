<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected LocationService $locationService
    ) {}

    /**
     * Display the main reconciliation dashboard.
     */
    public function index(Request $request): View
    {
        $selectedKode = (int) $request->input('kode', $request->input('location', 1));
        if ($selectedKode < 1) {
            $selectedKode = 1;
        }

        $locations = $this->locationService->getLocations();
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

        $records = $this->locationService->getRecordsByLocation($selectedKode);

        return view('pages.dashboard.index', compact('locations', 'records', 'selectedLocation', 'selectedKode'));
    }

    /**
     * Return JSON data for location / date filtering via AJAX without page reload.
     */
    public function getData(Request $request): JsonResponse
    {
        $selectedKode = (int) $request->input('kode', $request->input('location', 1));
        $date = $request->input('date');

        $records = $this->locationService->getRecordsByLocation($selectedKode, $date);

        return response()->json([
            'success' => true,
            'kode' => $selectedKode,
            'total' => count($records),
            'records' => $records,
        ]);
    }
}
