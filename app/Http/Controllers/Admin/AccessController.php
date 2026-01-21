<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'super_admin')->get();
        return view('admin.access.index', compact('users'));
    }

    public function toggleUser($id)
    {
        $user = User::findOrFail($id);
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $status = $user->is_blocked ? 'bloqué' : 'débloqué';
        return back()->with('success', "L'utilisateur a été $status avec succès.");
    }
}
