<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\AdminTask;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminTaskController extends Controller
{
    /**
     * Affiche la liste des tâches administratives
     */
    public function index(Request $request)
    {
        $query = AdminTask::with(['assignedTo', 'assignedBy', 'company']);
        
        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        
        $tasks = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Données pour les filtres
        $users = User::where('role', '!=', 'comptable')->get();
        $companies = Company::all();
        
        return view('superadmin.tasks', compact('tasks', 'users', 'companies'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $users = User::where('role', '!=', 'comptable')->get();
        $companies = Company::all();
        
        return view('superadmin.create_task', compact('users', 'companies'));
    }

    /**
     * Enregistre une nouvelle tâche
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);
        
        AdminTask::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'assigned_by' => Auth::id(),
            'company_id' => $validated['company_id'] ?? null,
            'priority' => $validated['priority'],
            'status' => 'pending',
            'due_date' => $validated['due_date'] ?? null,
        ]);
        
        return redirect()->route('superadmin.tasks.index')
            ->with('success', 'Tâche créée avec succès !');
    }

    /**
     * Affiche une tâche spécifique
     */
    public function show($id)
    {
        $task = AdminTask::with(['assignedTo', 'assignedBy', 'company'])->findOrFail($id);
        return view('superadmin.show_task', compact('task'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit($id)
    {
        $task = AdminTask::findOrFail($id);
        $users = User::where('role', '!=', 'comptable')->get();
        $companies = Company::all();
        
        return view('superadmin.edit_task', compact('task', 'users', 'companies'));
    }

    /**
     * Met à jour une tâche
     */
    public function update(Request $request, $id)
    {
        $task = AdminTask::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'due_date' => 'nullable|date',
        ]);
        
        // Si le statut passe à "completed", enregistrer la date
        if ($validated['status'] === 'completed' && $task->status !== 'completed') {
            $validated['completed_at'] = now();
        }
        
        $task->update($validated);
        
        return redirect()->route('superadmin.tasks.index')
            ->with('success', 'Tâche mise à jour avec succès !');
    }

    /**
     * Supprime une tâche
     */
    public function destroy($id)
    {
        $task = AdminTask::findOrFail($id);
        $task->delete();
        
        return redirect()->route('superadmin.tasks.index')
            ->with('success', 'Tâche supprimée avec succès !');
    }
    
    /**
     * Assigne une tâche à un utilisateur
     */
    public function assign(Request $request, $id)
    {
        $task = AdminTask::findOrFail($id);
        
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);
        
        $task->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'in_progress',
        ]);
        
        return redirect()->back()
            ->with('success', 'Tâche assignée avec succès !');
    }
    
    /**
     * Met à jour le statut d'une tâche
     */
    public function updateStatus(Request $request, $id)
    {
        $task = AdminTask::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);
        
        $updateData = ['status' => $validated['status']];
        
        if ($validated['status'] === 'completed') {
            $updateData['completed_at'] = now();
        }
        
        $task->update($updateData);
        
        return redirect()->back()
            ->with('success', 'Statut mis à jour avec succès !');
    }
}
