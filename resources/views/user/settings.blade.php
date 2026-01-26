<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<style>
    .settings-nav-card {
        border: none;
        border-radius: 20px;
        background: #fff;
    }
    .settings-link {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px 20px;
        color: #64748b;
        text-decoration: none !important;
        border-radius: 15px;
        transition: all 0.3s ease;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .settings-link:hover, .settings-link.active {
        background-color: #eff6ff;
        color: #2563eb;
    }
    .settings-link i {
        font-size: 20px;
    }
    .form-premium .form-label {
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 8px;
    }
    .form-premium .form-control {
        border-radius: 12px;
        padding: 12px 18px;
        border: 1px solid #e2e8f0;
        background-color: #f8fafc;
        transition: all 0.3s ease;
    }
    .form-premium .form-control:focus {
        background-color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .section-card {
        border: none;
        border-radius: 25px;
        padding: 35px;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Paramètres du <span class="text-blue-600">Compte</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row g-5">
                            <!-- Sidebar Paramètres -->
                            <div class="col-lg-3">
                                <div class="card settings-nav-card shadow-sm p-3">
                                    <div class="nav flex-column nav-pills" id="settingsTabs" role="tablist">
                                        <a class="settings-link active" data-bs-toggle="pill" href="#account" role="tab">
                                            <i class="fa-solid fa-circle-user"></i>
                                            <span>Profil & Identité</span>
                                        </a>
                                        <a class="settings-link" data-bs-toggle="pill" href="#security" role="tab">
                                            <i class="fa-solid fa-shield-halved"></i>
                                            <span>Sécurité & Accès</span>
                                        </a>
                                        <a class="settings-link" data-bs-toggle="pill" href="#preferences" role="tab">
                                            <i class="fa-solid fa-sliders"></i>
                                            <span>Préférences UI</span>
                                        </a>
                                        <a class="settings-link" data-bs-toggle="pill" href="#notifications" role="tab">
                                            <i class="fa-solid fa-bell"></i>
                                            <span>Alerte & Mails</span>
                                        </a>
                                    </div>
                                    <hr class="my-4 opacity-50">
                                    <div class="p-3 bg-slate-50 rounded-15 text-center">
                                        <p class="text-slate-500 small mb-0">Dernière connexion :</p>
                                        <span class="fw-bold text-slate-800 small">{{ now()->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenu des Paramètres -->
                            <div class="col-lg-9">
                                <div class="tab-content border-0 p-0 shadow-none bg-transparent">
                                    
                                    {{-- IDENTITÉ --}}
                                    <div class="tab-pane fade show active" id="account" role="tabpanel">
                                        <div class="card section-card shadow-sm">
                                            <div class="d-flex align-items-center gap-4 mb-5">
                                                <div class="bg-blue-50 text-blue-600 p-4 rounded-20">
                                                    <i class="fa-solid fa-user-pen fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h4 class="fw-black m-0 text-slate-800">Votre Identité</h4>
                                                    <p class="text-slate-500 m-0">Gérez vos informations personnelles et professionnelles.</p>
                                                </div>
                                            </div>

                                            <form action="{{ route('user.settings.account') }}" method="POST" class="form-premium">
                                                @csrf
                                                @method('PUT')
                                                <div class="row g-4">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Prénom</label>
                                                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nom de famille</label>
                                                        <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Adresse Email Professionnelle</label>
                                                        <input type="email" name="email_adresse" class="form-control" value="{{ $user->email_adresse }}" required>
                                                        <div class="form-text text-slate-400 mt-2">Cette adresse est utilisée pour l'envoi de vos rapports et alertes système.</div>
                                                    </div>
                                                    <div class="col-12 mt-5">
                                                        <button type="submit" class="btn btn-premium-blue">
                                                            Enregistrer les modifications
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- SÉCURITÉ --}}
                                    <div class="tab-pane fade" id="security" role="tabpanel">
                                        <div class="card section-card shadow-sm">
                                            <div class="d-flex align-items-center gap-4 mb-5">
                                                <div class="bg-indigo-50 text-indigo-600 p-4 rounded-20">
                                                    <i class="fa-solid fa-lock fa-2x"></i>
                                                </div>
                                                <div>
                                                    <h4 class="fw-black m-0 text-slate-800"> Protection du compte</h4>
                                                    <p class="text-slate-500 m-0">Sécurisez vos accès à la plateforme ComptaFlow.</p>
                                                </div>
                                            </div>

                                            <form action="{{ route('user.settings.password') }}" method="POST" class="form-premium">
                                                @csrf
                                                @method('PUT')
                                                <div class="row g-4">
                                                    <div class="col-12">
                                                        <label class="form-label">Mot de passe actuel</label>
                                                        <input type="password" name="current_password" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nouveau mot de passe</label>
                                                        <input type="password" name="password" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                                                        <input type="password" name="password_confirmation" class="form-control" required>
                                                    </div>
                                                    <div class="col-12 mt-5">
                                                        <button type="submit" class="btn btn-premium-blue" style="background:#4f46e5 !important;">
                                                            Mettre à jour la sécurité
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                            <hr class="my-5 opacity-50">
                                            <div class="p-4 rounded-20 bg-rose-50 border border-rose-100">
                                                <div class="d-flex gap-3">
                                                    <i class="fa-solid fa-circle-exclamation text-rose-600 mt-1"></i>
                                                    <div>
                                                        <h6 class="fw-bold text-rose-800">Double authentification (2FA)</h6>
                                                        <p class="text-rose-600 small m-0">Ajoutez une couche de sécurité supplémentaire. Cette option sera disponible dans la prochaine mise à jour.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- PRÉFÉRENCES --}}
                                    <div class="tab-pane fade" id="preferences" role="tabpanel">
                                        <div class="card section-card shadow-sm">
                                            <h4 class="fw-black text-slate-800 mb-5">Configuration de l'interface</h4>
                                            
                                            <div class="space-y-6">
                                                <div class="d-flex justify-content-between align-items-center p-4 rounded-20 bg-slate-50 mb-3">
                                                    <div>
                                                        <h6 class="fw-bold m-0">Mode Sombre</h6>
                                                        <p class="text-slate-500 small m-0">Réduit la fatigue oculaire en soirée.</p>
                                                    </div>
                                                    <div class="form-check form-switch fs-4">
                                                        <input class="form-check-input" type="checkbox" disabled>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center p-4 rounded-20 bg-slate-50">
                                                    <div>
                                                        <h6 class="fw-bold m-0">Langue de l'interface</h6>
                                                        <p class="text-slate-500 small m-0">Français (par défaut)</p>
                                                    </div>
                                                    <i class="fa-solid fa-chevron-right text-slate-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
