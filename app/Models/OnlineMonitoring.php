<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnlineMonitoring extends Model
{
    use HasFactory;

    protected $table = 'online_monitorings';

    protected $fillable = [
        'lokasi_id',
        'jaringan',
        'ip_address',
        'status',
        'last_update',
        'first_monitored_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'lokasi_id' => 'integer',
            'last_update' => 'datetime',
            'first_monitored_at' => 'datetime',
        ];
    }

    /**
     * Get the location that owns the monitoring record.
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    /**
     * Get the monitoring history records for this device.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(OnlineMonitoringHistory::class, 'online_monitoring_id');
    }
}
