<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasis';

    protected $fillable = [
        'nama_lokasi',
        'keterangan_alamat',
        'batas_waktu_henti',
        'uuid_lokasi',
        'api_lokasi',
        'api_token_otto',
        'api_url_otto',
        'api_token_mdd',
        'api_url_mdd',
        'api_token_lokasi',
        'api_url_lokasi',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * Automatically hashes uuid_lokasi and api_lokasi before saving to database.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'uuid_lokasi' => 'hashed',
            'api_lokasi' => 'hashed',
            'batas_waktu_henti' => 'integer',
        ];
    }

    /**
     * Boot the model.
     * Auto-generate a UUID if uuid_lokasi is not provided on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (Lokasi $lokasi) {
            if (empty($lokasi->uuid_lokasi)) {
                $lokasi->uuid_lokasi = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the online monitoring record associated with the location.
     */
    public function onlineMonitoring()
    {
        return $this->hasOne(OnlineMonitoring::class, 'lokasi_id');
    }

    /**
     * Get all network monitoring histories for this location.
     */
    public function monitoringHistories()
    {
        return $this->hasMany(OnlineMonitoringHistory::class, 'lokasi_id');
    }

    /**
     * Get all transactions for this location.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'lokasi_id');
    }

    /**
     * Get all settlement files for this location.
     */
    public function settlements()
    {
        return $this->hasMany(Settlement::class, 'lokasi_id');
    }
}
