<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class testapi extends Model
{
    //

    protected $fillable = [
        'image',
        'title',
        'description',
        'price',
        'stock',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($image) => asset('storage/products/' . $image),
        );
    }
}
