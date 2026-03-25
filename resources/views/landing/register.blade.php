<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Inscription {{ ucfirst($type) }} - Flow Compta</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <style>
        body {
            background-color: #f8fafc;
            font-family: "Public Sans", sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Blobs */
        .bg-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }
        .blob-1 {
            width: 600px; height: 600px;
            background: #696cff;
            top: -20%; left: -10%;
        }
        .blob-2 {
            width: 600px; height: 600px;
            background: #e83e8c;
            bottom: -20%; right: -10%;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
            border-radius: 20px;
            padding: 3rem;
        }

        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 25px;
            font-weight: 700;
            color: #32475c;
            font-size: 1.25rem;
        }
        .section-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 3px;
            width: 40px;
            background: #696cff;
            border-radius: 4px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            background-color: #f8fafc;
            border: 1px solid #d9dee3;
            color: #566a7f;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: #696cff;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.15);
        }
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #566a7f;
            margin-bottom: 0.4rem;
        }
    </style>
</head>
<body class="py-5">

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>

    <div class="container" style="max-width: 900px;">
        
        <div class="text-center mb-5">
            <a href="{{ route('landing.pricing') }}" class="btn btn-label-secondary btn-sm mb-4 rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Changer de pack
            </a>
            <h1 class="display-6 fw-bolder text-dark">
                Création de votre espace <span class="text-primary">{{ ucfirst($type) }}</span>
            </h1>
            <p class="text-muted mt-2">Remplissez les informations ci-dessous pour configurer votre environnement de travail.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-3 fs-4"></i>
                <div>
                    {{ session('error') }}
                </div>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger mb-4 rounded-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('landing.register_submit') }}" method="POST" class="glass-panel mb-5">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="row g-5">
                
                <!-- Section 1 : Entreprise / Cabinet -->
                <div class="col-md-6">
                    <h2 class="section-title">
                        <i class="fa-solid fa-building text-primary me-2"></i> 
                        Informations {{ $type == 'comptable' ? 'du Cabinet' : 'de l\'Entreprise' }}
                    </h2>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Raison Sociale <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" required class="form-control" placeholder="Ex: Flow SAS">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Forme Juridique <span class="text-danger">*</span></label>
                            <select name="juridique_form" required class="form-select">
                                <option value="">Sélectionner</option>
                                <option value="SARL" {{ old('juridique_form') == 'SARL' ? 'selected' : '' }}>SARL</option>
                                <option value="SAS" {{ old('juridique_form') == 'SAS' ? 'selected' : '' }}>SAS</option>
                                <option value="SA" {{ old('juridique_form') == 'SA' ? 'selected' : '' }}>SA</option>
                                <option value="SUARL" {{ old('juridique_form') == 'SUARL' ? 'selected' : '' }}>SUARL</option>
                                <option value="Entreprise Individuelle" {{ old('juridique_form') == 'Entreprise Individuelle' ? 'selected' : '' }}>Entreprise Individuelle</option>
                            </select>
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Activité <span class="text-danger">*</span></label>
                            <input type="text" name="activity" value="{{ old('activity') }}" required class="form-control" placeholder="Ex: Commerce">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email Entreprise <span class="text-danger">*</span></label>
                            <input type="email" name="company_email" value="{{ old('company_email') }}" required class="form-control" placeholder="contact@entreprise.com">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                            <input type="text" name="phone_number" value="{{ old('phone_number') }}" required class="form-control" placeholder="+225 0000000000">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Ville <span class="text-danger">*</span></label>
                            <input type="text" name="city" value="{{ old('city') }}" required class="form-control" placeholder="Abidjan">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Adresse complète <span class="text-danger">*</span></label>
                            <input type="text" name="adresse" value="{{ old('adresse') }}" required class="form-control" placeholder="Cocody Riviera...">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Boîte Postale <span class="text-danger">*</span></label>
                            <input type="text" name="code_postal" value="{{ old('code_postal') }}" required class="form-control" placeholder="01 BP 0000 Abidjan 01">
                        </div>
                        
                        <div class="col-6">
                            <label class="form-label">Pays <span class="text-danger">*</span></label>
                            <input type="text" name="country" value="{{ old('country') ?? 'Côte d\'Ivoire' }}" required class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Section 2 : Administrateur -->
                <div class="col-md-6">
                    <h2 class="section-title">
                        <i class="fa-solid fa-user-shield text-primary me-2"></i> 
                        Compte Administrateur
                    </h2>
                    
                    <div class="alert alert-primary py-2 px-3 small border-0 bg-label-primary rounded-3 mb-4">
                        Ce profil sera le compte propriétaire (Admin). Il possèdera par défaut tous les droits d'accès pour configurer l'environnement.
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="admin_name" value="{{ old('admin_name') }}" required class="form-control" placeholder="Doe">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="admin_last_name" value="{{ old('admin_last_name') }}" required class="form-control" placeholder="John">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Email de Connexion <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="form-control" placeholder="john.doe@email.com">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="admin_password" required class="form-control" placeholder="Minimum 8 caractères">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Confirmer mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="admin_password_confirmation" required class="form-control" placeholder="Retapez le mot de passe">
                        </div>
                    </div>
                    
                    <div class="mt-5 pt-4 border-top">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill shadow-sm d-flex justify-content-center align-items-center gap-2">
                            Créer mon espace <i class="fa-solid fa-rocket"></i>
                        </button>
                    </div>
                </div>
                
            </div>
        </form>

        <!-- Sécurité -->
        <div class="text-center text-muted small d-flex align-items-center justify-content-center gap-2 pb-4">
            <i class="fa-solid fa-lock"></i>
            Vos données sont chiffrées et stockées de manière sécurisée.
        </div>
    </div>

</body>
</html>
