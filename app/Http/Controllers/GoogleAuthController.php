<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google OAuth.
     * $type can be: login, cabinet, entreprise, comptable
     */
    public function redirectToGoogle(string $type = 'login')
    {
        session(['google_register_type' => $type]);

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Handle the Google OAuth callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'Echec de la connexion Google. Veuillez reessayer.']);
        }

        $type = session('google_register_type', 'login');
        session()->forget('google_register_type');

        // Split Google name into name / last_name
        $nameParts = explode(' ', trim($googleUser->getName()), 2);
        $name      = strtoupper($nameParts[0] ?? '');
        $lastName  = $nameParts[1] ?? '';

        // Try to find an existing user by google_id or email
        $user = User::where('google_id', $googleUser->getId())->first()
             ?? User::where('email_adresse', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
                'is_online' => 1,
            ]);
        } else {
            $role = match ($type) {
                'cabinet'    => 'comptable',
                'comptable'  => 'comptable',
                'entreprise' => 'admin',
                default      => 'comptable',
            };

            $user = User::create([
                'name'          => $name,
                'last_name'     => $lastName,
                'email_adresse' => $googleUser->getEmail(),
                'google_id'     => $googleUser->getId(),
                'avatar'        => $googleUser->getAvatar(),
                'password'      => bcrypt(Str::random(24)),
                'role'          => $role,
                'company_id'    => null,
                'is_active'     => true,
                'is_online'     => 1,
            ]);
        }

        if (!$user->is_active) {
            return redirect()->route('login')->withErrors(['google' => 'Votre compte est desactive.']);
        }

        Auth::login($user);

        if ($user->role === 'super_admin') {
            return redirect()->route('superadmin.dashboard');
        }

        return redirect()->route('accountant.space');
    }
}
