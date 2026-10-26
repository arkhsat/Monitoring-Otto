<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use App\Services\NetworkMonitoringService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnlineMonitoringController extends Controller
{
    public function __construct(
        protected LocationService $locationService,
        protected NetworkMonitoringService $networkService
    ) {}

    /**
     * Display the Online Monitoring dashboard page.
     */
    public function index(Request $request): View
    {
        $locations = $this->locationService->getLocations();
        $networkData = $this->networkService->getNetworkStatus($locations);

        $networkLocations = $networkData['networkLocations'];
        $summary = $networkData['summary'];

        return view('pages.online-monitoring.index', compact('locations', 'networkLocations', 'summary'));
    }
}
