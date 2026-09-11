<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Une clé de liaison par dossier — `X-Company-Key`.
 *
 * Selflow et Comptaflow s'authentifiaient par un secret unique partagé, le même
 * des deux côtés. Ce secret ne dit pas **quelle entreprise appelle** : c'était
 * le corps de la requête qui l'annonçait, et Comptaflow le croyait sur parole.
 * Quiconque détenait le secret pouvait donc écrire dans les livres de n'importe
 * quelle entreprise en changeant un entier dans un JSON.
 *
 * Les trois premières épreuves sont la simulation de cette attaque.
 *
 * Comme `DeversementReferentielTest`, ces épreuves montent leur propre schéma
 * plutôt que de rejouer les migrations de l'application : plusieurs d'entre
 * elles portent du SQL MySQL (`ALTER TABLE … MODIFY COLUMN ENUM`) que SQLite ne
 * sait pas lire.
 */
class LiaisonCleParEntrepriseTest extends TestCase
{
    private const SECRET = 'secret-serveur-de-test';

    /** Le dossier A, et l'entreprise Selflow qui lui est liée. */
    private const DOSSIER_A = 42;
    private const SELFLOW_A = 7;

    /** Le dossier B — celui dans lequel on ne doit pas pouvoir écrire. */
    private const DOSSIER_B = 43;
    private const SELFLOW_B = 8;

    private string $cleA;
    private string $cleB;

    protected function setUp(): void
    {
        parent::setUp();

        config(['external_sync.external_sync_secret' => self::SECRET]);
        Mail::fake();

        $this->monterLeSchema();

        DB::table('users')->insert(['id' => 1, 'name' => 'Comptable']);

        $this->cleA = $this->creerUnDossier(self::DOSSIER_A, self::SELFLOW_A, 'ELIKET MARKET');
        $this->cleB = $this->creerUnDossier(self::DOSSIER_B, self::SELFLOW_B, 'CDCI DISTRIBUTION');
    }

    // ═════════════════════════════════════════════════════════════════════════
    // L'écriture croisée — la simulation d'attaque
    // ═════════════════════════════════════════════════════════════════════════

    public function test_la_cle_du_dossier_A_avec_lidentifiant_de_B_est_refusee_403(): void
    {
        $this->postJson('/api/external/ecritures/deverser', [
            'secret'             => self::SECRET,
            'selflow_company_id' => self::SELFLOW_B,
            'ecritures'          => [$this->uneEcriture()],
        ], ['X-Company-Key' => $this->cleA])
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function test_aucune_ecriture_nentre_chez_B(): void
    {
        $this->postJson('/api/external/ecritures/deverser', [
            'secret'             => self::SECRET,
            'selflow_company_id' => self::SELFLOW_B,
            'ecritures'          => [$this->uneEcriture()],
        ], ['X-Company-Key' => $this->cleA])->assertStatus(403);

        // Ni chez B — l'entreprise visée — ni chez A, qui n'a rien demandé.
        $this->assertSame(0, DB::table('ecriture_comptables')->count());

        // Et le déversement refusé n'a pas daté une réception qui n'a pas eu lieu.
        $this->assertNull(DB::table('companies')->where('id', self::DOSSIER_B)->value('selflow_last_deposit_at'));
    }

    public function test_le_refus_journalise_les_deux_identifiants(): void
    {
        // Le journal doit permettre de distinguer un déploiement mal configuré
        // d'une tentative d'écriture croisée. Sans les deux identifiants, la
        // ligne dit qu'un appel a été refusé, pas qui écrivait chez qui.
        $lignes = [];
        Log::listen(function ($message) use (&$lignes) {
            $lignes[] = $message;
        });

        $this->postJson('/api/external/ecritures/deverser', [
            'secret'             => self::SECRET,
            'selflow_company_id' => self::SELFLOW_B,
            'ecritures'          => [$this->uneEcriture()],
        ], ['X-Company-Key' => $this->cleA])->assertStatus(403);

        $croisement = collect($lignes)->first(
            fn ($l) => str_contains($l->message, 'écriture croisée')
        );

        $this->assertNotNull($croisement, 'Le refus d\'écriture croisée n\'a rien journalisé.');
        $this->assertSame(self::DOSSIER_A, $croisement->context['dossier_de_la_cle']);
        $this->assertSame(self::SELFLOW_A, $croisement->context['selflow_de_la_cle']);
        $this->assertSame(self::SELFLOW_B, $croisement->context['selflow_annonce_dans_corps']);
    }

    public function test_401_et_403_ne_sont_pas_confondus(): void
    {
        // Clé inconnue : l'appelant n'est pas authentifié — le cas d'un
        // déploiement mal configuré.
        $this->deverserChez(self::SELFLOW_A, 'cptf_live_cette-cle-nexiste-pas')
            ->assertStatus(401);

        // Clé valide mais qui désigne un autre dossier : l'appelant est connu,
        // et il écrit chez quelqu'un d'autre. C'est celui-là qu'on veut voir
        // arriver dans le journal.
        $this->deverserChez(self::SELFLOW_B, $this->cleA)
            ->assertStatus(403);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // L'authentification
    // ═════════════════════════════════════════════════════════════════════════

    public function test_la_tolerance_de_transition_laisse_encore_passer_le_secret_seul(): void
    {
        // Cette épreuve **documente la porte encore ouverte**, elle ne la
        // valide pas : tant que la tolérance est là, le secret partagé suffit à
        // écrire dans n'importe quel dossier. Elle échouera le jour où la
        // tolérance tombera — et ce jour-là, c'est
        // `test_sans_cle_le_refus_sera_401` qu'il faudra activer à sa place.
        $this->postJson('/api/external/ecritures/deverser', [
            'secret'             => self::SECRET,
            'selflow_company_id' => self::SELFLOW_B,
            'ecritures'          => [$this->uneEcriture()],
        ])->assertOk();

        $this->assertSame(1, DB::table('ecriture_comptables')
            ->where('company_id', self::DOSSIER_B)->count());
    }

    public function test_sans_cle_le_refus_sera_401(): void
    {
        $this->markTestSkipped(
            'À activer quand les tolérances de transition tombent. Il y en a '
            . 'QUATRE, et elles se retirent ensemble : VerifieCleEntreprise::handle(), '
            . 'le repli de ExternalSyncController::entrepriseDeLaRequete(), celui de '
            . 'ExternalCompanyController::entrepriseDeLaRequete() — ce troisième ne '
            . 'figurait dans aucun décompte avant le lot 22 de Selflow — et, chez '
            . 'Selflow, ExternalSyncControleur::entrepriseDeLaCle(). Puis supprimer '
            . 'cette ligne et test_la_tolerance_de_transition_laisse_encore_passer_le_secret_seul().'
        );

        $this->postJson('/api/external/ecritures/deverser', [
            'secret'             => self::SECRET,
            'selflow_company_id' => self::SELFLOW_B,
            'ecritures'          => [$this->uneEcriture()],
        ])->assertStatus(401);

        $this->assertSame(0, DB::table('ecriture_comptables')->count());
    }

    public function test_une_cle_inconnue_est_refusee_et_rien_nentre_en_base(): void
    {
        $this->deverserChez(self::SELFLOW_A, 'cptf_live_' . str_repeat('x', 40))
            ->assertStatus(401);

        $this->assertSame(0, DB::table('ecriture_comptables')->count());
    }

    public function test_une_cle_revoquee_est_refusee_en_nommant_la_revocation(): void
    {
        DB::table('companies')->where('id', self::DOSSIER_A)
            ->update(['selflow_sync_key_revoked_at' => '2026-03-12 10:00:00']);

        $reponse = $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertStatus(401);

        // « Clé inconnue » enverrait l'entreprise chercher une panne de réseau
        // toute la journée. Le message doit nommer la révocation, et la dater.
        $message = $reponse->json('message');
        $this->assertStringContainsString('révoquée', $message);
        $this->assertStringContainsString('12/03/2026', $message);

        $this->assertSame(0, DB::table('ecriture_comptables')->count());
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Le provisionnement
    // ═════════════════════════════════════════════════════════════════════════

    public function test_provision_ouvre_le_dossier_et_rend_une_cle(): void
    {
        $reponse = $this->provisionner(99)->assertOk();

        $cle = $reponse->json('sync_key');
        $this->assertNotEmpty($cle);
        // Le préfixe permet de reconnaître un secret quand il traîne dans un
        // journal ou un presse-papiers, et donc de savoir qu'il faut le révoquer.
        $this->assertStringStartsWith('cptf_live_', $cle);

        $dossier = DB::table('companies')->where('id', $reponse->json('company_id'))->first();
        $this->assertSame('NOUVELLE ENTREPRISE', $dossier->company_name);
        $this->assertSame(99, (int) $dossier->selflow_company_id);

        // La clé n'est lisible dans aucune colonne : la recherche porte sur son
        // haché, et la copie de secours est chiffrée.
        $this->assertNull($dossier->selflow_sync_key);
        $this->assertSame(hash('sha256', $cle), $dossier->selflow_sync_key_hash);
        $this->assertNotSame($cle, $dossier->selflow_sync_key_chiffree);
        $this->assertSame($cle, Crypt::decryptString($dossier->selflow_sync_key_chiffree));

        // Et elle désigne bien ce dossier-là, et lui seul.
        $this->postJson('/api/external/companies/verify', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => 99,
            'comptaflow_company_id' => $dossier->id,
        ], ['X-Company-Key' => $cle])->assertOk()->assertJson([
            'company_id' => $dossier->id,
            'active'     => true,
        ]);

        $this->postJson('/api/external/companies/verify', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => self::SELFLOW_A,
            'comptaflow_company_id' => self::DOSSIER_A,
        ], ['X-Company-Key' => $cle])->assertStatus(403);
    }

    public function test_provision_rejouee_rend_la_meme_cle_et_un_seul_dossier(): void
    {
        // Une validation cliquée deux fois, ou un appel rejoué après un délai
        // réseau, ouvrirait sinon un second livre pour la même entreprise, et
        // les écritures se partageraient entre les deux.
        $premiere = $this->provisionner(99)->assertOk();
        $seconde  = $this->provisionner(99)->assertOk();

        $this->assertSame($premiere->json('sync_key'), $seconde->json('sync_key'));
        $this->assertSame($premiere->json('company_id'), $seconde->json('company_id'));
        $this->assertSame(1, DB::table('companies')->where('selflow_company_id', 99)->count());
    }

    public function test_provision_avec_un_secret_invalide_nouvre_rien(): void
    {
        $this->provisionner(99, 'mauvais-secret')->assertStatus(401);

        $this->assertSame(0, DB::table('companies')->where('selflow_company_id', 99)->count());
    }

    public function test_sans_secret_configure_provision_refuse_tout(): void
    {
        // Un secret non configuré ne vaut pas « pas de contrôle » : cette route
        // crée des dossiers et rend des clés.
        config(['external_sync.external_sync_secret' => null]);

        $this->provisionner(99, null)->assertStatus(401);
        $this->assertSame(0, DB::table('companies')->where('selflow_company_id', 99)->count());
    }

    public function test_selflow_company_id_reste_unique(): void
    {
        // Deux dossiers rattachés à la même entreprise Selflow rendraient le
        // déversement indéterminé.
        $this->provisionner(self::SELFLOW_A)->assertOk();

        $this->assertSame(1, DB::table('companies')
            ->where('selflow_company_id', self::SELFLOW_A)->count());
    }

    public function test_tier_digits_prend_bien_six(): void
    {
        // Selflow numérote ses tiers sur six caractères, préfixe compris.
        // À huit, aucun numéro ne correspond et chaque écriture retombe sur le
        // compte collectif.
        $reponse = $this->provisionner(99)->assertOk();

        $this->assertSame(6, (int) DB::table('companies')
            ->where('id', $reponse->json('company_id'))->value('tier_digits'));
    }

    public function test_le_compte_ouvert_accepte_le_mot_de_passe_selflow(): void
    {
        // Le compte Comptaflow **est** le compte Selflow : même adresse, même
        // mot de passe. C'est l'empreinte bcrypt de Selflow qui voyage, et elle
        // doit être rangée telle quelle — la re-hacher rendrait le compte
        // inaccessible avec le mot de passe que l'utilisateur connaît déjà.
        // C'est la seule épreuve qui prouve qu'elle n'a pas été re-hachée.
        $empreinte = Hash::make('le-mot-de-passe');

        $this->provisionner(99, self::SECRET, $empreinte)->assertOk();

        $admin = User::where('email_adresse', 'gerant@nouvelle.ci')->first();

        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('le-mot-de-passe', $admin->password));
        $this->assertSame($empreinte, $admin->password);
        $this->assertSame('Konan', $admin->name);
        $this->assertSame('Yao', $admin->last_name);

        // Le client se connecte avec ce qu'il a déjà : lui envoyer « choisissez
        // un mot de passe » l'inviterait à en poser un second sans le vouloir.
        $this->assertNull($admin->activation_token);
        Mail::assertNothingSent();
    }

    public function test_aucun_mot_de_passe_en_clair_n_entre_par_le_provisionnement(): void
    {
        // L'ancienne route `register-enterprise` faisait choisir par le
        // superadministrateur Selflow le mot de passe du compte d'un client, et
        // le transportait en clair dans le corps de la requête.
        $lignes = [];
        Log::listen(function ($message) use (&$lignes) {
            $lignes[] = $message;
        });

        $empreinte = Hash::make('le-mot-de-passe');
        $reponse = $this->provisionner(99, self::SECRET, $empreinte);

        $reponse->assertOk();

        // Le corps ne porte aucun champ `admin_password`…
        $corps = $this->corpsDeProvision(99, $empreinte);
        $this->assertArrayNotHasKey('admin_password', $corps['entreprise']);
        $this->assertStringNotContainsString('le-mot-de-passe', json_encode($corps));

        // …rien de lisible ne finit en base…
        $admin = DB::table('users')->where('email_adresse', 'gerant@nouvelle.ci')->first();
        $this->assertStringStartsWith('$2y$', $admin->password);
        $this->assertStringNotContainsString('le-mot-de-passe', $admin->password);

        // …ni la réponse, ni le journal ne reprennent l'empreinte — elle
        // s'attaque hors ligne, et ce journal est lu par du monde.
        $this->assertStringNotContainsString($empreinte, $reponse->getContent());
        foreach ($lignes as $ligne) {
            $this->assertStringNotContainsString($empreinte, $ligne->message . json_encode($ligne->context));
            $this->assertStringNotContainsString('le-mot-de-passe', $ligne->message . json_encode($ligne->context));
        }
    }

    public function test_sans_empreinte_le_repli_ouvre_un_compte_inutilisable_et_envoie_le_lien(): void
    {
        // Le repli, pas le cas normal : un connecteur Selflow antérieur à ce
        // contrat n'envoie pas d'empreinte.
        $this->provisionner(99)->assertOk();

        $admin = User::where('email_adresse', 'gerant@nouvelle.ci')->first();

        $this->assertNotNull($admin->activation_token, 'Aucun lien d\'activation n\'a été préparé.');
        Mail::assertSent(\App\Mail\LienActivationMail::class, fn ($mail) => $mail->hasTo('gerant@nouvelle.ci'));
    }

    public function test_un_mot_de_passe_en_clair_presente_comme_empreinte_est_refuse(): void
    {
        // Un connecteur mal réglé qui enverrait le mot de passe en clair sous
        // `admin_password_hash` le verrait rangé tel quel dans `password` : le
        // compte deviendrait inouvrable, et le mot de passe serait en base en
        // clair. Il vaut mieux refuser l'appel.
        $this->provisionner(99, self::SECRET, 'le-mot-de-passe')->assertStatus(422);

        $this->assertSame(0, DB::table('companies')->where('selflow_company_id', 99)->count());
    }

    // ═════════════════════════════════════════════════════════════════════════
    // La révocation
    // ═════════════════════════════════════════════════════════════════════════

    public function test_revoke_ferme_la_cle(): void
    {
        $this->revoquer(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertOk();

        $this->assertNotNull(DB::table('companies')->where('id', self::DOSSIER_A)
            ->value('selflow_sync_key_revoked_at'));

        $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertStatus(401);
    }

    public function test_revoke_ne_supprime_pas_le_dossier(): void
    {
        // Délier n'est pas supprimer : les écritures déjà reçues sont la
        // comptabilité de l'entreprise, et elle en répond devant
        // l'administration fiscale bien après la fin de son abonnement.
        $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertOk();
        $this->assertSame(1, DB::table('ecriture_comptables')->count());

        $this->revoquer(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertOk();

        $this->assertNotNull(DB::table('companies')->where('id', self::DOSSIER_A)->first());
        $this->assertSame(1, DB::table('ecriture_comptables')->count());
    }

    public function test_une_cle_revoquee_puis_reprovisionnee_est_une_nouvelle_cle(): void
    {
        $this->revoquer(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertOk();

        $nouvelle = $this->provisionner(self::SELFLOW_A)->assertOk()->json('sync_key');

        $this->assertNotSame($this->cleA, $nouvelle);
        $this->assertSame(self::DOSSIER_A, (int) DB::table('companies')
            ->where('selflow_company_id', self::SELFLOW_A)->value('id'));

        // L'ancienne ne redevient jamais valide.
        $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertStatus(401);
        $this->deverserChez(self::SELFLOW_A, $nouvelle)->assertOk();
    }

    public function test_verify_dit_letat_reel_de_la_liaison(): void
    {
        // Le bouton « vérifier la liaison » de Selflow écrivait la date du jour
        // et annonçait « Liaison active » sans interroger personne.
        $this->postJson('/api/external/companies/verify', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => self::SELFLOW_A,
            'comptaflow_company_id' => self::DOSSIER_A,
        ], ['X-Company-Key' => $this->cleA])->assertOk()->assertJson(['active' => true]);

        $this->revoquer(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertOk();

        // Une fois révoquée, la clé ne passe plus le filtre : Selflow lit un
        // refus qui nomme la révocation, et non plus « Liaison active ».
        $this->postJson('/api/external/companies/verify', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => self::SELFLOW_A,
            'comptaflow_company_id' => self::DOSSIER_A,
        ], ['X-Company-Key' => $this->cleA])->assertStatus(401);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Le référentiel, et la date de réception
    // ═════════════════════════════════════════════════════════════════════════

    public function test_un_tiers_deverse_entre_avec_son_compte_de_rattachement(): void
    {
        // `plan_tiers.compte_general` était `NOT NULL` avec une clé étrangère,
        // et aucun chemin d'import ne le renseignait : l'insertion tombait sur
        // une violation d'intégrité et **aucun tiers n'entrait**.
        $this->deverserLeReferentiel($this->cleA)->assertOk();

        $tiers = DB::table('plan_tiers')->where('numero_de_tiers', '411001')->first();

        $this->assertNotNull($tiers, 'Le tiers n\'est pas entré.');
        $this->assertSame(
            DB::table('plan_comptables')->where('numero_de_compte', '411000')->value('id'),
            $tiers->compte_general
        );
    }

    public function test_le_releve_dun_client_est_etablissable_apres_deversement(): void
    {
        $this->deverserLeReferentiel($this->cleA)->assertOk();

        $this->postJson('/api/external/ecritures/deverser', [
            'secret'             => self::SECRET,
            'selflow_company_id' => self::SELFLOW_A,
            'ecritures'          => [array_merge($this->uneEcriture(), [
                'compte_debit' => '411000',
                'compte_tiers' => '411001',
            ])],
        ], ['X-Company-Key' => $this->cleA])->assertOk();

        $tiersId = DB::table('plan_tiers')->where('numero_de_tiers', '411001')->value('id');

        // Le relevé du client : ses lignes, et non celles du compte collectif.
        $releve = DB::table('ecriture_comptables')
            ->where('company_id', self::DOSSIER_A)
            ->where('plan_tiers_id', $tiersId)
            ->get();

        $this->assertCount(1, $releve);
        $this->assertEquals(150000, $releve->first()->debit);
    }

    public function test_un_deversement_accepte_date_la_reception(): void
    {
        // Selflow affiche cette date à l'entreprise, et il l'écrivait au moment
        // de l'*envoi* : elle datait une réception qui n'avait pas eu lieu.
        $this->assertNull(DB::table('companies')->where('id', self::DOSSIER_A)
            ->value('selflow_last_deposit_at'));

        $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertOk();

        $this->assertNotNull(DB::table('companies')->where('id', self::DOSSIER_A)
            ->value('selflow_last_deposit_at'));
    }

    // ═════════════════════════════════════════════════════════════════════════
    // La clé présentée à Selflow — les appels sortants
    // ═════════════════════════════════════════════════════════════════════════

    public function test_la_cle_se_relit_pour_etre_presentee_a_selflow(): void
    {
        // `company-info` et `tier-info` de Selflow honorent maintenant
        // `X-Company-Key` : c'est la **même** clé des deux côtés, elle nomme la
        // paire (entreprise Selflow ↔ dossier Comptaflow).
        $dossier = Company::find(self::DOSSIER_A);

        $this->assertSame($this->cleA, $dossier->cleDeLiaisonEnClair());
        $this->assertSame(['X-Company-Key' => $this->cleA], $dossier->enTeteDeLiaison());
    }

    public function test_une_cle_revoquee_nest_pas_presentee_a_selflow(): void
    {
        // La présenter ferait un 401 côté Selflow là où l'appelant peut
        // simplement s'abstenir — et l'en-tête vide, lui, vaudrait « clé
        // inconnue » au lieu de « pas de clé ».
        $dossier = Company::find(self::DOSSIER_A);
        $dossier->forceFill(['selflow_sync_key_revoked_at' => now()])->save();

        $this->assertNull($dossier->cleDeLiaisonEnClair());
        $this->assertSame([], $dossier->enTeteDeLiaison());
    }

    public function test_un_dossier_sans_liaison_ne_presente_aucun_en_tete(): void
    {
        $dossier = Company::find(self::DOSSIER_A);
        $dossier->forceFill(['selflow_sync_key_chiffree' => null])->save();

        $this->assertSame([], $dossier->enTeteDeLiaison());
    }

    public function test_delier_revoque_la_cle_au_lieu_de_leffacer(): void
    {
        // Vider `selflow_sync_key` ne suffit plus : depuis que la
        // reconnaissance porte sur le haché, un dossier délié ainsi aurait
        // gardé une clé parfaitement valide, et Selflow aurait continué
        // d'écrire dans ses livres.
        $dossier = Company::find(self::DOSSIER_A);

        (new \App\Http\Controllers\Super\SuperAdminLiaisonController())->destroy($dossier->id);

        $frais = Company::find(self::DOSSIER_A);
        $this->assertNull($frais->selflow_company_id);
        $this->assertNotNull($frais->selflow_sync_key_revoked_at);
        // Le haché reste : un appel refusé peut dire « révoquée le … » plutôt
        // que « inconnue ».
        $this->assertNotNull($frais->selflow_sync_key_hash);

        $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertStatus(401);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Le renouvellement de la clé, et la période de grâce
    //
    // Une clé posée une fois et jamais changée ouvre le dossier comptable d'une
    // entreprise aussi longtemps qu'il existe. La rotation borne la durée de vie
    // d'une fuite à un mois — mais elle casserait le déversement une fois par
    // mois, au hasard, sans la grâce que ces épreuves vérifient.
    // ═════════════════════════════════════════════════════════════════════════

    public function test_rotate_key_rend_une_cle_neuve_et_lancienne_cesse_de_servir(): void
    {
        $reponse = $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA);

        $reponse->assertStatus(200)->assertJson(['success' => true]);
        $neuve = $reponse->json('sync_key');

        $this->assertNotSame($this->cleA, $neuve);
        $this->assertStringStartsWith('cptf_live_', $neuve);

        // La clé rendue ouvre le dossier ; celle d'avant, une fois la grâce
        // passée, ne l'ouvre plus.
        $this->deverserChez(self::SELFLOW_A, $neuve)->assertStatus(200);

        $dossier = Company::find(self::DOSSIER_A);
        $this->assertSame(hash('sha256', $neuve), $dossier->selflow_sync_key_hash);
        $this->assertSame(hash('sha256', $this->cleA), $dossier->selflow_sync_key_hash_precedente);
        $this->assertNotNull($dossier->selflow_sync_key_rotated_at);
    }

    public function test_une_requete_partie_avant_la_rotation_est_encore_acceptee(): void
    {
        // **L'épreuve qui compte.** Un déversement parti à l'instant précis du
        // renouvellement porte encore l'ancienne clé et arrive après elle. Sans
        // grâce, il échoue — rarement, une fois par mois au pire, et sans qu'on
        // comprenne pourquoi. C'est le genre de défaut qu'on met six mois à
        // diagnostiquer, parce qu'il ne se reproduit pas quand on le cherche.
        $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertStatus(200);

        $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertStatus(200);

        $this->assertDatabaseHas('ecriture_comptables', ['company_id' => self::DOSSIER_A]);
    }

    public function test_l_ancienne_cle_ne_vaut_plus_apres_la_grace(): void
    {
        $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertStatus(200);

        $this->travel(Company::MINUTES_DE_GRACE + 1)->minutes();

        $refus = $this->deverserChez(self::SELFLOW_A, $this->cleA);

        $refus->assertStatus(401);
        // Le refus nomme sa cause : « clé inconnue » enverrait chercher une
        // panne de réseau là où la réponse tient en une ligne.
        $this->assertStringContainsString('renouvelée', $refus->json('message'));

        // Et l'ancienne n'est pas gardée « au cas où » : le haché périmé est
        // retiré de la base au passage.
        $this->assertNull(Company::find(self::DOSSIER_A)->selflow_sync_key_hash_precedente);
    }

    public function test_la_cle_courante_est_essayee_avant_la_precedente(): void
    {
        // L'ordre n'est pas une commodité. On range ici, en clé *précédente* du
        // dossier A, le haché de la clé **courante** de B : présentée, elle doit
        // désigner B — son propriétaire actuel — et non A.
        Company::find(self::DOSSIER_A)->forceFill([
            'selflow_sync_key_hash_precedente'      => hash('sha256', $this->cleB),
            'selflow_sync_key_precedente_expire_at' => now()->addMinutes(5),
        ])->save();

        // Écrire chez B avec la clé de B : accepté, c'est bien B qu'elle désigne.
        $this->deverserChez(self::SELFLOW_B, $this->cleB)->assertStatus(200);

        // Et chez A, elle est refusée — la grâce de A ne la fait pas passer.
        $this->deverserChez(self::SELFLOW_A, $this->cleB)->assertStatus(403);
        $this->assertDatabaseMissing('ecriture_comptables', ['company_id' => self::DOSSIER_A]);
    }

    public function test_une_cle_revoquee_puis_reprovisionnee_ne_revient_pas_par_la_grace(): void
    {
        // Le même danger, par l'autre bout : si poser une clé neuve laissait la
        // grâce en place, une clé révoquée redeviendrait valide **par l'arrière**
        // au premier reprovisionnement — la révocation tombe, et l'ancienne clé
        // encore rangée en « précédente » rouvrirait la porte.
        $dossier = Company::find(self::DOSSIER_A);
        $dossier->forceFill([
            'selflow_sync_key_hash_precedente'      => hash('sha256', 'cptf_live_ancienne_cle_fuitee'),
            'selflow_sync_key_precedente_expire_at' => now()->addMinutes(5),
        ])->save();

        $dossier->poserUneCleDeLiaison();

        $frais = Company::find(self::DOSSIER_A);
        $this->assertNull($frais->selflow_sync_key_hash_precedente);
        $this->assertNull($frais->selflow_sync_key_precedente_expire_at);
    }

    public function test_un_renouvellement_rejoue_ne_produit_pas_deux_cles_valides_de_plus(): void
    {
        // Selflow n'écrit rien tant qu'il n'a pas la nouvelle clé en main : si la
        // réponse se perd, il rejoue avec la seule clé qu'il ait — l'ancienne.
        // Lui en tirer une troisième condamnerait sur-le-champ celle qu'il vient
        // de recevoir sans le savoir. La grâce garde **une** clé précédente, pas
        // une pile.
        $premiere = $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->json('sync_key');

        $rejeu = $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA);

        $rejeu->assertStatus(200);
        $this->assertSame($premiere, $rejeu->json('sync_key'));
        $this->assertTrue($rejeu->json('replayed'));

        // Deux clés valides en tout, jamais trois : la neuve et celle d'avant.
        $dossier = Company::find(self::DOSSIER_A);
        $this->assertSame(hash('sha256', $premiere), $dossier->selflow_sync_key_hash);
        $this->assertSame(hash('sha256', $this->cleA), $dossier->selflow_sync_key_hash_precedente);
    }

    public function test_le_renouvellement_exige_la_cle_actuelle(): void
    {
        // Pas de tolérance de transition sur ce point d'entrée, et c'est
        // délibéré : un renouvellement sans clé serait une prise de liaison en un
        // appel — l'appelant repart avec la clé neuve, le détenteur légitime est
        // coupé cinq minutes plus tard.
        $sansCle = $this->postJson('/api/external/companies/rotate-key', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => self::SELFLOW_A,
            'comptaflow_company_id' => self::DOSSIER_A,
        ]);

        $sansCle->assertStatus(401);
        $this->assertSame(
            hash('sha256', $this->cleA),
            Company::find(self::DOSSIER_A)->selflow_sync_key_hash
        );
    }

    public function test_le_renouvellement_avec_la_cle_dun_autre_dossier_est_refuse(): void
    {
        $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleB)->assertStatus(403);

        // La clé de A n'a pas bougé : personne n'a coupé sa liaison en passant.
        $this->assertSame(
            hash('sha256', $this->cleA),
            Company::find(self::DOSSIER_A)->selflow_sync_key_hash
        );
    }

    public function test_le_renouvellement_dune_cle_revoquee_est_refuse(): void
    {
        $this->revoquer(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertStatus(200);

        $refus = $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA);

        $refus->assertStatus(401);
        $this->assertStringContainsString('révoquée', $refus->json('message'));
    }

    public function test_revoquer_efface_la_grace_en_cours(): void
    {
        $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->assertStatus(200);
        $this->assertNotNull(Company::find(self::DOSSIER_A)->selflow_sync_key_hash_precedente);

        $neuve = Company::find(self::DOSSIER_A)->cleDeLiaisonEnClair();
        $this->revoquer(self::DOSSIER_A, self::SELFLOW_A, $neuve)->assertStatus(200);

        $frais = Company::find(self::DOSSIER_A);
        $this->assertNull($frais->selflow_sync_key_hash_precedente);
        // La clé de grâce ne survit pas à la coupure de la liaison.
        $this->deverserChez(self::SELFLOW_A, $this->cleA)->assertStatus(401);
    }

    public function test_verify_date_le_dernier_renouvellement(): void
    {
        // C'est cette date que la tâche mensuelle de Selflow regarde pour savoir
        // quelles clés ont passé trente jours.
        $neuve = $this->renouveler(self::DOSSIER_A, self::SELFLOW_A, $this->cleA)->json('sync_key');

        $etat = $this->postJson('/api/external/companies/verify', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => self::SELFLOW_A,
            'comptaflow_company_id' => self::DOSSIER_A,
        ], ['X-Company-Key' => $neuve]);

        $etat->assertStatus(200);
        $this->assertNotNull($etat->json('key_rotated_at'));
    }

    // ═════════════════════════════════════════════════════════════════════════
    // Utilitaires
    // ═════════════════════════════════════════════════════════════════════════

    private function renouveler(int $dossier, int $selflow, string $cle)
    {
        return $this->postJson('/api/external/companies/rotate-key', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => $selflow,
            'comptaflow_company_id' => $dossier,
        ], ['X-Company-Key' => $cle]);
    }

    private function deverserChez(int $selflowCompanyId, ?string $cle)
    {
        return $this->postJson('/api/external/ecritures/deverser', [
            'secret'             => self::SECRET,
            'selflow_company_id' => $selflowCompanyId,
            'ecritures'          => [$this->uneEcriture()],
        ], $cle ? ['X-Company-Key' => $cle] : []);
    }

    private function deverserLeReferentiel(string $cle)
    {
        return $this->postJson('/api/external/referentiel/deverser', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => self::SELFLOW_A,
            'comptaflow_company_id' => self::DOSSIER_A,
            'plan_comptable'        => [
                ['numero_de_compte' => '411000', 'intitule' => 'Clients'],
            ],
            'tiers' => [
                ['numero_de_tiers' => '411001', 'intitule' => 'Konan Yao',
                 'type_de_tiers' => 'client', 'compte_general' => '411000'],
            ],
        ], ['X-Company-Key' => $cle]);
    }

    private function provisionner(int $selflowCompanyId, ?string $secret = self::SECRET, ?string $empreinte = null)
    {
        $corps = $this->corpsDeProvision($selflowCompanyId, $empreinte);

        if ($secret !== null) {
            $corps['secret'] = $secret;
        }

        return $this->postJson('/api/external/companies/provision', $corps);
    }

    /** Le corps de `provision`, tel que Selflow le transmet. */
    private function corpsDeProvision(int $selflowCompanyId, ?string $empreinte = null): array
    {
        $entreprise = [
            'nom'               => 'NOUVELLE ENTREPRISE',
            'forme_juridique'   => 'SARL',
            'ncc'               => 'CI-1234567 A',
            'rccm'              => 'CI-ABJ-2026-B-1234',
            'regime_imposition' => 'RSI',
            'adresse'           => 'Cocody, Abidjan',
            'telephone'         => '+225 07 00 00 00',
            'email'             => 'contact@nouvelle.ci',
            'admin_nom'         => 'Konan',
            'admin_prenom'      => 'Yao',
            'admin_email'       => 'gerant@nouvelle.ci',
        ];

        if ($empreinte !== null) {
            $entreprise['admin_password_hash'] = $empreinte;
        }

        return [
            'selflow_company_id' => $selflowCompanyId,
            'entreprise'         => $entreprise,
            'numerotation_tiers' => 'numeric',
            'longueur_tiers'     => 6,
        ];
    }

    private function revoquer(int $dossier, int $selflow, string $cle)
    {
        return $this->postJson('/api/external/companies/revoke', [
            'secret'                => self::SECRET,
            'selflow_company_id'    => $selflow,
            'comptaflow_company_id' => $dossier,
        ], ['X-Company-Key' => $cle]);
    }

    private function uneEcriture(): array
    {
        return [
            'cle_selflow'        => 'SELFLOW-' . uniqid(),
            'date_ecriture'      => '2026-06-15',
            'code_journal'       => 'VTE',
            'libelle'            => 'Facture 2026-042',
            'reference_document' => 'FA-042',
            'compte_debit'       => '411000',
            'debit'              => 150000,
            'credit'             => 0,
        ];
    }

    /**
     * Un dossier lié, sa clé rendue en clair — comme `provision` la rendrait.
     */
    private function creerUnDossier(int $id, int $selflowId, string $nom): string
    {
        $cle = 'cptf_live_' . str_pad((string) $id, 40, 'k');

        DB::table('companies')->insert([
            'id'                    => $id,
            'company_name'          => $nom,
            'user_id'               => 1,
            'selflow_company_id'    => $selflowId,
            'selflow_sync_key_hash' => hash('sha256', $cle),
            'selflow_sync_key_chiffree' => Crypt::encryptString($cle),
            'selflow_linked_at'     => now(),
            'tier_digits'           => 6,
            'tier_id_type'          => 'numeric',
        ]);

        // De quoi recevoir des écritures : un exercice ouvert et un journal.
        DB::table('exercices_comptables')->insert([
            'company_id' => $id, 'user_id' => 1, 'is_active' => true,
            'date_debut' => '2026-01-01', 'date_fin' => '2026-12-31',
        ]);
        DB::table('code_journals')->insert([
            'code_journal' => 'VTE', 'intitule' => 'Ventes', 'type' => 'Ventes',
            'user_id' => 1, 'company_id' => $id,
        ]);

        return $cle;
    }

    private function monterLeSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email_adresse')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('activation_token', 64)->nullable();
            $table->timestamp('activation_token_expires_at')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('activity')->nullable();
            $table->string('juridique_form')->nullable();
            $table->decimal('social_capital', 20, 2)->nullable();
            $table->string('adresse')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email_adresse')->nullable();
            $table->string('ncc')->nullable();
            $table->string('rccm')->nullable();
            $table->string('regime')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('tier_digits')->default(6);
            $table->string('tier_id_type')->default('numeric');
            $table->unsignedBigInteger('selflow_company_id')->nullable()->unique();
            $table->string('selflow_sync_key', 100)->nullable();
            $table->string('selflow_sync_key_hash', 64)->nullable()->unique();
            $table->string('selflow_sync_key_hash_precedente', 64)->nullable();
            $table->timestamp('selflow_sync_key_precedente_expire_at')->nullable();
            $table->timestamp('selflow_sync_key_rotated_at')->nullable();
            $table->text('selflow_sync_key_chiffree')->nullable();
            $table->timestamp('selflow_sync_key_revoked_at')->nullable();
            $table->timestamp('selflow_linked_at')->nullable();
            $table->timestamp('selflow_last_deposit_at')->nullable();
            $table->string('selflow_sync_status')->nullable();
            $table->timestamps();
        });

        Schema::create('treasury_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('plan_comptables', function (Blueprint $table) {
            $table->id();
            $table->string('numero_de_compte');
            $table->string('numero_original')->nullable();
            $table->string('intitule');
            $table->string('type_de_compte')->nullable();
            $table->string('classe')->nullable();
            $table->string('adding_strategy')->default('manuel');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('code_journals', function (Blueprint $table) {
            $table->id();
            $table->string('code_journal');
            $table->string('numero_original')->nullable();
            $table->string('intitule');
            $table->string('type')->nullable();
            $table->unsignedBigInteger('compte_de_tresorerie')->nullable();
            $table->string('compte_de_contrepartie')->nullable();
            $table->boolean('traitement_analytique')->default(false);
            $table->string('rapprochement_sur')->nullable();
            $table->string('poste_tresorerie')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('plan_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('numero_de_tiers');
            $table->string('numero_original')->nullable();
            $table->unsignedBigInteger('compte_general')->nullable();
            $table->string('intitule');
            $table->string('type_de_tiers');
            $table->string('ncc')->nullable();
            $table->string('rccm')->nullable();
            $table->string('compte_contribuable')->nullable();
            $table->string('regime')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->string('adresse')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->unique(['numero_de_tiers', 'company_id']);
        });

        Schema::create('ecriture_comptables', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('n_saisie')->nullable();
            $table->string('description_operation')->nullable();
            $table->string('reference_piece')->nullable();
            $table->string('cle_selflow', 64)->nullable();
            $table->unsignedBigInteger('plan_comptable_id')->nullable();
            $table->unsignedBigInteger('plan_tiers_id')->nullable();
            $table->unsignedBigInteger('code_journal_id')->nullable();
            $table->unsignedBigInteger('exercices_comptables_id')->nullable();
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->string('statut')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
        });

        Schema::create('exercices_comptables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
        });

        // `CodeJournal::created` répercute le journal sur les exercices.
        Schema::create('journaux_saisis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exercices_comptables_id');
            $table->unsignedBigInteger('code_journals_id');
            $table->integer('annee')->nullable();
            $table->integer('mois')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->timestamps();
        });
    }
}
