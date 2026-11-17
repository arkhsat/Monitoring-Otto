<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $table = 'settlements';

    protected $fillable = [
        'lokasi_id',
        'tanggal_settlement',
        'file_txt_name',
        'file_txt_path',
        'file_txt_content',
        'file_ok_name',
        'file_ok_path',
        'file_ok_content',
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
            'tanggal_settlement' => 'date:Y-m-d',
        ];
    }

    /**
     * Relation to Lokasi model.
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }
}

