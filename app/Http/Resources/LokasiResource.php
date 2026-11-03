<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LokasiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_lokasi' => $this->nama_lokasi,
            'uuid_lokasi' => $this->uuid_lokasi,
            'api_lokasi' => $this->api_lokasi,
            'keterangan_alamat' => $this->keterangan_alamat ?: '-',
            'batas_waktu_henti' => (int) $this->batas_waktu_henti,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_formatted' => $this->created_at?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

