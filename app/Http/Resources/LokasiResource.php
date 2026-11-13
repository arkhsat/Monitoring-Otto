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
            'api_token_otto' => $this->api_token_otto,
            'api_url_otto' => $this->api_url_otto,
            'api_token_mdd' => $this->api_token_mdd,
            'api_url_mdd' => $this->api_url_mdd,
            'api_token_lokasi' => $this->api_token_lokasi,
            'api_url_lokasi' => $this->api_url_lokasi,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_formatted' => $this->created_at?->locale('id')->isoFormat('D MMM Y, HH:mm:ss') ?: '-',
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

