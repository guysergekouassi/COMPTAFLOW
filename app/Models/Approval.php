<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'type',
        'data',
        'status',
        'requested_by',
        'handled_by',
        'comment'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
