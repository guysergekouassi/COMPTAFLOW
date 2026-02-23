<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Tâches assignées par l'utilisateur.
     */
    public function index()
    {
        $tasks = AdminTask::with(['assignees:id,name,last_name', 'creator:id,name,last_name'])
            ->where('assigned_by', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tasks);
    }

    /**
     * Tâches quotidiennes (assignées à l'utilisateur).
     */
    public function dailyTasks()
    {
        $user = Auth::user();
        $tasks = AdminTask::whereHas('assignees', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['creator:id,name,last_name', 'assignees:id,name,last_name'])
        ->orderBy('priority', 'desc')
        ->orderBy('due_date', 'asc')
        ->get();

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'required|array',
            'assigned_to.*' => 'exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'nullable|string'
        ]);

        $task = AdminTask::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_by' => Auth::id(),
            'due_date' => $request->due_date,
            'status' => 'pending',
            'priority' => $request->priority,
        ]);

        $task->assignees()->attach($request->assigned_to);

        return response()->json([
            'success' => true,
            'task' => $task->load('assignees')
        ]);
    }

    public function markAsCompleted($id)
    {
        $task = AdminTask::findOrFail($id);
        $task->assignees()->updateExistingPivot(Auth::id(), ['status' => 'completed']);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $task = AdminTask::findOrFail($id);
        
        if ($task->assigned_by !== Auth::id() && !Auth::user()->isAdmin()) {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $task->delete();
        return response()->json(['success' => true]);
    }
}
