<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * L'activation d'un compte créé sans mot de passe.
 *
 * `POST /api/external/register-enterprise` exigeait `admin_password` : le
 * superadministrateur Selflow choisissait le mot de passe du compte d'un
 * client, et le transportait en clair dans le corps de la requête.
 * `companies/provision` ne transmet plus rien de tel — le compte est ouvert
 * avec un secret aléatoire que personne ne détient, et c'est ici que son
 * titulaire choisit le sien.
 */
class ActivationCompteController extends Controller
{
    public function formulaire(string $jeton)
    {
        $user = self::utilisateurDuJeton($jeton);

        if (!$user) {
            return view('ativations.activation_expiree');
        }

        return view('ativations.activation_mot_de_passe', ['jeton' => $jeton]);
    }

    public function enregistrer(Request $request, string $jeton)
    {
        $user = self::utilisateurDuJeton($jeton);

        if (!$user) {
            return view('ativations.activation_expiree');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->forceFill([
            'password'                    => Hash::make($request->input('password')),
            // Le jeton est consommé : un lien d'activation qui resterait
            // valable après usage rouvrirait le compte à quiconque a encore le
            // message dans sa boîte — ou dans ses archives.
            'activation_token'            => null,
            'activation_token_expires_at' => null,
            'is_active'                   => true,
        ])->save();

        Log::info('Activation : mot de passe posé', ['user_id' => $user->id]);

        return redirect()->route('login')
            ->with('success', 'Votre compte est activé. Vous pouvez vous connecter.');
    }

    /**
     * Le compte que ce jeton désigne, s'il est encore valable.
     *
     * La recherche porte sur le haché : le jeton n'est lisible dans aucune
     * colonne, et tant qu'il n'a pas servi il vaut un mot de passe.
     */
    private static function utilisateurDuJeton(string $jeton): ?User
    {
        $user = User::where('activation_token', hash('sha256', $jeton))->first();

        if (!$user) {
            return null;
        }

        if ($user->activation_token_expires_at && now()->greaterThan($user->activation_token_expires_at)) {
            return null;
        }

        return $user;
    }
}
