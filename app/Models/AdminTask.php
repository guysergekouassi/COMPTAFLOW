<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminTask extends Model
{
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'due_date',
        'status',
        'priority'
    ];

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
