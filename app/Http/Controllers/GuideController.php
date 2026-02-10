<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuideController extends Controller
{
    /**
     * Redirige vers le guide approprié selon le rôle de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('guide.superadmin');
        } elseif ($user->isAdmin()) {
            return redirect()->route('guide.admin');
        } else {
            return redirect()->route('guide.comptable');
        }
    }

    /**
     * Affiche le guide SuperAdmin
     */
    public function superadmin()
    {
        // Vérifier que l'utilisateur est bien SuperAdmin
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        return view('guides.superadmin', [
            'page_title' => 'Guide d\'utilisation - Super Administrateur'
        ]);
    }

    /**
     * Affiche le guide Admin
     */
    public function admin()
    {
        // Vérifier que l'utilisateur est au moins Admin
        if (!Auth::user()->isAdmin() && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Accès non autorisé');
        }

        return view('guides.admin', [
            'page_title' => 'Guide d\'utilisation - Administrateur'
        ]);
    }

    /**
     * Affiche le guide Comptable
     */
    public function comptable()
    {
        // Tous les utilisateurs authentifiés peuvent voir le guide comptable
        return view('guides.comptable', [
            'page_title' => 'Guide d\'utilisation - Comptable'
        ]);
    }
}
