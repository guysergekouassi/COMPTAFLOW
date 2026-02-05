@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #0f172a;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
    }
    .badge-premium {
        padding: 6px 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-premium-success { background-color: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .badge-premium-danger { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }
    .badge-premium-info { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    
    .company-card-avatar {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }
    .company-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
    }
    .btn-flow-premium {
        background: #0f172a;
        color: white !important;
        border: none;
        padding: 12px 24px;
        border-radius: 14px;
        font-weight: 700;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none !important;
    }
    .btn-flow-premium:hover {
        background: #1e293b;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.2);
    }
    .premium-modal-content {
        background: #ffffff;
        border: none;
        border-radius: 28px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        padding: 2rem !important;
    }
    .input-field-premium {
        transition: all 0.2s ease;
        border: 2px solid #f1f5f9 !important;
        background-color: #f8fafc !important;
        border-radius: 14px !important;
        padding: 0.85rem 1.25rem !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        width: 100%;
    }
    .input-field-premium:focus {
        border-color: #3b82f6 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        outline: none !important;
    }
    .input-label-premium {
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 0.5rem !important;
        display: block !important;
    }

</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            {{-- La sidebar est incluse --}}
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Gestion des <span class="text-gradient">Entités</span>'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header de Section Premium -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-8 gap-4">
                            <div>
                                <h1 class="text-3xl font-black text-slate-900 mb-2">Gouvernance des <span class="text-blue-600">Structures</span></h1>
                                <p class="text-slate-500 font-medium">Administration centrale et pilotage de vos entités comptables.</p>
                            </div>
                            <div>
                                {{-- Bouton Ajouter Entité supprimé sur demande --}}
                            </div>
                        </div>

                        <!-- KPIs Premium -->
                        <div class="row g-6 mb-8">
                            <div class="col-sm-6 col-xl-4">
                                <div class="glass-card p-6">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="stats-icon bg-emerald-50 text-emerald-600">
                                            <i class="fa-solid fa-check-double"></i>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Opérationnel</span>
                                    </div>
                                    <div>
                                        <h3 class="text-3xl font-black text-slate-800 mb-1">{{ number_format($activeAccounts ?? 0) }}</h3>
                                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-0">Comptes Actifs</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-4">
                                <div class="glass-card p-6">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div class="stats-icon bg-amber-50 text-amber-600">
                                            <i class="fa-solid fa-pause-circle"></i>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 bg-amber-50 px-2 py-1 rounded">En sommeil</span>
                                    </div>
                                    <div>
                                        <h3 class="text-3xl font-black text-slate-800 mb-1">{{ number_format($inactiveAccounts ?? 0) }}</h3>
                                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-0">Comptes Inactifs</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Message de succès/erreur --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                <strong>Erreur de Validation:</strong> Veuillez vérifier les champs du formulaire.
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif


                        <!-- Section Grille des Entités Premium -->
                        <div class="company-grid">
                            @forelse ($comptaAccounts as $comptaAccount)
                                <div class="glass-card">
                                    <div class="p-6">
                                        <div class="d-flex justify-content-between align-items-start mb-6">
                                            <div class="company-card-avatar">
                                                {{ strtoupper(substr($comptaAccount->company_name, 0, 1)) }}
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn p-2 rounded-xl hover:bg-slate-50 transition-colors dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                    <i class="fa-solid fa-ellipsis-vertical text-slate-400"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end p-2 border-0 shadow-xl rounded-2xl">
                                                    <a class="dropdown-item py-2 rounded-xl details-btn" href="javascript:void(0);"
                                                        data-bs-toggle="modal" data-bs-target="#modalSeeComptaAccount"
                                                        data-company-name="{{ $comptaAccount->company_name }}"
                                                        data-activity="{{ $comptaAccount->activity }}"
                                                        data-juridique-form="{{ $comptaAccount->juridique_form }}"
                                                        data-social-capital="{{ $comptaAccount->social_capital }}"
                                                        data-adresse="{{ $comptaAccount->adresse }}"
                                                        data-code-postal="{{ $comptaAccount->code_postal }}"
                                                        data-city="{{ $comptaAccount->city }}"
                                                        data-country="{{ $comptaAccount->country }}"
                                                        data-phone-number="{{ $comptaAccount->phone_number }}"
                                                        data-email-adresse="{{ $comptaAccount->email_adresse }}"
                                                        data-identification-tva="{{ $comptaAccount->identification_TVA }}"
                                                        data-is-active="{{ $comptaAccount->is_active }}">
                                                        <i class="fa-solid fa-eye me-2 text-slate-400"></i> <span class="fw-bold text-slate-600">Détails complets</span>
                                                    </a>
                                                    <a class="dropdown-item py-2 rounded-xl edit-btn" href="javascript:void(0);"
                                                        data-bs-toggle="modal" data-bs-target="#modalUpdateComptaAccount"
                                                        data-account-id="{{ $comptaAccount->id }}"
                                                        data-company-name="{{ $comptaAccount->company_name }}"
                                                        data-activity="{{ $comptaAccount->activity }}"
                                                        data-juridique-form="{{ $comptaAccount->juridique_form }}"
                                                        data-social-capital="{{ $comptaAccount->social_capital }}"
                                                        data-adresse="{{ $comptaAccount->adresse }}"
                                                        data-code-postal="{{ $comptaAccount->code_postal }}"
                                                        data-city="{{ $comptaAccount->city }}"
                                                        data-country="{{ $comptaAccount->country }}"
                                                        data-phone-number="{{ $comptaAccount->phone_number }}"
                                                        data-email-adresse="{{ $comptaAccount->email_adresse }}"
                                                        data-identification-tva="{{ $comptaAccount->identification_TVA }}"
                                                        data-is-active="{{ $comptaAccount->is_active }}">
                                                        <i class="fa-solid fa-pen-to-square me-2 text-blue-500"></i> <span class="fw-bold text-slate-600">Modifier</span>
                                                    </a>
                                                    <div class="dropdown-divider opacity-50"></div>
                                                    <a class="dropdown-item py-2 rounded-xl delete-btn text-danger" href="javascript:void(0);"
                                                        data-bs-toggle="modal" data-bs-target="#deleteAccountModal"
                                                        data-account-id="{{ $comptaAccount->id }}"
                                                        data-company-name="{{ $comptaAccount->company_name }}">
                                                        <i class="fa-solid fa-trash-can me-2"></i> <span class="fw-bold">Supprimer</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="text-xl font-black text-slate-800 mb-1">{{ $comptaAccount->company_name }}</h4>
                                        <p class="text-slate-400 text-sm font-semibold mb-4 d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-tag text-xs"></i> {{ $comptaAccount->activity ?: 'Secteur non défini' }}
                                        </p>

                                        <div class="d-flex flex-wrap gap-2 mb-6">
                                            <span class="badge-premium {{ $comptaAccount->is_active ? 'badge-premium-success' : 'badge-premium-danger' }}">
                                                {{ $comptaAccount->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                            <span class="badge-premium badge-premium-info">
                                                {{ $comptaAccount->juridique_form ?: 'SARL' }}
                                            </span>
                                        </div>

                                        <div class="row g-4 mb-6">
                                            <div class="col-6">
                                                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Écritures</p>
                                                    <p class="text-lg font-extrabold text-slate-700 mb-0">{{ number_format($comptaAccount->ecritures_count ?? 0) }}</p>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Équipe</p>
                                                    <p class="text-lg font-extrabold text-slate-700 mb-0">{{ number_format($comptaAccount->users_count ?? 0) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-6 pb-6 mt-auto">
                                        <a href="{{ route('compta_accounts.access', ['companyId' => $comptaAccount->id]) }}" 
                                           class="btn w-100 py-3 rounded-2xl font-bold text-white transition-all shadow-lg"
                                           style="background-color: #2563eb; border-color: #2563eb;"> {{-- Force Blue Style --}}
                                            <i class="fa-solid fa-door-open me-2"></i> Accéder au dossier
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5 glass-card">
                                    <i class="fa-solid fa-building-circle-exclamation fa-3x text-muted mb-3"></i>
                                    <h5>Aucune entité trouvée</h5>
                                    <p class="text-muted">Commencez par créer votre première structure comptable</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="modal fade" id="modalCreateComptaAccount" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content premium-modal-content">
                                    <div class="text-center mb-8">
                                        <h2 class="text-2xl font-black text-slate-800 mb-2">Nouvelle <span class="text-blue-600">Entité</span></h2>
                                        <p class="text-slate-400 font-medium">Configurez une nouvelle structure comptable</p>
                                    </div>
                                    
                                    <form id="createAccountForm" method="POST" action="{{ route('compta_accounts.store') }}">
                                        @csrf
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Nom de la Société <span class="text-danger">*</span></label>
                                                <input type="text" name="company_name" class="input-field-premium" value="{{ old('company_name') }}" required placeholder="Ex: Ma Structure SARL" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Secteur d'activité</label>
                                                <input type="text" name="activity" class="input-field-premium" value="{{ old('activity') }}" placeholder="Ex: Commerce, Conseil..." />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Forme Juridique</label>
                                                <input type="text" name="juridique_form" class="input-field-premium" value="{{ old('juridique_form') }}" placeholder="Ex: SARL, SAS, SA..." />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Capital Social</label>
                                                <input type="number" step="0.01" name="social_capital" class="input-field-premium" value="{{ old('social_capital') }}" placeholder="0.00" />
                                            </div>
                                            <div class="col-12">
                                                <label class="input-label-premium">Adresse Complète</label>
                                                <input type="text" name="adresse" class="input-field-premium" value="{{ old('adresse') }}" placeholder="N°, Rue, Quartier..." />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="input-label-premium">Code Postal</label>
                                                <input type="text" name="code_postal" class="input-field-premium" value="{{ old('code_postal') }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="input-label-premium">Ville</label>
                                                <input type="text" name="city" class="input-field-premium" value="{{ old('city') }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="input-label-premium">Pays</label>
                                                <input type="text" name="country" class="input-field-premium" value="{{ old('country') }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Email de contact <span class="text-danger">*</span></label>
                                                <input type="email" name="email_adresse" class="input-field-premium" value="{{ old('email_adresse') }}" required placeholder="contact@entreprise.com" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Numéro de Téléphone</label>
                                                <input type="text" name="phone_number" class="input-field-premium" value="{{ old('phone_number') }}" placeholder="+225 ..." />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">COMPTE CONTRIBUABLE</label>
                                                <input type="text" name="identification_TVA" class="input-field-premium" value="{{ old('identification_TVA') }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Statut Initial</label>
                                                <select name="is_active" class="input-field-premium">
                                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Actif</option>
                                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-3 justify-content-end mt-10">
                                            <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-premium-blue">
                                                Créer l'entité
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <div class="modal fade" id="modalUpdateComptaAccount" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content premium-modal-content">
                                    <div class="text-center mb-8">
                                        <h2 class="text-2xl font-black text-slate-800 mb-2">Modifier l'<span class="text-blue-600">Entité</span></h2>
                                        <p class="text-slate-400 font-medium">Mise à jour des informations structurelles</p>
                                    </div>
                                    
                                    <form id="updateAccountForm" method="POST" action="">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="id" id="updateAccountId" />

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Nom de la Société <span class="text-danger">*</span></label>
                                                <input type="text" id="update_company_name" name="company_name" class="input-field-premium" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Secteur d'activité</label>
                                                <input type="text" id="update_activity" name="activity" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Forme Juridique</label>
                                                <input type="text" id="update_juridique_form" name="juridique_form" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Capital Social</label>
                                                <input type="number" step="0.01" id="update_social_capital" name="social_capital" class="input-field-premium" />
                                            </div>
                                            <div class="col-12">
                                                <label class="input-label-premium">Adresse Complète</label>
                                                <input type="text" id="update_adresse" name="adresse" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="input-label-premium">Code Postal</label>
                                                <input type="text" id="update_code_postal" name="code_postal" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="input-label-premium">Ville</label>
                                                <input type="text" id="update_city" name="city" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="input-label-premium">Pays</label>
                                                <input type="text" id="update_country" name="country" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Email de contact <span class="text-danger">*</span></label>
                                                <input type="email" id="update_email_adresse" name="email_adresse" class="input-field-premium" required />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Numéro de Téléphone</label>
                                                <input type="text" id="update_phone_number" name="phone_number" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Identification TVA</label>
                                                <input type="text" id="update_identification_TVA" name="identification_TVA" class="input-field-premium" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="input-label-premium">Statut</label>
                                                <select id="update_is_active" name="is_active" class="input-field-premium">
                                                    <option value="1">Actif</option>
                                                    <option value="0">Inactif</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-3 justify-content-end mt-10">
                                            <button type="button" class="btn btn-premium-outline" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-premium-blue">
                                                Sauvegarder
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalSeeComptaAccount" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content premium-modal-content">
                                    <div class="text-center mb-8">
                                        <h2 class="text-2xl font-black text-slate-800 mb-2" id="seeAccountTitle">Détails de l'entité</h2>
                                        <div class="h-1 w-12 bg-blue-600 mx-auto rounded-full"></div>
                                    </div>
                                    
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <h6 class="text-[10px] font-black uppercase tracking-widest text-blue-600 mb-2 px-1">Informations Générales</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Nom de la Société</label>
                                            <input type="text" id="see_company_name" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Secteur d'activité</label>
                                            <input type="text" id="see_activity" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Forme Juridique</label>
                                            <input type="text" id="see_juridique_form" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Capital Social</label>
                                            <input type="text" id="see_social_capital" class="input-field-premium" readonly />
                                        </div>

                                        <div class="col-12 mt-6">
                                            <h6 class="text-[10px] font-black uppercase tracking-widest text-blue-600 mb-2 px-1">Coordonnées & Fiscalité</h6>
                                        </div>
                                        <div class="col-12">
                                            <label class="input-label-premium text-slate-400">Adresse</label>
                                            <input type="text" id="see_adresse" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="input-label-premium text-slate-400">Code Postal</label>
                                            <input type="text" id="see_code_postal" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="input-label-premium text-slate-400">Ville</label>
                                            <input type="text" id="see_city" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="input-label-premium text-slate-400">Pays</label>
                                            <input type="text" id="see_country" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Email de contact</label>
                                            <input type="email" id="see_email_adresse" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Numéro de Téléphone</label>
                                            <input type="text" id="see_phone_number" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Identification TVA</label>
                                            <input type="text" id="see_identification_TVA" class="input-field-premium" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="input-label-premium text-slate-400">Statut</label>
                                            <input type="text" id="see_is_active" class="input-field-premium" readonly />
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end mt-10">
                                        <button type="button" class="btn px-10 py-3 rounded-xl font-bold bg-slate-800 text-white hover:bg-slate-900 transition-all shadow-lg" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="deleteAccountModal" tabindex="-1"
                            aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content border-0 shadow">
                                    {{-- L'action sera mise à jour par JS pour inclure l'ID --}}
                                    <form id="deleteAccountForm" method="POST" action="">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header text-dark justify-content-center">
                                            <h5 class="modal-title" id="deleteAccountModalLabel">
                                                <i class="bx bx-error-circle me-2"></i>Confirmer la suppression
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <p class="mb-0">
                                                Êtes-vous sûr de vouloir supprimer le compte
                                                <strong><span id="accountToDeleteName" class="text-danger"></span></strong> ?
                                                Cette action est <strong>irréversible</strong>.
                                            </p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                Supprimer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    @include('components.footer')

    {{-- Script JS pour la gestion des modales et des actions --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Définition des URL de base pour la modification et la suppression
            // Ces URLs utilisent le paramètre de route {id} qui sera remplacé
            const accountsUpdateBaseUrl = "{{ route('compta_accounts.update', ['id' => '__ID__']) }}";
            const accountsDeleteBaseUrl = "{{ route('compta_accounts.destroy', ['id' => '__ID__']) }}";

            // Fonction utilitaire pour formater le capital social
            function formatCurrency(amount) {
                if (amount === null || amount === '' || amount === '0' || amount === 0) return 'N/A';
                return parseFloat(amount).toLocaleString('fr-FR', {
                    style: 'currency',
                    currency: 'XOF', // Vous pouvez ajuster la devise si nécessaire
                    minimumFractionDigits: 0
                });
            }


            // --- Logique pour la MODALE DE MODIFICATION (Update) ---
            // Correction: Utiliser le sélecteur .edit-btn
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-account-id');
                    const updateForm = document.getElementById('updateAccountForm');

                    // 1. Mise à jour de l'action du formulaire avec l'ID correct
                    // L'URL de modification est supposée être: /compta_accounts/{id} avec la méthode PUT
                    updateForm.action = accountsUpdateBaseUrl.replace('__ID__', id);

                    // 2. Remplissage des champs (directement depuis les data-attributs du bouton)
                    document.getElementById('updateAccountId').value = id;
                    document.getElementById('update_company_name').value = this.getAttribute('data-company-name') || '';
                    document.getElementById('update_activity').value = this.getAttribute('data-activity') || '';
                    document.getElementById('update_juridique_form').value = this.getAttribute('data-juridique-form') || '';

                    // Utilisez un nombre ou null pour le champ number (pas de formatage)
                    document.getElementById('update_social_capital').value = this.getAttribute('data-social-capital') || '';

                    document.getElementById('update_adresse').value = this.getAttribute('data-adresse') || '';
                    document.getElementById('update_code_postal').value = this.getAttribute('data-code-postal') || '';
                    document.getElementById('update_city').value = this.getAttribute('data-city') || '';
                    document.getElementById('update_country').value = this.getAttribute('data-country') || '';
                    document.getElementById('update_phone_number').value = this.getAttribute('data-phone-number') || '';
                    document.getElementById('update_email_adresse').value = this.getAttribute('data-email-adresse') || '';
                    document.getElementById('update_identification_TVA').value = this.getAttribute('data-identification-tva') || '';

                    // Sélection correcte du statut (doit être '1' ou '0')
                    // Utiliser parseInt(value) == 1 pour être sûr que '1' ou 1 est traité
                    const isActive = (parseInt(this.getAttribute('data-is-active')) === 1) ? '1' : '0';
                    document.getElementById('update_is_active').value = isActive;
                });
            });

            // --- Logique pour la MODALE D'AFFICHAGE (See) ---
            // Correction: Utiliser le sélecteur .details-btn
            document.querySelectorAll('.details-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Remplissage des champs de lecture seule
                    document.getElementById('see_company_name').value = this.getAttribute('data-company-name') || '';
                    document.getElementById('see_activity').value = this.getAttribute('data-activity') || '';
                    document.getElementById('see_juridique_form').value = this.getAttribute('data-juridique-form') || '';

                    // Formatage du capital social pour l'affichage
                    const socialCapital = this.getAttribute('data-social-capital');
                    document.getElementById('see_social_capital').value = formatCurrency(socialCapital);

                    document.getElementById('see_adresse').value = this.getAttribute('data-adresse') || '';
                    document.getElementById('see_code_postal').value = this.getAttribute('data-code-postal') || '';
                    document.getElementById('see_city').value = this.getAttribute('data-city') || '';
                    document.getElementById('see_country').value = this.getAttribute('data-country') || '';
                    document.getElementById('see_phone_number').value = this.getAttribute('data-phone-number') || '';
                    document.getElementById('see_email_adresse').value = this.getAttribute('data-email-adresse') || '';
                    document.getElementById('see_identification_TVA').value = this.getAttribute('data-identification-tva') || '';

                    // Affichage du statut
                    const isActive = this.getAttribute('data-is-active');
                    document.getElementById('see_is_active').value = (parseInt(isActive) === 1) ? 'Actif' : 'Inactif';

                    // Mise à jour du titre
                    document.getElementById('seeAccountTitle').textContent = `Détails du Compte : ${this.getAttribute('data-company-name')}`;
                });
            });

            // --- Logique pour la MODALE DE SUPPRESSION (Delete) ---
            // Correction: Utiliser le sélecteur .delete-btn n'est pas nécessaire pour le trigger de modal.
            // On utilise l'événement natif show.bs.modal sur la modal elle-même.
            document.getElementById('deleteAccountModal').addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; // Bouton qui a déclenché la modal (.delete-btn)
                const accountId = button.getAttribute('data-account-id');
                const companyName = button.getAttribute('data-company-name');

                // Mise à jour de l'action du formulaire avec l'ID correct
                const deleteForm = document.getElementById('deleteAccountForm');
                deleteForm.action = accountsDeleteBaseUrl.replace('__ID__', accountId);

                // Mise à jour du nom de la société à supprimer dans le message
                document.getElementById('accountToDeleteName').textContent = companyName;
            });

            // --- Logique pour la LIGNE CLICABLE ---
             document.querySelectorAll('.clickable-row').forEach(row => {
                const rowDataHref = row.getAttribute('data-href');

                // Gérer le clic sur la ligne entière (y compris les cellules TD)
                row.addEventListener('click', function(e) {
                    // Clic sur un bouton d'action ou un lien dans la dernière colonne (Actions)
                    if (e.target.closest('.dropdown') || e.target.closest('a')) {
                        // Ne rien faire si l'utilisateur clique sur le menu déroulant ou une action
                        return;
                    }
                    if (rowDataHref) {
                        window.location.href = rowDataHref;
                    }
                });
            });
        });
    </script>
</body>

</html>
