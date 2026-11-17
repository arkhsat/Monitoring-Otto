<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'lokasi_id',
        'sumber_id',
        'token_source',
        'transactionid',
        'timein',
        'nokartubank',
        'posinid',
        'image_plate_in',
        'vehicleid',
        'nopolisi',
        'dateout',
        'status',
        'cost',
        'paymentby',
        'posoutid',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'timein'    => 'datetime',
            'dateout'   => 'datetime',
            'cost'      => 'integer',
            'lokasi_id' => 'integer',
            'sumber_id' => 'integer',
        ];
    }

    /**
     * Relation to Lokasi model.
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    /**
     * Relation to SumberTransaksi model.
     */
    public function sumber()
    {
        return $this->belongsTo(SumberTransaksi::class, 'sumber_id');
    }
}

