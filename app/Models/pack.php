<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pack extends Model
{
    protected $table = 'pack';

    protected $fillable = [
        'name',
        'price',
        'max_users',
        'duration',
        'description',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'float',
    ];
}
