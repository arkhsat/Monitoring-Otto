<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1. Ambil nilai angka mentah
        $admin = (int) ($this->Admin ?? 0);
        $settlement = (int) ($this->Settlement ?? 0);
        $lokal = (int) ($this->Lokal ?? 0);

        // 2. Logika pengecekan kecocokan (sama seperti di LocationService)
        $isMatch = ($admin === $settlement && $settlement === $lokal);
        $status = $isMatch ? 'Cocok' : 'Tidak Cocok';

        // 3. Logika pemformatan tanggal
        $dateFormatted = '-';
        $isoDate = null;
        if ($this->Tanggal) {
            $carbon = Carbon::parse($this->Tanggal)->locale('id');
            $dateFormatted = $carbon->format('d/m/Y');
            $isoDate = $carbon->format('Y-m-d');
        }

        // 4. Struktur JSON yang rapi untuk dikirim ke Client/Frontend
        return [
            'kode'       => $this->Kode,
            'date'       => $dateFormatted,
            'iso_date'   => $isoDate,
            'admin'      => number_format($admin, 0, ',', '.'),
            'mdd'        => number_format($settlement, 0, ',', '.'),
            'settlement' => number_format($settlement, 0, ',', '.'),
            'report'     => number_format($lokal, 0, ',', '.'),
            'lokal'      => number_format($lokal, 0, ',', '.'),
            'status'     => $status,
            'is_match'   => $isMatch,
        ];
    }
}
