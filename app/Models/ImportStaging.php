<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportStaging extends Model
{
    protected $fillable = [
        'batch_id',
        'company_id',
        'user_id',
        'exercice_id',
        'source',
        'type',
        'file_name',
        'raw_data',
        'mapping',
        'metadata',
        'status',
        'error_log'
    ];

    protected $casts = [
        'raw_data' => 'array',
        'mapping' => 'array'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
