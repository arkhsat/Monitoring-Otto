<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringData extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'data';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Tanggal',
        'Admin',
        'Settlement',
        'Lokal',
        'Kode',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Tanggal' => 'datetime',
            'Admin' => 'integer',
            'Settlement' => 'integer',
            'Lokal' => 'integer',
            'Kode' => 'integer',
        ];
    }
}
