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
                        
                        {{-- Messages de succès/erreur --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        {{-- En-tête avec avatar --}}
                        <div class="card mb-4 border-0 shadow-sm overflow-hidden">
                            <!-- Background Decoration -->
                            <div class="card-header border-0 p-0" style="height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                            
                            <div class="card-body position-relative pt-0 px-4 pb-4">
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center align-items-md-end">
                                    
                                    <!-- Avatar & Basic Info Wrapper -->
                                    <div class="d-flex flex-column flex-md-row align-items-center gap-4" style="margin-top: -50px;">
                                        
                                        <!-- Avatar Section -->
                                        <form action="{{ route('settings.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm" class="flex-shrink-0">
                                            @csrf
                                            <div class="position-relative d-inline-block">
                                                @if($user->profile_photo_path)
                                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" id="avatarPreview" alt="Avatar" class="rounded-circle shadow-lg bg-white" style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #fff;">
                                                @else
                                                    <!-- Placeholder for preview if no image exists initially -->
                                                    <img id="avatarPreview" src="" class="rounded-circle shadow-lg bg-white d-none" style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #fff;">
                                                    
                                                    <div id="avatarInitials" class="avatar avatar-xl rounded-circle d-inline-flex align-items-center justify-content-center bg-white text-primary shadow-lg"
                                                        style="width: 140px; height: 140px; font-size: 3.5rem; font-weight: bold; border: 4px solid #fff;">
                                                        {{ $user->initiales }}
                                                    </div>
                                                @endif
                                                
                                                <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow p-2 mb-2 me-2" style="cursor: pointer; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border: 1px solid #e7e9ed; transition: all 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                                                    <i class="bx bx-camera text-primary"></i>
                                                    <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewAndSubmit(this)">
                                                </label>
                                            </div>
                                        </form>

                                        <script>
                                            function previewAndSubmit(input) {
                                                if (input.files && input.files[0]) {
                                                    var reader = new FileReader();
                                                    
                                                    reader.onload = function(e) {
                                                        // Show preview image
                                                        var preview = document.getElementById('avatarPreview');
                                                        var initials = document.getElementById('avatarInitials');
                                                        
                                                        preview.src = e.target.result;
                                                        preview.classList.remove('d-none');
                                                        if(initials) initials.classList.add('d-none');
                                                        
                                                        // Submit form after a brief delay to allow UI update
                                                        setTimeout(() => {
                                                            document.getElementById('avatarForm').submit();
                                                        }, 500);
                                                    }
                                                    
                                                    reader.readAsDataURL(input.files[0]);
                                                }
                                            }
                                        </script>

                                        <!-- User Info -->
                                        <div class="text-center text-md-start mt-3 mt-md-5 pt-md-2">
                                            <h3 class="mb-1 text-dark fw-bold">{{ $user->last_name }} {{ $user->name }}</h3>
                                            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 text-muted">
                                                <span class="badge bg-label-{{ $user->role === 'admin' ? 'warning' : ($user->role === 'super_admin' ? 'danger' : 'info') }} rounded-pill px-3">
                                                    {{ $user->role === 'super_admin' ? 'Super Admin' : ucfirst($user->role) }}
                                                </span>
                                                <span class="d-flex align-items-center"><i class="bx bx-envelope me-1"></i>{{ $user->email_adresse }}</span>
                                                @if($company)
                                                    <span class="d-none d-sm-inline mx-1">•</span>
                                                    <span class="d-flex align-items-center"><i class="bx bx-buildings me-1"></i>{{ $company->company_name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Side Actions/Stats -->
                                    <div class="d-flex gap-3 mt-4 mt-md-0 pb-2">
                                        <div class="text-center px-3 border-end d-none d-sm-block">
                                            <div class="small text-muted text-uppercase fw-bold">Statut</div>
                                            <div class="fw-bold text-success"><i class="bx bxs-circle me-1 small"></i>En ligne</div>
                                        </div>
                                        <div class="text-center px-3 d-none d-sm-block">
                                            <div class="small text-muted text-uppercase fw-bold">Inscrit le</div>
                                            <div class="fw-bold text-dark">{{ $user->created_at->format('d/m/Y') }}</div>
                                        </div>
                                        <a href="{{ route('settings') }}" class="btn btn-primary d-flex align-items-center shadow-sm">
                                            <i class="bx bx-cog me-2"></i> Éditer
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- Grille de cartes --}}
                        <div class="row g-4">
                            
                            {{-- Informations personnelles --}}
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0"><i class="bx bx-user me-2"></i>Informations personnelles</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Prénom</label>
                                            <p class="mb-0 fw-semibold">{{ $user->name }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Nom de famille</label>
                                            <p class="mb-0 fw-semibold">{{ $user->last_name }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Adresse email</label>
                                            <p class="mb-0 fw-semibold">{{ $user->email_adresse }}</p>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label text-muted small">Rôle</label>
                                            <p class="mb-0 fw-semibold">
                                                {{ $user->role === 'super_admin' ? 'Super Administrateur' : ($user->role === 'admin' ? 'Administrateur' : 'Comptable') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Entreprise --}}
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0"><i class="bx bx-buildings me-2"></i>Entreprise</h5>
                                    </div>
                                    <div class="card-body">
                                        @if($company)
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Nom de l'entreprise</label>
                                                <p class="mb-0 fw-semibold">{{ $company->company_name }}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Activité</label>
                                                <p class="mb-0 fw-semibold">{{ $company->activity ?? 'Non renseigné' }}</p>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label text-muted small">Ville</label>
                                                <p class="mb-0 fw-semibold">{{ $company->city ?? 'Non renseigné' }}</p>
                                            </div>
                                            <div class="mb-0">
                                                <label class="form-label text-muted small">Email entreprise</label>
                                                <p class="mb-0 fw-semibold">{{ $company->email_adresse ?? 'Non renseigné' }}</p>
                                            </div>
                                        @else
                                            <p class="text-muted">Aucune entreprise rattachée</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Statistiques --}}
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0"><i class="bx bx-bar-chart me-2"></i>Statistiques</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Membre depuis</label>
                                            <p class="mb-0 fw-semibold">{{ $stats['member_since']->format('d/m/Y') }}</p>
                                            <small class="text-muted">{{ $stats['member_since']->diffForHumans() }}</small>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Dernière activité</label>
                                            <p class="mb-0 fw-semibold">{{ $stats['last_activity']->format('d/m/Y H:i') }}</p>
                                            <small class="text-muted">{{ $stats['last_activity']->diffForHumans() }}</small>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label text-muted small">Statut</label>
                                            <p class="mb-0">
                                                <span class="badge bg-label-{{ $user->is_online ? 'success' : 'secondary' }}">
                                                    {{ $user->is_online ? 'En ligne' : 'Hors ligne' }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Habilitations (si comptable) --}}
                            @if($user->role === 'comptable')
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="mb-0"><i class="bx bx-shield-alt me-2"></i>Mes habilitations</h5>
                                        <span class="badge bg-primary">{{ $stats['habilitations_count'] }} permissions</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($habilitations as $key => $value)
                                                @if($value)
                                                <div class="col-12 mb-2">
                                                    <i class="bx bx-check-circle text-success me-2"></i>
                                                    <span>{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                                </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                        </div>

                        {{-- Boutons d'action --}}
                        <div class="mt-4 d-flex gap-2">
                            <a href="{{ route('user.settings') }}" class="btn btn-primary">
                                <i class="bx bx-cog me-2"></i>Paramètres
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
