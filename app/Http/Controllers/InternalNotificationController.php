<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternalNotification;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InternalNotificationController extends Controller
{
    /**
     * Liste des notifications de l'utilisateur connecté
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = InternalNotification::where('receiver_id', $user->id)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Récupérer les destinataires possibles pour envoyer un nouveau message
        $recipients = $this->getAvailableRecipients($user);

        return view('user.notifications.index', compact('notifications', 'recipients'));
    }

    /**
     * Envoie une nouvelle notification / message
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error,message'
        ]);

        $user = Auth::user();

        // Vérification de sécurité supplémentaire (hiérarchie)
        $allowed = $this->getAvailableRecipients($user)->pluck('id')->toArray();
        if (!in_array($request->receiver_id, $allowed)) {
            return back()->with('error', 'Vous n\'êtes pas autorisé à envoyer un message à cet utilisateur.');
        }

        InternalNotification::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type,
            'company_id' => $user->company_id,
            'is_read' => false
        ]);

        return back()->with('success', 'Votre message a été envoyé avec succès.');
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        $notification = InternalNotification::where('receiver_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Récupère la liste des destinataires autorisés selon la hiérarchie
     */
    private function getAvailableRecipients($user)
    {
        $recipients = collect();

        if ($user->role === 'super_admin') {
            // Super Admin peut écrire aux Admins qu'il a créé
            $recipients = User::where('created_by_id', $user->id)->get();
        } elseif ($user->role === 'admin') {
            // Admin peut écrire à son Super Admin (créateur)
            if ($user->created_by_id) {
                $creator = User::find($user->created_by_id);
                if ($creator) $recipients->push($creator);
            }
            // Admin peut écrire à tous ses utilisateurs de société
            $companyUsers = User::where('company_id', $user->company_id)
                ->where('id', '!=', $user->id)
                ->get();
            $recipients = $recipients->concat($companyUsers);
        } else {
            // Utilisateur classique (comptable, etc.)
            // Peut écrire à son Admin
            $admin = User::where('company_id', $user->company_id)
                ->where('role', 'admin')
                ->first();
            if ($admin) $recipients->push($admin);

            // Peut écrire à ses collègues (même société)
            $colleagues = User::where('company_id', $user->company_id)
                ->where('id', '!=', $user->id)
                ->where('id', '!=', $admin ? $admin->id : 0)
                ->get();
            $recipients = $recipients->concat($colleagues);
        }

        return $recipients->unique('id');
    }
    
    /**
     * API pour le header : compte des notifications non lues
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => InternalNotification::where('receiver_id', Auth::id())
                ->where('is_read', false)
                ->count()
        ]);
    }
}
