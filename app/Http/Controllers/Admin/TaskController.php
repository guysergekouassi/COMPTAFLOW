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
        $tasks = AdminTask::with(['assignee', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        $users = User::where('role', '!=', 'super_admin')->get();

        return view('admin.tasks.index', compact('tasks', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'required|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent'
        ]);

        AdminTask::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => auth()->id(),
            'due_date' => $request->due_date,
            'status' => 'pending',
            'priority' => $request->priority,
        ]);

        return back()->with('success', 'Tâche assignée avec succès.');
    }
}
