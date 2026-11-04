<?php

namespace App\Http\Controllers;

use App\Services\OnlineMonitoringService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnlineMonitoringController extends Controller
{
    public function __construct(
        protected OnlineMonitoringService $monitoringService
    ) {}

    /**
     * Display the Online Monitoring dashboard page.
     */
    public function index(Request $request): View
    {
        $monitoringData = $this->monitoringService->getAllMonitoringData();

        $networkLocations = $monitoringData['items'];
        $summary = $monitoringData['summary'];
        $serverTime = $monitoringData['server_time'];

        return view('pages.online-monitoring.index', compact('networkLocations', 'summary', 'serverTime'));
    }
}
