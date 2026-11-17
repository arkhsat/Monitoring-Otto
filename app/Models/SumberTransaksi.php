<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberTransaksi extends Model
{
    use HasFactory;

    protected $table = 'sumber_transaksis';

    protected $fillable = [
        'kode_sumber',
        'nama_sumber',
        'keterangan',
    ];

    /**
     * Get all transactions associated with this source.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'sumber_id');
    }
}

