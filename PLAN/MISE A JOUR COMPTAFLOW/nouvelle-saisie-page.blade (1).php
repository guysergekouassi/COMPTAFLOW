{{--
  Page combinée "Nouvelle saisie" + "Écritures du journal".
  Remplace accounting_entry_real.blade.php.
  Variables attendues depuis EcritureComptableController@index :
  $plansComptables, $plansTiers, $comptesTresorerie, $exerciceActif, $nextSaisieNumber, $ecritures
  À ajouter au controller : $codeJournaux (liste CodeJournal de la company), $modelesSaisie (optionnel).
--}}

<div class="fc-header d-flex flex-wrap align-items-end gap-3 p-3 mb-3"
     style="background:linear-gradient(135deg,#2563eb 0%,#1e3a8a 100%);border-radius:16px;">

    {{-- Exercice : non modifiable ici, c'est celui en cours dans la sidebar --}}
    <div class="d-flex align-items-center gap-2 px-3 py-2"
         style="background:rgba(255,255,255,.15);border-radius:10px;color:#fff;cursor:not-allowed;">
        <i class="bx bx-lock-alt"></i>
        <span class="fw-semibold small">EXERCICE {{ $exerciceActif->libelle ?? $exerciceActif->id }}</span>
        <input type="hidden" id="id_exercice" value="{{ $exerciceActif->id }}">
    </div>

    <div class="d-flex flex-column gap-1">
        <label class="text-white-50 small text-uppercase" style="font-size:10.5px">Journal</label>
        <select id="code_journal_id" class="form-select form-select-sm" style="width:170px">
            <option value="">— Choisir —</option>
            @foreach ($codeJournaux as $j)
                <option value="{{ $j->id }}"
                        data-code_journal_j="{{ $j->code_journal }}"
                        data-intitule_j="{{ $j->intitule }}"
                        data-type_j="{{ $j->type }}"
                        data-contrepartie="{{ $j->compte_de_contrepartie }}">
                    {{ $j->code_journal }} - {{ $j->intitule }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="d-flex flex-column gap-1">
        <label class="text-white-50 small text-uppercase" style="font-size:10.5px">Modèle de saisie</label>
        <select id="modele_saisie" class="form-select form-select-sm" style="width:180px">
            <option value="">— Aucun —</option>
            @foreach ($modelesSaisie ?? [] as $m)
                <option value="{{ $m->id }}">{{ $m->nom }}</option>
            @endforeach
        </select>
    </div>

    {{-- Année réelle de l'exercice actif, nécessaire pour journaux_saisis.find --}}
    <input type="hidden" id="annee_exercice" value="{{ \Carbon\Carbon::parse($exerciceActif->date_debut)->format('Y') }}">

    <div class="d-flex flex-column gap-1">
        <label class="text-white-50 small text-uppercase" style="font-size:10.5px">Mois</label>
        <select id="mois_ecriture" class="form-select form-select-sm" style="width:120px">
            @php $moisNoms = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']; @endphp
            @foreach ($moisNoms as $i => $nom)
                <option value="{{ $i + 1 }}" {{ now()->month == $i + 1 ? 'selected' : '' }}>{{ $nom }}</option>
            @endforeach
        </select>
    </div>

    <div class="d-flex flex-column gap-1">
        <label class="text-white-50 small text-uppercase" style="font-size:10.5px">Jour</label>
        <select id="jour_ecriture" class="form-select form-select-sm" style="width:75px">
            @for ($j = 1; $j <= 31; $j++)
                <option value="{{ $j }}" {{ now()->day == $j ? 'selected' : '' }}>{{ $j }}</option>
            @endfor
        </select>
    </div>

    <div class="d-flex flex-column gap-1">
        <label class="text-white-50 small text-uppercase" style="font-size:10.5px">N° de saisie</label>
        <input id="n_saisie_user" class="form-control form-control-sm" style="width:200px" readonly
               value="{{ $nextSaisieNumber }}">
    </div>

    <div class="d-flex gap-2 ms-auto">
        <button type="button" class="btn btn-outline-light btn-sm fw-semibold" id="btnScannerFacture" onclick="saisieGrille.ouvrirScanner()">
            <i class="bx bx-scan"></i> Scanner facture
        </button>
        <button type="button" class="btn btn-light btn-sm fw-semibold" id="btnNouvelleSaisie" onclick="saisieGrille.toggle()">
            <i class="bx bx-plus"></i> Nouvelle saisie
        </button>
    </div>
</div>

{{-- ===================== BLOC SAISIE (masqué tant qu'on ne clique pas "Nouvelle saisie") ===================== --}}
<div class="fc-card p-3 mb-3" id="panelSaisie" style="display:none;background:#fff;border-radius:20px;box-shadow:0 10px 25px -5px rgba(0,0,0,.05)">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="fw-bold" id="saisieTitre" style="font-size:14px">Nouvelle saisie</div>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnFermerSaisie" onclick="saisieGrille.fermer()">
            <i class="bx bx-x"></i> Fermer la saisie
        </button>
    </div>

    {{-- Carte d'avertissement : visible uniquement si le groupe en cours est déséquilibré --}}
    <div id="carteDesequilibre" class="d-flex align-items-start gap-2 p-3 mb-3"
         style="display:none !important;background:#fff7ed;border:1px solid #fdba74;border-radius:12px;color:#c2410c">
        <i class="bx bx-error-circle" style="font-size:20px"></i>
        <div>
            <div class="fw-semibold" id="carteDesequilibreTitre">Écriture non équilibrée</div>
            <div class="small" id="carteDesequilibreTexte"></div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-2">
        <input id="description_operation" class="form-control form-control-sm" placeholder="Libellé de l'opération">
        <input id="reference_piece" class="form-control form-control-sm" style="width:150px" placeholder="Réf. pièce">
        <label class="btn btn-outline-secondary btn-sm mb-0 d-flex align-items-center gap-1" style="width:180px;cursor:pointer">
            <i class="bx bx-paperclip"></i>
            <span id="pieceLabel">Pièce jointe (facultatif)</span>
            <input type="file" id="piece_justificatif" class="d-none" onchange="saisieGrille.onFichierChoisi(this)">
        </label>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr class="small text-muted text-uppercase">
                    <th style="min-width:210px">Compte général</th>
                    <th style="min-width:190px">Compte tiers</th>
                    <th style="width:110px" class="text-end">Débit</th>
                    <th style="width:110px" class="text-end">Crédit</th>
                    <th style="min-width:170px">Poste trésorerie</th>
                    <th style="width:50px" class="text-center">TVA</th>
                    <th style="width:50px" class="text-center">Analyt.</th>
                    <th style="width:40px"></th>
                </tr>
            </thead>
            <tbody id="grilleBody"></tbody>
        </table>
    </div>

    <div id="contrepartieHint" class="small text-primary d-flex align-items-center gap-1 mt-1" style="display:none !important">
        <i class="bx bx-bulb"></i> <span></span>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2 p-2" style="background:#f8fafc;border-radius:10px">
        <span class="small text-muted">Débit <strong id="totalDebit">0</strong> · Crédit <strong id="totalCredit">0</strong></span>
        <span id="balanceBadge" class="badge bg-danger rounded-pill px-3 py-2">Non équilibré</span>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saisieGrille.ajouterLigne()">
            <i class="bx bx-plus"></i> Ajouter une ligne
        </button>
        <button type="button" class="btn btn-success btn-sm" id="btnValiderGrille" disabled
                style="background:linear-gradient(135deg,#10b981,#059669);border:none"
                onclick="saisieGrille.enregistrer()">Valider &amp; enregistrer</button>
    </div>
</div>

{{-- ===================== BLOC LISTE (même page, filtrée en JS) ===================== --}}
<div class="fc-card p-3" style="background:#fff;border-radius:20px;box-shadow:0 10px 25px -5px rgba(0,0,0,.05)">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <div class="fw-bold" style="font-size:14px">Écritures du journal</div>
            <div class="small text-muted">Filtré instantanément sur le journal sélectionné ci-dessus — pas de rechargement</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm" id="chipDesequilibre" onclick="saisieGrille.toggleFiltreDesequilibre()"
                    style="border:1.5px solid #d1d5db;border-radius:20px;background:#fff">
                <span style="width:7px;height:7px;border-radius:50%;background:#f97316;display:inline-block;margin-right:5px"></span>
                Déséquilibrées uniquement
            </button>
            <span class="small text-muted" id="compteurLignes"></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr class="small text-muted text-uppercase">
                    <th></th><th>Date</th><th>N° saisie</th><th>Statut</th><th>Journal</th>
                    <th>Poste trés.</th><th>Réf.</th><th>Description</th>
                    <th>Cpte gén.</th><th>Cpte tiers</th><th>An.</th>
                    <th class="text-end">Débit</th><th class="text-end">Crédit</th><th>Pièce</th><th>Actions</th>
                </tr>
            </thead>
            <tbody id="listeEcrituresBody"></tbody>
        </table>
    </div>
</div>

<script>
    window.SAISIE_DATA = {
        plansComptables: @json($plansComptables),
        plansTiers: @json($plansTiers),
        comptesTresorerie: @json($comptesTresorerie),
        idExercice: {{ (int) $exerciceActif->id }},
        csrfToken: '{{ csrf_token() }}',
        storeMultipleUrl: '/api/ecritures/multiple',
        // Écritures déjà chargées par le contrôleur -> filtrage journal 100% côté client, aucun aller-retour serveur
        ecritures: @json($ecritures->map(function ($e) {
            return [
                'id' => $e->id,
                'date' => $e->date,
                'n_saisie' => $e->n_saisie,
                'statut' => $e->statut,
                'code_journal_id' => $e->code_journal_id,
                'code_journal' => $e->codeJournal->code_journal ?? '',
                'description_operation' => $e->description_operation,
                'reference_piece' => $e->reference_piece,
                'compte_general' => $e->planComptable->numero_de_compte ?? '',
                'compte_tiers' => $e->planTiers->numero_de_tiers ?? '',
                'analytique' => (bool) $e->plan_analytique,
                'debit' => $e->debit,
                'credit' => $e->credit,
                'poste_tresorerie' => $e->posteTresorerie->name ?? '',
                'piece' => (bool) $e->piece_justificatif,
            ];
        })),
    };
</script>
<script src="{{ asset('js/saisie-grille.js') }}"></script>
