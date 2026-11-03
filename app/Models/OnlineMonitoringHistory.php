<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineMonitoringHistory extends Model
{
    use HasFactory;

    protected $table = 'online_monitoring_histories';

    protected $fillable = [
        'online_monitoring_id',
        'lokasi_id',
        'jaringan',
        'previous_status',
        'current_status',
        'downtime_start',
        'downtime_end',
        'duration_seconds',
        'recovery_time',
        'recorded_at',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'online_monitoring_id' => 'integer',
            'lokasi_id' => 'integer',
            'downtime_start' => 'datetime',
            'downtime_end' => 'datetime',
            'recovery_time' => 'datetime',
            'recorded_at' => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }

    /**
     * Get the monitoring device that owns this history log.
     */
    public function onlineMonitoring(): BelongsTo
    {
        return $this->belongsTo(OnlineMonitoring::class, 'online_monitoring_id');
    }

    /**
     * Get the location that owns this history log.
     */
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }
}

