<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirige l'utilisateur vers Google pour l'authentification.
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    /**
     * Callback Google : connecte ou crée l'utilisateur puis redirige.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['email_adresse' => 'Échec de la connexion Google. Veuillez réessayer.']);
        }

        // Chercher un utilisateur existant par email
        $user = User::where('email_adresse', $googleUser->getEmail())->first();

        if ($user) {
            // --- Utilisateur existant ---

            // Vérifier si le compte est actif
            if (!$user->is_active) {
                return redirect()->route('login')
                    ->withErrors(['email_adresse' => 'Votre compte est désactivé.']);
            }

            // Vérifier si l'entreprise est bloquée (pour les non super-admin)
            if ($user->company && $user->company->is_blocked) {
                return redirect()->route('login')
                    ->withErrors(['email_adresse' => 'Votre entreprise est actuellement bloquée pour cause d\'abonnement impayé.']);
            }

            // Mettre à jour le google_id si pas encore enregistré
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            }

        } else {
            // --- Nouvel utilisateur → créer automatiquement un compte ---
            $nameParts = explode(' ', trim($googleUser->getName()), 2);
            $firstName = $nameParts[0] ?? $googleUser->getName();
            $lastName  = $nameParts[1] ?? '';

            $user = User::create([
                'name'          => $firstName,
                'last_name'     => $lastName,
                'email_adresse' => $googleUser->getEmail(),
                'google_id'     => $googleUser->getId(),
                'avatar'        => $googleUser->getAvatar(),
                'password'      => bcrypt(Str::random(24)), // Mot de passe aléatoire (connexion uniquement par Google)
                'role'          => 'admin',
                'is_active'     => true,
                'is_online'     => 0,
                'company_id'    => null, // Pas encore d'entreprise assignée
            ]);
        }

        // Connecter l'utilisateur
        Auth::login($user);
        $user->update(['is_online' => 1]);

        // Redirection selon rôle
        if ($user->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        }

        // Admin, comptable ou nouvel utilisateur → Mon Espace
        return redirect()->route('accountant.space');
    }
}
