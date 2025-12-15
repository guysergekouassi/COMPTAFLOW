<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{


    public function login(Request $request)
{
    $request->validate([
        'email_adresse' => 'required|email',
        'password' => 'required',
    ]);

    // Récupérer l'utilisateur
    $user = User::with('company')->where('email_adresse', $request->email_adresse)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors(['email_adresse' => 'Identifiants incorrects'])->withInput();
    }

    // Vérifier si le compte est actif
    if (!$user->is_active) {
        return back()->withErrors([
            'email_adresse' => 'Compte désactivé.' // Message de validation pour le compte désactivé
        ])->withInput();
    }


    // Vérifier si l'entreprise est bloquée
    if ($user->company && $user->company->is_blocked) {
        return back()->withErrors([
            'email_adresse' => 'Votre entreprise est actuellement bloquée pour cause d’abonnement impayé.'
        ])->withInput();
    }

    // Authentifier manuellement
    Auth::login($user);
    $user->update(['is_online' => 1]);


    if ($user->role === 'super_admin') {
        return redirect()->route('superadmin.dashboard'); 
    } elseif ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'comptable') {
        return redirect()->route('comptable.comptdashboard');
    }

    return redirect('/unauthorized');
}


    public function logout(Request $request)
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            if ($user) {
                $user->update(['is_online' => 0]);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Vous avez été déconnecté avec succès.');
        } catch (\Throwable $e) {
            Log::error('Erreur lors de la déconnexion : ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'email_adresse' => 'Erreur lors de la déconnexion. Veuillez réessayer.'
            ]);
        }
    }
}
