<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminTask;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        // Vue "Assigner Tâche" : Voir les tâches que J'AI créées ou TOUTES les tâches si SuperAdmin ?
        // Pour l'instant : Les tâches créées par l'utilisateur connecté
        $tasks = AdminTask::with(['assignees', 'creator'])
            ->where('assigned_by', auth()->id()) // Voir seulement ce qu'on a délégué
            ->orderBy('created_at', 'desc')
            ->get();
            
        $users = User::where('role', '!=', 'super_admin')->where('company_id', auth()->user()->company_id)->get();

        return view('admin.tasks.index', compact('tasks', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'required|array',
            'assigned_to.*' => 'exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'file' => 'nullable|file|max:20480', // 20MB max
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('tasks_attachments', 'public');
        }

        $task = AdminTask::create([
            'title' => $request->title,
            'description' => $request->description,
            // 'assigned_to' => null, // Plus utilisé
            'assigned_by' => auth()->id(),
            'due_date' => $request->due_date,
            'status' => 'pending',
            'priority' => $request->priority,
            'file_path' => $filePath
        ]);

        $task->assignees()->attach($request->assigned_to);

        return back()->with('success', 'Tâche assignée avec succès.');
    }

    // Vue "Tâche Quotidienne" pour les collaborateurs
    public function dailyTasks()
    {
        $user = auth()->user();
        
        // Celles qui me sont assignées
        $tasks = AdminTask::whereHas('assignees', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['creator', 'assignees']) // Pour voir qui a envoyé
        ->orderBy('priority', 'desc') // Urgences d'abord
        ->orderBy('due_date', 'asc')
        ->get();

        return view('admin.tasks.daily', compact('tasks'));
    }

    public function markAsCompleted(AdminTask $task)
    {
        // Update pivot status
        $task->assignees()->updateExistingPivot(auth()->id(), ['status' => 'completed']);
        // Check if all completed? Optional logic.
        return back()->with('success', 'Tâche marquée comme terminée.');
    }

    public function destroy($id)
    {
        $task = AdminTask::findOrFail($id);
        
        // Vérification des droits (celui qui a assigné ou admin)
        if ($task->assigned_by !== auth()->id() && !auth()->user()->isAdmin()) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à supprimer cette tâche.');
        }

        $task->delete();

        return back()->with('success', 'Tâche supprimée avec succès.');
    }
}
