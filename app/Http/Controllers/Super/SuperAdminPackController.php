<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Gestion des offres souscrites (montées et descentes en gamme).
 *
 *  - Pack Entreprise : une seule comptabilité, celle du titulaire. Son espace
 *    existe, mais il n'y ouvre pas d'autres sociétés et ne fusionne pas.
 *  - Pack Cabinet : autant de comptabilités que voulu, création et fusion.
 *
 * Dans les deux cas, le titulaire est le gérant de ses dossiers et garde
 * toutes les habilitations métier : sans elles, il perdrait des pages
 * entières de sa propre comptabilité.
 */
class SuperAdminPackController extends Controller
{
    private const PACKS = ['entreprise', 'cabinet'];

    public function index(Request $request)
    {
        $recherche = trim((string) $request->input('q', ''));
        $packFiltre = $request->input('pack');
        $portee = $request->input('portee', 'titulaires');
        $parPage = (int) $request->input('per_page', 20);
        $parPage = in_array($parPage, [20, 50, 100], true) ? $parPage : 20;

        $requete = User::query()->where('role', '!=', 'super_admin');

        // Par défaut, on ne montre que les titulaires d'une offre : celui qui
        // s'est inscrit lui-même, ou qui a créé au moins une comptabilité. Les
        // collaborateurs invités ne souscrivent pas, ils sont rattachés.
        if ($portee !== 'tous') {
            $requete->where(function ($q) {
                $q->whereNull('created_by_id')
                  ->orWhereExists(function ($sous) {
                      $sous->select(DB::raw(1))
                           ->from('companies')
                           ->whereColumn('companies.user_id', 'users.id');
                  });
            });
        }

        if ($recherche !== '') {
            $requete->where(function ($q) use ($recherche) {
                $q->where('name', 'like', "%{$recherche}%")
                  ->orWhere('last_name', 'like', "%{$recherche}%")
                  ->orWhere('email_adresse', 'like', "%{$recherche}%");
            });
        }

        if (in_array($packFiltre, self::PACKS, true)) {
            $requete->where('pack', $packFiltre);
        }

        $comptes = $requete->orderBy('created_at', 'desc')
            ->paginate($parPage)
            ->withQueryString();

        // Comptabilités de chaque titulaire, en une seule passe
        $ids = $comptes->pluck('id')->all() ?: [0];
        $creees = Company::whereIn('user_id', $ids)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');
        $affectees = DB::table('company_user')
            ->whereIn('user_id', $ids)
            ->select('user_id', DB::raw('count(distinct company_id) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        foreach ($comptes as $compte) {
            $compte->nb_creees = (int) ($creees[$compte->id] ?? 0);
            $compte->nb_gerees = count($this->comptabilitesDe($compte));
            $compte->nb_affectees = (int) ($affectees[$compte->id] ?? 0);
        }

        $stats = [
            'entreprise' => User::where('role', '!=', 'super_admin')->where('pack', 'entreprise')->count(),
            'cabinet'    => User::where('role', '!=', 'super_admin')->where('pack', 'cabinet')->count(),
            'comptas'    => Company::count(),
        ];

        return view('superadmin.packs', compact('comptes', 'stats', 'recherche', 'packFiltre', 'portee', 'parPage'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(
            ['pack' => 'required|in:entreprise,cabinet'],
            ['pack.required' => "Choisissez l'offre à appliquer.", 'pack.in' => "Offre inconnue."]
        );

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('superadmin.packs')->with('error', 'Compte introuvable.');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('superadmin.packs')
                ->with('error', "Un super administrateur ne souscrit pas d'offre.");
        }

        $nouveau = $request->input('pack');
        $ancien = $user->pack ?: 'cabinet';

        if ($nouveau === $ancien) {
            return redirect()->route('superadmin.packs')
                ->with('info', $user->name . ' ' . $user->last_name . ' est déjà sur le ' . $user->libellePack() . '.');
        }

        $comptabilites = $this->comptabilitesDe($user);

        if ($nouveau === 'entreprise') {
            if (count($comptabilites) === 0) {
                return redirect()->route('superadmin.packs')
                    ->with('error', "Le Pack Entreprise porte sur une comptabilité : ce compte n'en a aucune. Créez-la d'abord, ou laissez-le sur le Pack Cabinet.");
            }
            if (count($comptabilites) > 1) {
                return redirect()->route('superadmin.packs')
                    ->with('error', "Impossible : ce compte gère " . count($comptabilites) . " comptabilités. Le Pack Entreprise n'en autorise qu'une. Détachez les autres avant de redescendre l'offre.");
            }
        }

        $avant = ['pack' => $ancien, 'role' => $user->role];

        DB::transaction(function () use ($user, $nouveau, $comptabilites) {
            $user->pack = $nouveau;

            // Le titulaire d'une offre est le gérant de ses comptabilités, quelle
            // que soit l'offre. On promeut celui qui en a créé une et qui ne
            // l'était pas encore ; on ne rétrograde jamais personne, et un
            // collaborateur simplement rattaché n'est pas concerné.
            if ($user->role === 'comptable' && Company::where('user_id', $user->id)->exists()) {
                $user->role = 'admin';
            }

            if ($nouveau === 'entreprise') {
                $companyId = (int) $comptabilites[0];
                $user->company_id = $companyId;

                $company = Company::find($companyId);
                if ($company) {
                    $company->pack = 'entreprise';
                    $company->save();
                    // Le titulaire reste responsable de sa comptabilité :
                    // sans ce rattachement, il ne pourrait plus ouvrir d'exercice.
                    $company->associatedUsers()->syncWithoutDetaching([$user->id => ['role' => 'admin']]);
                }
            } else {
                Company::where('user_id', $user->id)->update(['pack' => 'cabinet']);
            }

            $user->save();

            // Le changement d'offre ne doit jamais retirer d'accès métier.
            $user->accorderToutesLesHabilitationsMetier();
        });

        $this->tracer($user, $avant, ['pack' => $user->pack, 'role' => $user->role]);

        $sens = $nouveau === 'cabinet' ? 'Montée en gamme appliquée' : 'Retour au Pack Entreprise appliqué';

        $espace = $user->peutCreerDesSocietes()
            ? "avec la création de sociétés et la fusion"
            : "sur une seule comptabilité, sans création ni fusion";

        return redirect()->route('superadmin.packs')->with(
            'success',
            $sens . ' : ' . $user->name . ' ' . $user->last_name . ' passe au '
                . $user->libellePack() . ', ' . $espace . '.'
        );
    }

    /** Identifiants des comptabilités qu'un compte gère, tous rattachements confondus. */
    private function comptabilitesDe(User $user): array
    {
        $creees = Company::where('user_id', $user->id)->pluck('id')->all();
        $affectees = DB::table('company_user')->where('user_id', $user->id)->pluck('company_id')->all();
        $historique = $user->company_id ? [$user->company_id] : [];

        return array_values(array_unique(array_map('intval', array_merge($creees, $affectees, $historique))));
    }

    private function tracer(User $user, array $avant, array $apres): void
    {
        AuditLog::create([
            'user_id'     => Auth::id(),
            'company_id'  => $user->company_id,
            'action'      => 'UPDATE',
            'model_type'  => User::class,
            'model_id'    => $user->id,
            'description' => "Changement d'offre pour " . $user->name . ' ' . $user->last_name
                . ' : ' . ($avant['pack'] === 'entreprise' ? 'Pack Entreprise' : 'Pack Cabinet')
                . ' → ' . $user->libellePack(),
            'payload'     => ['old' => $avant, 'new' => $apres],
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
