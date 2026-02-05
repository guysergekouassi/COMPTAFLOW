<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'company_id',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Utilisateur assigné à la tâche
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Utilisateurs assignés à la tâche (Relation Many-to-Many)
     */
    public function assignees()
    {
        return $this->belongsToMany(User::class, 'admin_task_user')
                    ->withPivot('status', 'read_at')
                    ->withTimestamps();
    }

    /**
     * Utilisateur qui a créé/assigné la tâche
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Alias pour assignedBy (Createur de la tâche)
     */
    public function creator()
    {
        return $this->assignedBy();
    }

    /**
     * Entreprise concernée par la tâche
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour filtrer par priorité
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope pour les tâches en retard
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Vérifier si la tâche est en retard
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date || in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }
        
        return $this->due_date->isPast();
    }

    /**
     * Obtenir le badge de priorité
     */
    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'badge-danger',
            'high' => 'badge-warning',
            'medium' => 'badge-info',
            'low' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    /**
     * Obtenir le badge de statut
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'completed' => 'badge-success',
            'in_progress' => 'badge-primary',
            'cancelled' => 'badge-dark',
            'pending' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
