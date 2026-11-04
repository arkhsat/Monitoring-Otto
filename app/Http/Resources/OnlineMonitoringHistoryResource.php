<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OnlineMonitoringHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (is_array($this->resource)) {
            return $this->resource;
        }

        return [
            'id' => $this->id,
            'lokasi_id' => $this->lokasi_id,
            'location_name' => $this->lokasi?->nama_lokasi ?? '-',
            'network' => $this->jaringan,
            'recorded_at' => $this->recorded_at?->toIso8601String(),
            'recorded_at_formatted' => $this->recorded_at?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
            'previous_status' => $this->previous_status,
            'current_status' => $this->current_status,
            'downtime_start' => $this->downtime_start?->toIso8601String(),
            'downtime_start_formatted' => $this->downtime_start?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
            'downtime_end' => $this->downtime_end?->toIso8601String(),
            'downtime_end_formatted' => $this->downtime_end?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
            'duration_seconds' => (int) $this->duration_seconds,
            'recovery_time' => $this->recovery_time?->toIso8601String(),
            'recovery_time_formatted' => $this->recovery_time?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
            'notes' => $this->notes ?: '-',
        ];
    }
}

