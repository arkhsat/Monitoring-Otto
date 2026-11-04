<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnlineMonitoringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // If resource is already an associative array (prepared by service)
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->id,
            'lokasi_id' => $this->lokasi_id,
            'location' => $this->lokasi?->nama_lokasi ?? '-',
            'address' => $this->lokasi?->keterangan_alamat ?? '-',
            'network' => $this->jaringan ?? '-',
            'ip_address' => $this->ip_address,
            'last_update' => $this->last_update?->toIso8601String(),
            'first_monitored_at' => $this->first_monitored_at?->toIso8601String(),
        ];
    }
}

