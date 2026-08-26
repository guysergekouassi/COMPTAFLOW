<?php

namespace Tests\Feature;

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
            'À activer quand la tolérance de transition tombe : retirer le '
            . 'bloc marqué TOLÉRANCE DE TRANSITION dans VerifieCleEntreprise::handle() '
            . 'et le `??` de ExternalSyncController::entrepriseDeLaRequete(), puis '
            . 'supprimer cette ligne et test_la_tolerance_de_transition_laisse_encore_passer_le_secret_seul().'
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
    // Utilitaires
    // ═════════════════════════════════════════════════════════════════════════

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
