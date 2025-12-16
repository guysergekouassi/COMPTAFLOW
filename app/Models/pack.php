<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pack extends Model
{
    //
    protected $fillable = [
        'name',
        'price',
        'max_users',
        'duration',
        'desciption',
        'features',

    ];

    protected $casts = [
        'features' => 'array',
        'price_monthly' => 'float',
    ];
}
