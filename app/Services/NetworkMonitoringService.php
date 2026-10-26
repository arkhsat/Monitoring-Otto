<?php

namespace App\Services;

class NetworkMonitoringService
{
    /**
     * Build dynamic network monitoring data and statistics for given locations.
     *
     * @param array<int, array<string, mixed>> $locations
     * @return array{networkLocations: array<int, array<string, mixed>>, summary: array<string, int>}
     */
    public function getNetworkStatus(array $locations): array
    {
        $configuredNetworks = config('monitoring.network_devices', []);

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
                    'history' => $net['history'] ?? [],
                ];

                if ($net['status'] === 'Online') {
                    $countOnline++;
                } elseif ($net['status'] === 'Gangguan') {
                    $countGangguan++;
                } else {
                    $countOffline++;
                }
            } else {
                // Newly added location from "Lokasi" menu: default attributes to "-" until configured
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

        return [
            'networkLocations' => $networkLocations,
            'summary' => $summary,
        ];
    }
}
