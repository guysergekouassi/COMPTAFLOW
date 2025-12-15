<?php
namespace App\Http\Controllers\Souscrire;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{

    public function showPricing()
    {
        
        $packs = [
            'ESSENTIEL' => ['price_monthly' => 16.50, 'features' => ['1 utilisateur', '100 tiers']],
            'CROISSANCE' => ['price_monthly' => 39.99, 'features' => ['5 utilisateurs', 'tiers illimités']],
            // ... autres packs
        ];

        // L'utilisateur est-il déjà abonné ?
        $currentSubscription = Auth::check() ? Auth::user()->currentSubscription : null;

        // Retourne la vue Blade que nous avons créée.
        // Assurez-vous que le chemin de la vue est correct (ex: 'subscriptions.pricing')
        return view('ativations.active', [
            'packs' => $packs,
            'currentSubscription' => $currentSubscription,
        ]);
    }


    public function processSubscription(Request $request)
    {
        // 1. Validation de la requête (quel pack a été choisi)
        $request->validate([
            'pack_name' => 'required|string|in:ESSENTIEL,CROISSANCE,ENTREPRISE',
            'billing_cycle' => 'required|string|in:monthly,annual',
        ]);


        // 3. Redirection avec un message de succès
        return redirect()->route('dashboard')->with('success', 'Félicitations ! Votre abonnement a été activé.');
    }
}
