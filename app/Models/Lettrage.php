<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lettrage extends Model
{
    protected $fillable = [
        'code',
        'date_lettrage',
        'user_id',
        'company_id',
    ];

    protected $casts = [
        'date_lettrage' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class);
    }
}
