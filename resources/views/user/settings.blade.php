<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations' => $habilitations])
            
            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <h4 class="mb-4"><i class="bx bx-cog me-2"></i>Paramètres du compte</h4>

                        {{-- Messages de succès/erreur --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                {{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        {{-- Navigation par onglets --}}
                        <div class="card">
                            <ul class="nav nav-tabs nav-fill" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#account" role="tab">
                                        <i class="bx bx-user me-1"></i>Mon Compte
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security" role="tab">
                                        <i class="bx bx-lock-alt me-1"></i>Sécurité
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#preferences" role="tab">
                                        <i class="bx bx-palette me-1"></i>Préférences
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content p-4">
                                
                                {{-- Onglet Mon Compte --}}
                                <div class="tab-pane fade show active" id="account" role="tabpanel">
                                    <h5 class="mb-4">Informations du compte</h5>
                                    
                                    <form method="POST" action="{{ route('settings.account') }}">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="name" class="form-label">Prénom <span class="text-danger">*</span></label>
                                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                                    value="{{ old('name', $user->name) }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="last_name" class="form-label">Nom de famille <span class="text-danger">*</span></label>
                                                <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" 
                                                    value="{{ old('last_name', $user->last_name) }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <label for="email_adresse" class="form-label">Adresse email <span class="text-danger">*</span></label>
                                                <input type="email" id="email_adresse" name="email_adresse" class="form-control @error('email_adresse') is-invalid @enderror" 
                                                    value="{{ old('email_adresse', $user->email_adresse) }}" required>
                                                @error('email_adresse')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-save me-2"></i>Enregistrer les modifications
                                                </button>
                                                <a href="{{ route('profile') }}" class="btn btn-outline-secondary">
                                                    Annuler
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                {{-- Onglet Sécurité --}}
                                <div class="tab-pane fade" id="security" role="tabpanel">
                                    <h5 class="mb-4">Changer le mot de passe</h5>
                                    
                                    <form method="POST" action="{{ route('settings.password') }}">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                                                <input type="password" id="current_password" name="current_password" 
                                                    class="form-control @error('current_password') is-invalid @enderror" required>
                                                @error('current_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                                                <input type="password" id="password" name="password" 
                                                    class="form-control @error('password') is-invalid @enderror" required>
                                                <div class="form-text">Minimum 8 caractères</div>
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                                                <input type="password" id="password_confirmation" name="password_confirmation" 
                                                    class="form-control" required>
                                            </div>

                                            <div class="col-12">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-lock-alt me-2"></i>Changer le mot de passe
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <hr class="my-5">

                                    <h5 class="mb-3">Sessions actives</h5>
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        Fonctionnalité à venir : Gérez vos sessions actives et déconnectez-vous à distance.
                                    </div>
                                </div>

                                {{-- Onglet Préférences --}}
                                <div class="tab-pane fade" id="preferences" role="tabpanel">
                                    <h5 class="mb-4">Préférences d'affichage</h5>
                                    
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Fonctionnalités à venir :</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Choix de la langue (Français, Anglais)</li>
                                            <li>Fuseau horaire</li>
                                            <li>Format de date et heure</li>
                                            <li>Thème (Clair, Sombre)</li>
                                            <li>Notifications par email</li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Retour au profil --}}
                        <div class="mt-4">
                            <a href="{{ route('user.profile') }}" class="btn btn-outline-primary">
                                <i class="bx bx-arrow-back me-2"></i>Retour au profil
                            </a>

                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    @include('components.footer')
</body>
</html>
