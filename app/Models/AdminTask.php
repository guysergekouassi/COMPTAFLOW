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
        'priority',
        'file_path'
    ];

    // Relation Many-to-Many via la table pivot admin_task_user
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'admin_task_user', 'admin_task_id', 'user_id')
                    ->withPivot('status', 'read_at')
                    ->withTimestamps();
    }
    
    // Legacy support (optional, or method to get singular assignee if needed)
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
