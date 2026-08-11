<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free" data-bs-theme="light">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @include('components.head')
    <style>
        /* ===================== PAGE COMBINÉE : NOUVELLE SAISIE + LISTE DES ÉCRITURES ===================== */
        .fc-header {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .fc-field {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .fc-field label {
            font-size: 10.5px;
            color: rgba(255, 255, 255, .75);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .fc-field select,
        .fc-field input {
            height: 32px;
            font-size: 12.5px;
            border-radius: 8px;
            border: none;
            padding: 0 8px;
        }

        /* --- Modales de création (compte / tiers / poste) : style existant conservé --- */
        .premium-modal-content-tiers,
        .premium-modal-content {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid rgba(255, 255, 255, 1) !important;
            border-radius: 20px !important;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            padding: 1.5rem !important;
        }

        .text-blue-gradient-premium {
            background: linear-gradient(to right, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .input-field-premium {
            transition: all 0.2s ease;
            border: 2px solid #f1f5f9 !important;
            background-color: #f8fafc !important;
            border-radius: 16px !important;
            padding: 0.75rem 1rem !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            width: 100%;
        }

        .input-field-premium:focus {
            border-color: #1e40af !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
            outline: none !important;
        }

        .input-label-premium {
            font-size: 0.7rem !important;
            font-weight: 800 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-bottom: 0.5rem !important;
            display: block;
        }

        .btn-save-premium {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 16px;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.3);
        }

        .btn-cancel-premium {
            background: #f1f5f9;
            color: #64748b;
            border: none;
            border-radius: 16px;
            padding: 0.75rem 1.5rem;
            font-weight: 700;
        }

        /* Carte "Nouvelle Écriture" modernisée */
        .card-header-saisie {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.25rem;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-group label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .custom-select,
        .custom-input {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 13.5px;
            color: #1e293b;
            outline: none;
            height: 38px;
            transition: all 0.2s ease;
        }

        .custom-select:focus,
        .custom-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .badge-exercice {
            background-color: #e2e8f0;
            color: #334155;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            height: 38px;
            display: flex;
            align-items: center;
        }

        .select-with-btn {
            display: flex;
            gap: 6px;
        }

        .btn-add {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 12px;
            color: #4f46e5;
            font-weight: bold;
            cursor: pointer;
            height: 38px;
            transition: all 0.2s ease;
        }

        .btn-add:hover {
            background-color: #4f46e5;
            color: #ffffff;
        }

        /* Correctif de débordement et scroll des tableaux */
        .table-responsive-clean,
        .fc-table-responsive {
            width: 100% !important;
            display: block !important;
            overflow-x: scroll !important;
            overflow-y: auto !important;
            max-height: 520px !important;
            border-radius: 12px;
        }

        .table-responsive-clean table,
        .fc-table-responsive table {
            min-width: 1450px !important;
            width: 100% !important;
            white-space: nowrap !important;
        }

        .table-responsive-clean th,
        .table-responsive-clean td,
        .fc-table-responsive th,
        .fc-table-responsive td {
            white-space: nowrap !important;
            vertical-align: middle !important;
        }

        .fc-table-responsive::-webkit-scrollbar {
            width: 10px !important;
            height: 12px !important;
        }

        .fc-table-responsive::-webkit-scrollbar-thumb {
            background-color: #2563eb !important;
            border-radius: 6px !important;
            border: 2px solid #eff6ff !important;
        }

        .fc-table-responsive::-webkit-scrollbar-track {
            background: #e2e8f0 !important;
            border-radius: 6px !important;
        }

        /* Contrainte de largeur des listes déroulantes de la grille (Image 3) */
        .cpte-general,
        .cpte-tiers,
        .poste-treso {
            max-width: 260px !important;
            width: 260px !important;
        }

        .select2-container {
            width: 260px !important;
            max-width: 260px !important;
        }

        #panelSaisie .select2-container .select2-selection--single {
            height: 30px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
        }
        #panelSaisie .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            font-size: 12px !important;
            color: #1e293b !important;
            padding-left: 8px !important;
        }
        #panelSaisie .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 28px !important;
        }

        #panelSaisie .form-control-sm,
        #panelSaisie .form-select-sm,
        #panelSaisie .btn-sm {
            height: 30px !important;
            padding-top: 2px !important;
            padding-bottom: 2px !important;
            font-size: 12px !important;
        }
        #panelSaisie table td,
        #panelSaisie table th {
            padding: 4px 6px !important;
        }

        /* Enlever les flèches directionnelles sur les inputs Débit/Crédit */
        input.debit-input::-webkit-outer-spin-button,
        input.debit-input::-webkit-inner-spin-button,
        input.credit-input::-webkit-outer-spin-button,
        input.credit-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input.debit-input[type=number],
        input.credit-input[type=number] {
            -moz-appearance: textfield;
        }

        /* Style dynamique pour le bouton Valider lorsque l'écriture est équilibrée */
        .btn-balanced-active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            border: none !important;
            color: white !important;
            transform: scale(1.12) !important;
            font-size: 14px !important;
            padding: 8px 20px !important;
            font-weight: 800 !important;
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4) !important;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            animation: pulse-button 2s infinite;
        }

        @keyframes pulse-button {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'NOUVELLE <span class="text-gradient">ÉCRITURE</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        {{-- ===================== CARTE "NOUVELLE ÉCRITURE" MODERNISÉE ===================== --}}
                        <div class="card-header-saisie mb-3" id="headerCard">
                            <div class="d-flex flex-wrap align-items-end gap-3 w-100" id="headerCardContent">

                                <div class="field-group">
                                    <label>EXERCICE</label>
                                    <div class="badge-exercice">
                                        <i class="bx bx-lock-alt text-primary me-1"></i>
                                        EXERCICE {{ $exerciceActif->intitule ?? ($exerciceActif->id ?? '—') }}
                                    </div>
                                    <input type="hidden" id="id_exercice" value="{{ $exerciceActif->id ?? '' }}">
                                </div>

                                <div class="field-group">
                                    <label>JOURNAL <span class="text-danger fw-bold">*</span></label>
                                    <div class="select-with-btn">
                                        <select id="code_journal_id" class="custom-select" style="min-width:180px">
                                            <option value="">— Tous les journaux —</option>
                                            @foreach ($codeJournaux as $j)
                                                <option value="{{ $j->id }}" data-code_journal_j="{{ $j->code_journal }}"
                                                    data-intitule_j="{{ $j->intitule }}" data-type_j="{{ $j->type }}"
                                                    data-contrepartie="{{ $j->compte_de_contrepartie }}" {{ (isset($data['id_journal_code']) && $data['id_journal_code'] == $j->id) ? 'selected' : '' }}>
                                                    {{ $j->code_journal }} - {{ $j->intitule }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-add" id="btnOpenCreateJournalModal" title="Créer un journal" data-bs-toggle="modal" data-bs-target="#modalCreateJournalInline">+</button>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label>MODÈLE DE SAISIE</label>
                                    <div class="select-with-btn">
                                        <select id="modele_saisie" class="custom-select" style="min-width:160px">
                                            <option value="">— Aucun —</option>
                                            @foreach ($modelesSaisie ?? [] as $m)
                                                <option value="{{ $m->id }}">{{ $m->nom }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn-add" title="Créer un modèle"
                                            onclick="saisieGrille.ouvrirModalCreerModele()">+</button>
                                    </div>
                                </div>

                                <input type="hidden" id="annee_exercice"
                                    value="{{ $exerciceActif ? \Carbon\Carbon::parse($exerciceActif->date_debut)->format('Y') : date('Y') }}">

                                <div class="field-group">
                                    <label>MOIS <span class="text-danger fw-bold">*</span></label>
                                    <select id="mois_ecriture" class="custom-select" style="min-width:145px">
                                        <option value="">— Tous les mois —</option>
                                        @php $moisNoms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']; @endphp
                                        @foreach ($moisNoms as $i => $nom)
                                            <option value="{{ $i + 1 }}" {{ now()->month == $i + 1 ? 'selected' : '' }}>
                                                {{ $nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label>JOUR <span class="text-danger fw-bold">*</span></label>
                                    <select id="jour_ecriture" class="custom-select" style="min-width:145px">
                                        <option value="">— Tous les jours —</option>
                                        @for ($j = 1; $j <= 31; $j++)
                                            <option value="{{ $j }}">{{ $j }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label>N° DE SAISIE</label>
                                    <input type="text" id="n_saisie_user" class="custom-input"
                                        value="{{ $nextSaisieNumber }}" readonly style="min-width:180px">
                                </div>

                                <div class="d-flex gap-2 ms-auto align-items-end">
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-3"
                                        style="height:38px;border-radius:8px" id="btnScannerFacture"
                                        onclick="saisieGrille.scannerFacture()">
                                        <i class="bx bx-scan me-1"></i> Scanner facture
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm fw-bold px-3"
                                        style="height:38px;border-radius:8px;background-color:#4f46e5;border-color:#4f46e5"
                                        id="btnNouvelleSaisie" onclick="saisieGrille.toggle()">
                                        <i class="bx bx-plus me-1"></i> Nouvelle saisie
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border"
                                        style="height:38px;border-radius:8px" id="btnToggleHeaderCard"
                                        title="Ouvrir / Masquer l'en-tête" onclick="saisieGrille.toggleHeaderCard()">
                                        <i class="bx bx-chevron-up" id="iconToggleHeader"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ===================== BARRE DE SWITCH ENTRE TABLEAUX (GRILLE SAGE vs LISTE CONSULTATION)
                        ===================== --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                            <div class="btn-group shadow-sm" role="group" style="border-radius:12px;overflow:hidden">
                                <button type="button" class="btn btn-primary btn-sm px-3 fw-bold active"
                                    id="btnTabModeSage" onclick="saisieGrille.switchViewMode('sage')">
                                    <i class="bx bx-table me-1"></i> Écritures du journal
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold bg-white"
                                    id="btnTabModeListe" onclick="saisieGrille.switchViewMode('consultation')">
                                    <i class="bx bx-list-ul me-1"></i> Liste des écritures (Consultation)
                                </button>
                            </div>
                        </div>

                        {{-- ===================== BLOC SAISIE (masqué tant qu'on ne clique pas "Nouvelle saisie")
                        ===================== --}}
                        <div class="fc-card p-2 mb-2" id="panelSaisie"
                            style="display:none;background:#fff;border-radius:12px;box-shadow:0 3px 10px -2px rgba(0,0,0,.07)">

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-bold text-primary" id="saisieTitre" style="font-size:12px"><i
                                        class="bx bx-edit me-1"></i>Libellé de l'opération</div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size:11px"
                                        onclick="saisieGrille.enregistrerCommeModele()">
                                        <i class="bx bx-bookmark me-1"></i> Modèle
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnFermerSaisie" style="font-size:11px"
                                        onclick="saisieGrille.fermer()">
                                        <i class="bx bx-x me-1"></i> Fermer
                                    </button>
                                </div>
                            </div>

                            {{-- Carte d'avertissement : visible uniquement si le groupe en cours est déséquilibré --}}
                            <div id="carteDesequilibre" class="d-flex align-items-start gap-2 p-2 mb-2"
                                style="display:none !important;background:#fff7ed;border:1px solid #fdba74;border-radius:10px;color:#c2410c">
                                <i class="bx bx-error-circle" style="font-size:17px"></i>
                                <div>
                                    <div class="fw-semibold small" id="carteDesequilibreTitre">Écriture non équilibrée</div>
                                    <div class="small" id="carteDesequilibreTexte"></div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mb-1">
                                <input id="description_operation" class="form-control form-control-sm"
                                    placeholder="Libellé de l'opération *">
                                <input id="reference_piece" class="form-control form-control-sm" style="width:140px"
                                    placeholder="Réf. pièce">
                                <label class="btn btn-outline-secondary btn-sm mb-0 d-flex align-items-center gap-1"
                                    style="width:150px;cursor:pointer;font-size:11px">
                                    <i class="bx bx-paperclip"></i>
                                    <span id="pieceLabel">Pièce jointe</span>
                                    <input type="file" id="piece_justificatif" class="d-none"
                                        onchange="saisieGrille.onFichierChoisi(this)">
                                </label>
                            </div>

                            <div class="table-responsive-clean mb-1">
                                <table class="table table-sm align-middle mb-0" style="font-size:12px">
                                    <thead>
                                        <tr class="small text-muted text-uppercase">
                                            <th style="min-width:220px">Compte général <span
                                                    class="text-danger fw-bold">*</span></th>
                                            <th style="min-width:190px">Compte tiers</th>
                                            <th style="width:105px" class="text-end">Débit</th>
                                            <th style="width:105px" class="text-end">Crédit</th>
                                            <th style="min-width:190px">Poste trésorerie</th>
                                            <th style="width:44px" class="text-center">TVA</th>
                                            <th style="width:44px" class="text-center">Analyt.</th>
                                            <th style="width:80px" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <!-- Ligne de saisie unique (Style SAGE) -->
                                    <tbody id="inputRowContainer">
                                        <tr id="inputRow" data-ventilations="[]">
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <select id="input_compte_general" class="form-select form-select-sm cpte-general">
                                                         <option value="">— Choisir —</option>
                                                         @foreach ($plansComptables as $p)
                                                             <option value="{{ $p->id }}" data-numero="{{ $p->numero_de_compte }}">
                                                                 {{ $p->numero_de_compte }} - {{ $p->intitule }}
                                                             </option>
                                                         @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-sm btn-plus" title="Créer un compte" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;border:none;width:28px">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <select id="input_compte_tiers" class="form-select form-select-sm cpte-tiers"></select>
                                                    <button type="button" class="btn btn-sm btn-plus-tiers" title="Créer un tiers" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;border:none;width:28px">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="input_debit" class="form-control form-control-sm text-end debit-input" placeholder="0.00">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" id="input_credit" class="form-control form-control-sm text-end credit-input" placeholder="0.00">
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <select id="input_poste_treso" class="form-select form-select-sm poste-treso" disabled>
                                                         <option value="">— Sélectionner poste —</option>
                                                         @foreach ($comptesTresorerie as $c)
                                                             <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                         @endforeach
                                                    </select>
                                                    <button type="button" id="input_btn_plus_poste" class="btn btn-sm btn-plus-poste" title="Créer un poste" style="background:linear-gradient(135deg,#2563eb,#1e3a8a);color:#fff;border:none;width:28px" disabled>+</button>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" id="input_btn_tva" class="btn btn-sm btn-tva" title="Assistant TVA" style="background:#eff6ff;color:#2563eb;border:none;width:28px">
                                                    <i class="bx bx-receipt"></i>
                                                </button>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" id="input_btn_analytique" class="btn btn-sm btn-analytique" title="Ventiler sur les sections analytiques" style="background:#eff6ff;color:#2563eb;border:none;width:28px">
                                                    <i class="bx bx-pie-chart-alt"></i>
                                                </button>
                                            </td>
                                            <td class="text-center"></td>
                                        </tr>
                                    </tbody>
                                    <!-- Tableau des lignes déjà ajoutées au groupe -->
                                    <tbody id="addedLinesBody" style="border-top: 2px solid #e2e8f0;"></tbody>
                                </table>
                            </div>

                            <div id="contrepartieHint" class="small text-primary d-flex align-items-center gap-1 mt-1"
                                style="display:none !important">
                                <i class="bx bx-bulb"></i> <span></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2 p-2"
                                style="background:#f8fafc;border-radius:10px">
                                <span class="small text-muted">Débit <strong id="totalDebit">0</strong> · Crédit <strong
                                        id="totalCredit">0</strong></span>
                                <span id="balanceBadge" class="badge bg-danger rounded-pill px-3 py-2">Non
                                    équilibré</span>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-2">
                                <button type="button" class="btn btn-outline-warning btn-sm fw-bold"
                                    id="btnBrouillonGrille" onclick="saisieGrille.enregistrerBrouillon()">
                                    <i class="bx bx-file me-1"></i> Enregistrer comme brouillon
                                </button>
                                <button type="button" class="btn btn-primary btn-sm fw-bold px-4"
                                    id="input_btn_ajouter"
                                    style="background:linear-gradient(135deg,#4f46e5,#3730a3);border:none"
                                    onclick="saisieGrille.ajouterLigneEnCours()">
                                    <i class="bx bx-plus me-1"></i> Ajouter la ligne
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="btnValiderGrille" disabled
                                    style="background:linear-gradient(135deg,#10b981,#059669);border:none"
                                    onclick="saisieGrille.enregistrer()">Valider &amp; enregistrer</button>
                            </div>
                        </div>

                        {{-- ===================== TABLEAU 1 : ÉCRITURES DU JOURNAL (CONTENEUR SCROLLABLE)
                        ===================== --}}
                        <div id="viewSageContainer" class="fc-card p-3 mb-3"
                            style="background:#fff;border-radius:20px;box-shadow:0 10px 25px -5px rgba(0,0,0,.05)">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="fw-bold" style="font-size:14px"><i
                                            class="bx bx-table text-primary me-1"></i>Écritures du journal
                                    </div>
                                    <div class="small text-muted">Conteneur dynamique : le dernier élément saisi se
                                        met en bas, défilement vers le haut pour voir l'historique sans rechargement.
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm" id="chipDesequilibre"
                                        onclick="saisieGrille.toggleFiltreDesequilibre()"
                                        style="border:1.5px solid #d1d5db;border-radius:20px;background:#fff">
                                        <span
                                            style="width:7px;height:7px;border-radius:50%;background:#f97316;display:inline-block;margin-right:5px"></span>
                                        Déséquilibrées uniquement
                                    </button>
                                    <span class="small text-muted" id="compteurLignes"></span>
                                </div>
                            </div>

                            {{-- Conteneur Sage scrollable --}}
                            <div id="sageScrollContainer" class="fc-table-responsive"
                                style="max-height:480px;overflow:auto;scroll-behavior:smooth;">
                                <table class="table table-sm align-middle table-hover">
                                    <thead class="sticky-top bg-white border-bottom shadow-xs">
                                        <tr class="small text-muted text-uppercase">
                                            <th style="width:25px"></th>
                                            <th style="width:95px">Date</th>
                                            <th style="width:145px">N° saisie</th>
                                            <th style="width:95px">Statut</th>
                                            <th style="width:85px">Journal</th>
                                            <th style="width:115px">Poste trés.</th>
                                            <th style="width:130px">Réf.</th>
                                            <th style="width:220px">Description</th>
                                            <th style="width:150px">Cpte gén.</th>
                                            <th style="width:150px">Cpte tiers</th>
                                            <th style="width:65px" class="text-center">An.</th>
                                            <th style="width:110px" class="text-end">Débit</th>
                                            <th style="width:110px" class="text-end">Crédit</th>
                                            <th style="width:75px" class="text-center">Pièce</th>
                                            <th style="width:80px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listeEcrituresBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ===================== TABLEAU 2 : LISTE DES ÉCRITURES (MODE CONSULTATION GÉNÉRALE)
                        ===================== --}}
                        <div id="viewConsultationContainer" class="fc-card p-3 mb-3"
                            style="display:none;background:#fff;border-radius:20px;box-shadow:0 10px 25px -5px rgba(0,0,0,.05)">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="fw-bold" style="font-size:14px"><i
                                            class="bx bx-list-ul text-primary me-1"></i>Consultation et suivi des
                                        écritures comptables</div>
                                    <div class="small text-muted">Vue d'ensemble complète avec filtres instantanés sur
                                        le code journal et le mois.</div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1">
                                        <label class="small text-muted mb-0 fw-semibold">Journal:</label>
                                        <select id="filtre_consultation_journal" class="form-select form-select-sm"
                                            style="width:160px" onchange="saisieGrille.appliquerFiltresConsultation()">
                                            <option value="">Tous les journaux</option>
                                            @foreach ($codeJournaux as $j)
                                                <option value="{{ $j->code_journal }}">{{ $j->code_journal }} -
                                                    {{ $j->intitule }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <label class="small text-muted mb-0 fw-semibold">Mois:</label>
                                        <select id="filtre_consultation_mois" class="form-select form-select-sm"
                                            style="width:130px" onchange="saisieGrille.appliquerFiltresConsultation()">
                                            <option value="">Tous les mois</option>
                                            @foreach ($moisNoms as $i => $nom)
                                                <option value="{{ $i + 1 }}">{{ $nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="small text-muted" id="compteurConsultationLignes"></span>
                                </div>
                            </div>

                            <div class="fc-table-responsive">
                                <table class="table table-sm align-middle table-hover">
                                    <thead class="bg-light">
                                        <tr class="small text-muted text-uppercase">
                                            <th style="width:25px"></th>
                                            <th style="width:95px">Date</th>
                                            <th style="width:145px">N° saisie</th>
                                            <th style="width:95px">Statut</th>
                                            <th style="width:85px">Journal</th>
                                            <th style="width:115px">Poste trés.</th>
                                            <th style="width:130px">Réf.</th>
                                            <th style="width:220px">Description</th>
                                            <th style="width:150px">Cpte gén.</th>
                                            <th style="width:150px">Cpte tiers</th>
                                            <th style="width:65px" class="text-center">An.</th>
                                            <th style="width:110px" class="text-end">Débit</th>
                                            <th style="width:110px" class="text-end">Crédit</th>
                                            <th style="width:75px" class="text-center">Pièce</th>
                                            <th style="width:80px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listeConsultationBody"></tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ===================== MODALES DE CRÉATION RAPIDE (compte / tiers / poste)
                        ===================== --}}
                        {{-- Réutilisation exacte des modales et endpoints existants de l'application --}}

                        <!-- Modal Nouveau Poste de Trésorerie -->
                        <div class="modal fade" id="modalCreatePoste" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                                <form id="createPosteForm" class="w-full">
                                    <div class="modal-content premium-modal-content">
                                        <div class="text-center mb-6 position-relative">
                                            <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                                Nouveau <span class="text-blue-gradient-premium">Poste</span>
                                            </h1>
                                            <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                                        </div>
                                        <div class="space-y-4 px-4 pb-4">
                                            <div>
                                                <label class="input-label-premium">Nom du Poste *</label>
                                                <input type="text" id="poste_name" name="name"
                                                    class="input-field-premium" required
                                                    placeholder="Ex: Ventes de marchandises">
                                            </div>
                                            <div>
                                                <label class="input-label-premium">Catégorie *</label>
                                                <select id="poste_category_id" name="category_id"
                                                    class="input-field-premium" required>
                                                    <option value="" disabled selected>-- Sélectionner --</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between gap-3 pt-4 px-4 pb-4">
                                            <button type="button" class="btn-cancel-premium flex-fill"
                                                data-bs-dismiss="modal">Annuler</button>
                                            <button type="button" id="btnSavePoste" onclick="createPosteSimple(event)"
                                                class="btn-save-premium flex-fill">Enregistrer</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Modal Nouveau Tiers -->
                        <div class="modal fade" id="createTiersModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                                <div id="createTiersForm" class="w-full">
                                    <div class="modal-content premium-modal-content-tiers">
                                        <div class="text-center mb-6 position-relative">
                                            <button type="button" class="btn-close position-absolute end-0 top-0"
                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900"
                                                style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                                Nouveau <span class="text-blue-gradient-premium">Tiers</span>
                                            </h1>
                                            <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                                        </div>
                                        <div class="space-y-4">
                                            <div class="mb-3">
                                                <label class="input-label-premium">Catégorie</label>
                                                <select id="type_tiers" name="type_de_tiers" class="input-field-premium"
                                                    required>
                                                    <option value="" disabled selected>Sélectionner une catégorie
                                                    </option>
                                                    <option value="Fournisseur" data-prefix="40">Fournisseur</option>
                                                    <option value="Client" data-prefix="41">Client</option>
                                                    <option value="Personnel" data-prefix="42">Personnel</option>
                                                    <option value="CNPS" data-prefix="43">Organisme sociaux / CNPS
                                                    </option>
                                                    <option value="Impots" data-prefix="44">Impôt</option>
                                                    <option value="Organisme international" data-prefix="45">Organisme
                                                        international</option>
                                                    <option value="Associé" data-prefix="46">Associé / Actionnaire
                                                    </option>
                                                    <option value="Divers Tiers" data-prefix="47">Divers Tiers</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="input-label-premium">Numéro de tiers</label>
                                                <input type="text" id="numero_tiers" name="numero_de_tiers"
                                                    class="input-field-premium opacity-75"
                                                    placeholder="Généré automatiquement" required readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="input-label-premium">Compte de Rattachement</label>
                                                <div class="d-flex gap-2">
                                                    <select id="compte_general_tiers" name="compte_general"
                                                        class="input-field-premium form-select" style="flex: 1;">
                                                        <option value="" disabled selected>-- Sélectionnez un compte --
                                                        </option>
                                                        @foreach ($plansComptables as $p)
                                                            <option value="{{ $p->id }}"
                                                                data-numero="{{ $p->numero_de_compte }}">
                                                                {{ $p->numero_de_compte }} - {{ $p->intitule }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="input-label-premium">Nom / Raison Sociale</label>
                                                <input type="text" id="intitule_tiers" name="intitule"
                                                    class="input-field-premium" placeholder="Entrez le nom de l'entité"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between gap-3 mt-4">
                                            <button type="button" class="btn-cancel-premium flex-fill"
                                                data-bs-dismiss="modal">Annuler</button>
                                            <button type="button" id="btnCreateTiers"
                                                onclick="window.createTiersSimple(event)"
                                                class="btn-save-premium flex-fill">Enregistrer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Nouveau Compte Général -->
                        <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                                <div id="planComptableForm" class="w-full">
                                    <div class="modal-content premium-modal-content"
                                        style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                                        <div class="text-center mb-4 position-relative"
                                            style="padding: 1.5rem 1.5rem 0;">
                                            <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                                                data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900"
                                                style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                                                Nouveau <span class="text-blue-gradient-premium">Compte</span>
                                            </h1>
                                            <div class="h-1 w-8 bg-blue-700 mx-auto rounded-full"
                                                style="height: 4px; width: 32px;"></div>
                                        </div>
                                        <div class="modal-body" style="padding: 0 2rem 2rem;">
                                            <div class="space-y-4">
                                                <div class="mb-3">
                                                    <label for="numero_de_compte" class="input-label-premium">Numéro de
                                                        compte</label>
                                                    <input type="text" class="input-field-premium" id="numero_de_compte"
                                                        name="numero_de_compte" maxlength="8" placeholder="Ex: 41110000"
                                                        required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="intitule" class="input-label-premium">Intitulé du
                                                        compte</label>
                                                    <input type="text" class="input-field-premium" id="intitule"
                                                        name="intitule" placeholder="Entrez l'intitulé du compte"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between gap-3 mt-2">
                                                <button type="button" class="btn btn-light flex-fill"
                                                    data-bs-dismiss="modal"
                                                    style="padding: 0.75rem; border-radius: 12px; font-weight: 700; color: #64748b;">Annuler</button>
                                                <button type="button" id="btnCreateCompte"
                                                    onclick="window.createCompteSimple(event)"
                                                    class="btn btn-primary-premium flex-fill"
                                                    style="padding: 0.75rem; border-radius: 12px; font-weight: 700; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border: none; color: white;">Enregistrer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Nouveau Modèle de Saisie -->
                        <div class="modal fade" id="modalCreateModeleSaisie" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
                                <div class="modal-content premium-modal-content">
                                    <div class="text-center mb-4 position-relative">
                                        <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                                            data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900"
                                            style="font-size: 1.3rem; font-weight: 800; margin-bottom: 0.5rem;">
                                            Nouveau <span class="text-blue-gradient-premium">Modèle de Saisie</span>
                                        </h1>
                                        <div class="h-1 w-8 bg-blue-700 mx-auto rounded-full"
                                            style="height: 4px; width: 32px;"></div>
                                    </div>
                                    <div class="modal-body px-4 pb-4">
                                        <div class="mb-3">
                                            <label for="nom_modele_saisie_input" class="input-label-premium">Nom du
                                                modèle *</label>
                                            <input type="text" class="input-field-premium" id="nom_modele_saisie_input"
                                                placeholder="Ex: ACHAT FOURNISSEUR TVA 18%">
                                        </div>
                                        <div class="small text-muted mb-3">
                                            <i class="bx bx-info-circle me-1"></i> Ce modèle enregistrera les lignes
                                            actuellement saisies dans la grille ci-dessous.
                                        </div>
                                        <div class="d-flex justify-content-between gap-3">
                                            <button type="button" class="btn-cancel-premium flex-fill"
                                                data-bs-dismiss="modal">Annuler</button>
                                            <button type="button" id="btnSaveModeleInline"
                                                onclick="saisieGrille.enregistrerNouveauModeleInline()"
                                                class="btn-save-premium flex-fill">Enregistrer le modèle</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Nouveau Journal (Inline depuis la saisie d'écriture) -->
                        <div class="modal fade" id="modalCreateJournalInline" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                <div class="modal-content premium-modal-content">
                                    <div class="text-center mb-4 position-relative">
                                        <button type="button" class="btn-close position-absolute end-0 top-0 m-3"
                                            data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900"
                                            style="font-size: 1.3rem; font-weight: 800; margin-bottom: 0.5rem;">
                                            Nouveau <span class="text-blue-gradient-premium">Journal</span>
                                        </h1>
                                        <div class="h-1 w-8 bg-blue-700 mx-auto rounded-full"
                                            style="height: 4px; width: 32px;"></div>
                                    </div>
                                    <div class="modal-body px-4 pb-4">
                                        <div class="row g-3">
                                            <div class="col-md-6 text-start">
                                                <label class="input-label-premium">Code Journal *</label>
                                                <input type="text" id="inline_journal_code" name="code_journal"
                                                    class="input-field-premium"
                                                    maxlength="{{ auth()->user()->company->journal_code_digits ?? 4 }}"
                                                    placeholder="ex: VT"
                                                    readonly
                                                    style="background-color:#f8fafc;cursor:not-allowed;text-transform:uppercase;">
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="input-label-premium">Type *</label>
                                                <select id="inline_journal_type" class="input-field-premium" required>
                                                    <option value="" disabled selected>-- Choisir un type --</option>
                                                    <option value="Achats">Achats</option>
                                                    <option value="Ventes">Ventes</option>
                                                    <option value="Tresorerie">Trésorerie</option>
                                                    <option value="Opérations Diverses">Opérations Diverses</option>
                                                    <option value="Standard">Standard</option>
                                                    <option value="REPORT A NOUVEAU">REPORT A NOUVEAU</option>
                                                </select>
                                            </div>
                                            <div class="col-12 text-start">
                                                <label class="input-label-premium">Intitulé *</label>
                                                <input type="text" id="inline_journal_intitule" class="input-field-premium"
                                                    placeholder="ex: Journal des Ventes" required>
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="input-label-premium">Traitement analytique</label>
                                                <select id="inline_journal_analytique" class="input-field-premium">
                                                    <option value="non">Non</option>
                                                    <option value="oui">Oui</option>
                                                </select>
                                            </div>
                                            <!-- Options Trésorerie (conditionnelles) -->
                                            <div class="col-12 text-start d-none" id="inline_tresorerie_options">
                                                <label class="input-label-premium">Type de Trésorerie</label>
                                                <div class="d-flex gap-4 p-3 bg-slate-50 rounded-2xl border border-slate-100 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="inline_poste_tresorerie"
                                                            id="inline_treso_caisse" value="Caisse" onchange="window.handleTresoChangeInline()">
                                                        <label class="form-check-label fw-bold text-slate-700" for="inline_treso_caisse">Caisse</label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="inline_poste_tresorerie"
                                                            id="inline_treso_banque" value="Banque" onchange="window.handleTresoChangeInline()">
                                                        <label class="form-check-label fw-bold text-slate-700" for="inline_treso_banque">Banque</label>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="input-label-premium">Autre libellé</label>
                                                    <input type="text" id="inline_treso_autre" class="input-field-premium"
                                                        placeholder="Saisir un autre libellé..." oninput="window.handleOtherInputInline()">
                                                </div>
                                                <div class="mb-2">
                                                    <label class="input-label-premium">Compte de contrepartie</label>
                                                    <select id="inline_compte_contrepartie" class="input-field-premium">
                                                        <option value="">-- Sélectionner --</option>
                                                        @foreach($plansComptables->filter(fn($p) => str_starts_with($p->numero_de_compte, '5')) as $compte)
                                                            <option value="{{ $compte->numero_de_compte }}">
                                                                {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="input-label-premium">État de rapprochement bancaire</label>
                                                    <select id="inline_rapprochement" class="input-field-premium">
                                                        <option value="">-- Sélectionner --</option>
                                                        <option value="Manuel">Manuel</option>
                                                        <option value="Automatique">Automatique</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between gap-3 mt-3">
                                            <button type="button" class="btn-cancel-premium flex-fill"
                                                data-bs-dismiss="modal">Annuler</button>
                                            <button type="button" id="btnSaveJournalInline"
                                                onclick="window.createJournalInline()"
                                                class="btn-save-premium flex-fill">Enregistrer le journal</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Nouveau Poste de Trésorerie (selon Images 1 et 2) -->
                        <div class="modal fade" id="modalCreatePoste" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
                                <div class="modal-content"
                                    style="border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.15);">
                                    <div class="modal-body p-4 text-center">
                                        <h3 class="fw-bold mb-4" style="color: #1e293b; font-size: 1.5rem;">Nouveau
                                            poste de trésorerie</h3>

                                        <div class="text-start mb-3">
                                            <label class="form-label fw-medium text-secondary small">Nom du poste <span
                                                    class="text-danger fw-bold">*</span></label>
                                            <input type="text" id="nom_poste_tresorerie_input"
                                                class="form-control form-control-lg"
                                                style="border-radius: 12px; font-size: 14px;"
                                                placeholder="Ex: Caisse Menue Dépense">
                                        </div>

                                        <div class="text-start mb-4">
                                            <label class="form-label fw-medium text-secondary small">Catégorie <span
                                                    class="text-danger fw-bold">*</span></label>
                                            <select id="categorie_poste_tresorerie_select"
                                                class="form-select form-select-lg"
                                                style="border-radius: 12px; font-size: 14px;">
                                                <option value="" disabled selected>Sélectionner une catégorie...
                                                </option>
                                                <option value="Flux de trésorerie des activités opérationnelles">I. Flux
                                                    de trésorerie des activités opérationnelles</option>
                                                <option value="Flux de trésorerie des activités d'investissement">II.
                                                    Flux de trésorerie des activités d'investissement</option>
                                                <option value="Flux de trésorerie des activités de financement">III.
                                                    Flux de trésorerie des activités de financement</option>
                                            </select>
                                        </div>

                                        <div class="d-flex justify-content-center gap-3">
                                            <button type="button"
                                                onclick="saisieGrille.creerEtAssignerPosteTresorerie()"
                                                class="btn btn-primary px-4 py-2 fw-bold"
                                                style="background: #6366f1; border: none; border-radius: 10px;">Créer et
                                                Assigner</button>
                                            <button type="button" class="btn btn-secondary px-4 py-2 fw-bold"
                                                data-bs-dismiss="modal" style="border-radius: 10px;">Annuler</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Include Modal Ventilation Analytique --}}
                        @include('accounting.partials.modal_ventilation_analytique')

                        @include('components.footer')
                        <div class="content-backdrop fade"></div>
                    </div>
                </div>
            </div>

            @php
                $fcModelesSaisie = ($modelesSaisie ?? collect())->map(function ($m) {
                    return ['id' => $m->id, 'nom' => $m->nom, 'lignes' => $m->lignes];
                })->values();

                $fcEcritures = $ecritures->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'date' => $e->date,
                        'n_saisie' => $e->n_saisie,
                        'n_saisie_user' => $e->n_saisie_user ?? null,
                        'n_saisie_original' => $e->n_saisie_original ?? null,
                        'statut' => $e->statut,
                        'code_journal_id' => $e->code_journal_id,
                        'code_journal' => $e->codeJournal->code_journal ?? '',
                        'code_journal_original' => $e->codeJournal->numero_original ?? null,
                        'description_operation' => $e->description_operation,
                        'reference_piece' => $e->reference_piece,
                        'compte_general' => $e->planComptable->numero_de_compte ?? '',
                        'compte_general_intitule' => $e->planComptable->intitule ?? '',
                        'compte_general_original' => $e->planComptable->numero_original ?? null,
                        'compte_tiers' => $e->planTiers->numero_de_tiers ?? '',
                        'compte_tiers_intitule' => $e->planTiers->intitule ?? '',
                        'compte_tiers_original' => $e->planTiers->numero_original ?? null,
                        'analytique' => (bool) $e->plan_analytique,
                        'debit' => $e->debit,
                        'credit' => $e->credit,
                        'poste_tresorerie' => $e->posteTresorerie->name ?? '',
                        'poste_tresorerie_id' => $e->poste_tresorerie_id ?? null,
                        'piece' => (bool) $e->piece_justificatif,
                        'piece_url' => $e->piece_justificatif ? (str_starts_with($e->piece_justificatif, 'http') ? $e->piece_justificatif : asset(file_exists(public_path('justificatifs/' . $e->piece_justificatif)) ? 'justificatifs/' . $e->piece_justificatif : (str_contains($e->piece_justificatif, '/') ? $e->piece_justificatif : 'justificatifs/' . $e->piece_justificatif))) : null,
                    ];
                })->values();
              @endphp
            <script>
                window.SAISIE_DATA = {
                    plansComptables: @json($plansComptables),
                    plansTiers: @json($plansTiers),
                    comptesTresorerie: @json($comptesTresorerie),
                    idExercice: {{ $exerciceActif->id ?? 'null' }},
                    csrfToken: '{{ csrf_token() }}',
                    storeMultipleUrl: '{{ route('api.ecriture.storeMultiple') }}',
                    miseAJourMassiveUrl: '{{ route('accounting_entry_real_goupes.miseAJourMassive') }}',
                    ecritureModelesStoreUrl: '{{ route('ecriture_modeles.store') }}',
                    journauxSaisisFindUrl: '{{ route('journaux_saisis.find') }}',
                    ecritureScanUrl: '{{ route('ecriture.scan') }}',
                    // Modèles de saisie déjà enregistrés (support serveur), utilisés pour "Appeler un modèle"
                    modelesSaisie: @json($fcModelesSaisie),
                    // Écritures déjà chargées par le contrôleur -> filtrage journal 100% côté client, aucun aller-retour serveur
                    ecritures: @json($fcEcritures),
                };
            </script>
            <script src="{{ asset('js/saisie-grille.js') }}"></script>

            <script>
                // --- GESTION DES TIERS (réutilisation du contrat JSON existant plan_tiers.store) ---
                const createTiersModalEl = document.getElementById('createTiersModal');
                if (createTiersModalEl) {
                    const tiersModal = new bootstrap.Modal(createTiersModalEl);
                    const typeTiersSelect = document.getElementById('type_tiers');
                    const numeroTiersInput = document.getElementById('numero_tiers');
                    const compteGeneralTiers = document.getElementById('compte_general_tiers');
                    const intituleTiersInput = document.getElementById('intitule_tiers');
                    const btnCreateTiers = document.getElementById('btnCreateTiers');

                    createTiersModalEl.addEventListener('show.bs.modal', function () {
                        typeTiersSelect.value = '';
                        numeroTiersInput.value = '';
                        intituleTiersInput.value = '';
                    });

                    // Changement de type -> filtre les comptes rattachables + génère le numéro via getDernierNumero
                    typeTiersSelect.addEventListener('change', function () {
                        const type = this.value;
                        numeroTiersInput.value = '';
                        numeroTiersInput.placeholder = 'Calcul...';

                        const prefixes = {
                            'Fournisseur': '40', 'Client': '41', 'Personnel': '42', 'CNPS': '43',
                            'Impots': '44', 'Associé': '46', 'Organisme international': '45', 'Divers Tiers': '47'
                        };
                        const prefix = prefixes[type];
                        const allComptes = window.SAISIE_DATA.plansComptables;
                        let filtered = allComptes;
                        if (type === 'Divers Tiers') {
                            const allPrefixes = ['40', '41', '42', '43', '44', '45', '46'];
                            filtered = allComptes.filter(p => !allPrefixes.some(pf => p.numero_de_compte.startsWith(pf)));
                        } else if (prefix) {
                            filtered = allComptes.filter(p => p.numero_de_compte.startsWith(prefix));
                        }

                        compteGeneralTiers.innerHTML = '<option value="" selected disabled>Sélectionner un compte rattaché</option>' +
                            filtered.map(p => `<option value="${p.id}" data-numero="${p.numero_de_compte}">${p.numero_de_compte} - ${p.intitule}</option>`).join('');

                        if (type !== 'Divers Tiers' && prefix) {
                            fetch(`/plan_tiers/${prefix}`)
                                .then(r => r.json())
                                .then(data => {
                                    numeroTiersInput.value = data.numero || (prefix + Math.floor(Math.random() * 89999 + 10000));
                                })
                                .catch(() => {
                                    numeroTiersInput.value = prefix + Math.floor(Math.random() * 89999 + 10000);
                                });
                        } else {
                            numeroTiersInput.placeholder = 'Saisir manuellement';
                            numeroTiersInput.readOnly = false;
                        }
                    });

                    window.createTiersSimple = function (event) {
                        if (event) event.preventDefault();

                        const data = {
                            type_de_tiers: typeTiersSelect.value,
                            compte_general: compteGeneralTiers.value,
                            numero_de_tiers: numeroTiersInput.value,
                            intitule: intituleTiersInput.value.trim()
                        };

                        if (!data.type_de_tiers || !data.compte_general || !data.intitule) {
                            Swal.fire({ icon: 'warning', title: 'Champs manquants', text: 'Veuillez remplir toutes les informations obligatoires.' });
                            return;
                        }

                        const originalBtnHtml = btnCreateTiers.innerHTML;
                        btnCreateTiers.disabled = true;
                        btnCreateTiers.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';

                        fetch('{{ route("plan_tiers.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(data)
                        })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) {
                                    tiersModal.hide();
                                    // Injection directe dans la ligne de la grille ciblée par le bouton "+"
                                    window.fcInjecterElementCree('tiers', {
                                        id: result.id,
                                        numero_de_tiers: result.numero_de_tiers,
                                        intitule: result.intitule,
                                        compte_general: data.compte_general,
                                    });
                                    Swal.fire({ icon: 'success', title: 'Succès !', text: 'Le compte tiers a été créé et sélectionné.', timer: 2000, showConfirmButton: false });
                                } else {
                                    throw new Error(result.error || 'Erreur lors de la création');
                                }
                            })
                            .catch(err => {
                                Swal.fire({ icon: 'error', title: 'Oups...', text: 'Une erreur est survenue : ' + err.message });
                            })
                            .finally(() => {
                                btnCreateTiers.disabled = false;
                                btnCreateTiers.innerHTML = originalBtnHtml;
                            });
                    };
                }

                // --- GESTION DES POSTES TRÉSORERIE (réutilisation du contrat JSON existant postetresorerie.store_poste) ---
                const modalCreatePosteEl = document.getElementById('modalCreatePoste');
                if (modalCreatePosteEl) {
                    const posteModal = new bootstrap.Modal(modalCreatePosteEl);

                    window.createPosteSimple = function (event) {
                        if (event) event.preventDefault();

                        const btn = document.getElementById('btnSavePoste');
                        const name = document.getElementById('poste_name').value.trim();
                        const category_id = document.getElementById('poste_category_id').value;

                        if (!name || !category_id) {
                            Swal.fire({ icon: 'warning', title: 'Champs manquants', text: 'Veuillez remplir toutes les informations obligatoires.' });
                            return;
                        }

                        const originalBtnHtml = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';

                        fetch('{{ route("postetresorerie.store_poste") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ name, category_id })
                        })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) {
                                    posteModal.hide();
                                    document.getElementById('createPosteForm').reset();
                                    window.fcInjecterElementCree('poste', { id: result.id, name: result.name });
                                    Swal.fire({ icon: 'success', title: 'Succès !', text: 'Le poste de trésorerie a été créé et sélectionné.', timer: 2000, showConfirmButton: false });
                                } else {
                                    throw new Error(result.error || 'Erreur lors de la création');
                                }
                            })
                            .catch(err => {
                                Swal.fire({ icon: 'error', title: 'Oups...', text: 'Une erreur est survenue : ' + err.message });
                            })
                            .finally(() => {
                                btn.disabled = false;
                                btn.innerHTML = originalBtnHtml;
                            });
                    };
                }

                // --- GESTION DES COMPTES GÉNÉRAUX (réutilisation du contrat JSON existant plan_comptable.store) ---
                const modalCenterCreateEl = document.getElementById('modalCenterCreate');
                if (modalCenterCreateEl) {
                    const compteModal = new bootstrap.Modal(modalCenterCreateEl);
                    const numeroCompteInput = document.getElementById('numero_de_compte');
                    const intituleCompteInput = document.getElementById('intitule');
                    const btnCreateCompte = document.getElementById('btnCreateCompte');

                    window.createCompteSimple = function (event) {
                        if (event) event.preventDefault();

                        const numero_de_compte = numeroCompteInput.value.trim();
                        const intitule = intituleCompteInput.value.trim();

                        if (!numero_de_compte || !intitule) {
                            Swal.fire({ icon: 'warning', title: 'Champs manquants', text: 'Veuillez remplir toutes les informations obligatoires.' });
                            return;
                        }

                        const originalBtnHtml = btnCreateCompte.innerHTML;
                        btnCreateCompte.disabled = true;
                        btnCreateCompte.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';

                        fetch('{{ route("plan_comptable.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ numero_de_compte, intitule })
                        })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) {
                                    compteModal.hide();
                                    window.fcInjecterElementCree('compte', {
                                        id: result.id,
                                        numero_de_compte: result.numero_de_compte,
                                        intitule: result.intitule,
                                    });
                                    numeroCompteInput.value = '';
                                    intituleCompteInput.value = '';
                                    Swal.fire({ icon: 'success', title: 'Succès !', text: 'Le compte général a été créé et sélectionné.', timer: 2000, showConfirmButton: false });
                                } else {
                                    throw new Error(result.error || 'Erreur lors de la création');
                                }
                            })
                            .catch(err => {
                                Swal.fire({ icon: 'error', title: 'Oups...', text: 'Une erreur est survenue : ' + err.message });
                            })
                            .finally(() => {
                                btnCreateCompte.disabled = false;
                                btnCreateCompte.innerHTML = originalBtnHtml;
                            });
                    };
                }

                // --- GESTION DE LA CRÉATION DE JOURNAL INLINE ---
                window.handleTresoChangeInline = function() {
                    const caisse = document.getElementById('inline_treso_caisse');
                    const banque = document.getElementById('inline_treso_banque');
                    const autre = document.getElementById('inline_treso_autre');
                    
                    if (caisse && banque && autre) {
                        if (caisse.checked || banque.checked) {
                            autre.value = '';
                            autre.disabled = true;
                            autre.classList.add('bg-slate-50');
                        } else {
                            autre.disabled = false;
                            autre.classList.remove('bg-slate-50');
                        }
                    }
                    window.fetchNextJournalCode();
                };

                window.handleOtherInputInline = function() {
                    const caisse = document.getElementById('inline_treso_caisse');
                    const banque = document.getElementById('inline_treso_banque');
                    const autre = document.getElementById('inline_treso_autre');
                    
                    if (caisse && banque && autre) {
                        if (autre.value.trim() !== '') {
                            caisse.checked = false;
                            banque.checked = false;
                            caisse.disabled = true;
                            banque.disabled = true;
                        } else {
                            caisse.disabled = false;
                            banque.disabled = false;
                        }
                    }
                    window.fetchNextJournalCode();
                };

                // Définir fetchNextJournalCode pour qu'il soit disponible dans tous les listeners
                window.fetchNextJournalCode = function () {
                    const type = document.getElementById('inline_journal_type')?.value;
                    const codeInput = document.getElementById('inline_journal_code');
                    if (!type || !codeInput) return;

                    const prefixMap = {
                        'Achats': 'ACH', 'Ventes': 'VEN', 'Opérations Diverses': 'OD',
                        'Standard': 'STD', 'REPORT A NOUVEAU': 'RAN'
                    };
                    let prefix = prefixMap[type] || null;

                    if (type === 'Tresorerie') {
                        const caisse = document.getElementById('inline_treso_caisse');
                        const banque = document.getElementById('inline_treso_banque');
                        const autre = document.getElementById('inline_treso_autre');
                        if (caisse?.checked) prefix = 'CAI';
                        else if (banque?.checked) prefix = 'BQ';
                        else if (autre?.value.trim()) prefix = autre.value.trim().substring(0, 3).toUpperCase();
                        else return; // Attendre la sélection Caisse/Banque/Autre
                    }

                    if (!prefix) return;

                    fetch(`/admin/config/get-next-journal-code?prefix=${prefix}`)
                        .then(r => r.json())
                        .then(data => { if (data.success) codeInput.value = data.code; })
                        .catch(() => {
                            const digits = {{ auth()->user()->company->journal_code_digits ?? 4 }};
                            codeInput.value = prefix.padEnd(digits, '0').substring(0, digits);
                        });
                };

                const modalCreateJournalEl = document.getElementById('modalCreateJournalInline');
                if (modalCreateJournalEl) {
                    const inlineJournalType = document.getElementById('inline_journal_type');
                    const inlineTresoOptions = document.getElementById('inline_tresorerie_options');

                    // Afficher/masquer les options trésorerie selon le type
                    inlineJournalType.addEventListener('change', function () {
                        const isTreso = ['Banque', 'Caisse', 'Tresorerie'].includes(this.value);
                        if (isTreso) {
                            inlineTresoOptions.classList.remove('d-none');
                            // Ne pas générer le code tout de suite — attendre Caisse/Banque/Autre
                            document.getElementById('inline_journal_code').value = '';
                        } else {
                            inlineTresoOptions.classList.add('d-none');
                            document.getElementById('inline_treso_caisse').checked = false;
                            document.getElementById('inline_treso_banque').checked = false;
                            document.getElementById('inline_treso_autre').value = '';
                            // Pour les autres types, générer le code immédiatement
                            window.fetchNextJournalCode();
                        }
                    });

                    // Réinitialiser le modal à sa fermeture
                    modalCreateJournalEl.addEventListener('hidden.bs.modal', function () {
                        document.getElementById('inline_journal_type').value = '';
                        document.getElementById('inline_journal_code').value = '';
                        document.getElementById('inline_journal_intitule').value = '';
                        document.getElementById('inline_journal_analytique').value = 'non';
                        document.getElementById('inline_treso_caisse').checked = false;
                        document.getElementById('inline_treso_banque').checked = false;
                        document.getElementById('inline_treso_autre').value = '';
                        document.getElementById('inline_compte_contrepartie').value = '';
                        document.getElementById('inline_rapprochement').value = '';
                        inlineTresoOptions.classList.add('d-none');
                    });
                }


                // Crée le journal via AJAX et l'injecte dans le select JOURNAL
                window.createJournalInline = function () {
                    const type = document.getElementById('inline_journal_type')?.value;
                    const code_journal = document.getElementById('inline_journal_code')?.value.trim();
                    const intitule = document.getElementById('inline_journal_intitule')?.value.trim();
                    const traitement_analytique = document.getElementById('inline_journal_analytique')?.value;

                    if (!type || !intitule) {
                        Swal.fire({ icon: 'warning', title: 'Champs manquants', text: 'Veuillez remplir le type et l\'intitulé du journal.' });
                        return;
                    }

                    if (!code_journal) {
                        Swal.fire({ icon: 'warning', title: 'Code non généré', text: 'Veuillez sélectionner un type pour générer le code journal automatiquement.' });
                        return;
                    }

                    // Construire les données trésorerie si applicable
                    let poste_tresorerie = null;
                    let poste_tresorerie_autre = null;
                    if (['Banque', 'Caisse', 'Tresorerie'].includes(type)) {
                        const caisse = document.getElementById('inline_treso_caisse');
                        const banque = document.getElementById('inline_treso_banque');
                        const autre = document.getElementById('inline_treso_autre');
                        if (caisse?.checked) poste_tresorerie = 'Caisse';
                        else if (banque?.checked) poste_tresorerie = 'Banque';
                        else if (autre?.value.trim()) poste_tresorerie_autre = autre.value.trim();
                    }

                    const compte_de_contrepartie = document.getElementById('inline_compte_contrepartie')?.value || '';
                    const rapprochement_sur = document.getElementById('inline_rapprochement')?.value || '';

                    const btn = document.getElementById('btnSaveJournalInline');
                    const originalHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';

                    // Utiliser FormData pour être compatible avec le contrôleur existant
                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    formData.append('type', type);
                    formData.append('intitule', intitule);
                    formData.append('traitement_analytique', traitement_analytique);
                    if (code_journal) formData.append('code_journal', code_journal);
                    if (poste_tresorerie) formData.append('poste_tresorerie', poste_tresorerie);
                    if (poste_tresorerie_autre) formData.append('poste_tresorerie_autre', poste_tresorerie_autre);
                    if (compte_de_contrepartie) formData.append('compte_de_contrepartie', compte_de_contrepartie);
                    if (rapprochement_sur) formData.append('rapprochement_sur', rapprochement_sur);

                    fetch('{{ route("accounting_journals.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success || result.id) {
                            // Fermer le modal
                            const bsModal = bootstrap.Modal.getInstance(document.getElementById('modalCreateJournalInline'));
                            if (bsModal) bsModal.hide();

                            // Injecter la nouvelle option dans le select JOURNAL et la sélectionner
                            const select = document.getElementById('code_journal_id');
                            const newId = result.id || result.journal?.id;
                            const newCode = result.code_journal || result.journal?.code_journal || code_journal;
                            const newIntitule = result.intitule || result.journal?.intitule || intitule;
                            const newType = result.type || result.journal?.type || type;
                            const newContrepartie = result.compte_de_contrepartie || result.journal?.compte_de_contrepartie || '';

                            if (select && newId) {
                                const opt = new Option(`${newCode} - ${newIntitule}`, newId, true, true);
                                opt.dataset.code_journal_j = newCode;
                                opt.dataset.intitule_j = newIntitule;
                                opt.dataset.type_j = newType;
                                opt.dataset.contrepartie = newContrepartie;
                                select.appendChild(opt);
                                select.value = newId;
                                // Déclencher l'événement change pour que saisieGrille puisse réagir
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Journal créé !',
                                text: `Le journal ${newCode} - ${newIntitule} a été créé et sélectionné.`,
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            throw new Error(result.message || result.error || 'Erreur lors de la création du journal.');
                        }
                    })
                    .catch(err => {
                        Swal.fire({ icon: 'error', title: 'Erreur', text: err.message });
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    });
                };
            </script>
</body>

</html>