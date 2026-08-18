<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comptaflow | Connexion</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" />

    <style>
        :root {
            --primary-color: #0f172a;
            --brand-blue: #696cff;
            --brand-yellow: #facc15;
            --bg-dark-gradient: radial-gradient(circle at 10% 20%, #0f113a 0%, #696cff 95%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            margin: 0;
            display: flex;
        }

        /* Split Screen Container */
        .login-split-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* Left Side: Branding and Promo */
        .split-left-panel {
            width: 50%;
            background: var(--bg-dark-gradient);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .split-left-panel::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(105, 108, 255, 0.2);
            border-radius: 50%;
            filter: blur(100px);
            top: 10%;
            left: 10%;
        }

        .split-left-panel::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(250, 204, 21, 0.08);
            border-radius: 50%;
            filter: blur(120px);
            bottom: 10%;
            right: 10%;
        }

        .left-content {
            max-width: 520px;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .brand-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 2.5rem;
        }

        .brand-icon {
            font-size: 2.5rem;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(5px);
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin: 0;
        }

        .yellow-badge {
            background-color: var(--brand-yellow);
            color: #0f172a;
            font-family: 'Outfit', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 6px 18px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(250, 204, 21, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .left-main-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 1.5rem;
            letter-spacing: -0.8px;
        }

        .left-subtitle {
            font-size: 1.05rem;
            color: #94a3b8;
            line-height: 1.6;
        }

        /* Right Side: Form Card */
        .split-right-panel {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2.5rem;
            background-color: #f8fafc;
        }

        .login-card {
            width: 100%;
            max-width: 460px;
            background: #ffffff;
            border-radius: 20px;
            padding: 2.75rem;
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 20px 40px -15px rgba(15, 23, 42, 0.03);
            border: 1px solid #e2e8f0;
        }

        .card-header-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .card-header-subtitle {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 2rem;
        }

        /* Tabs custom styling */
        .custom-tabs-container {
            display: flex;
            background-color: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .tab-btn {
            flex: 1;
            font-family: 'Outfit', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 9px 6px;
            border: none;
            background: transparent;
            border-radius: 9px;
            color: #64748b;
            transition: all 0.25s ease;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .tab-btn.active {
            background-color: #ffffff;
            color: #0f172a;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
        }

        /* Form Controls */
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.5rem;
        }

        .input-group-merge {
            position: relative;
        }

        .form-control {
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            color: #1e293b;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.08);
        }

        /* Password input toggle */
        .form-password-toggle .input-group-text {
            background-color: transparent;
            border-left: none;
            border-color: #cbd5e1;
            cursor: pointer;
            color: #64748b;
        }

        .form-password-toggle .form-control {
            border-right: none;
        }

        /* Links and Checkboxes */
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .checkbox-container input {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
        }

        .link-forgot {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--brand-blue);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .link-forgot:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* Buttons */
        .btn-submit-premium {
            background-color: var(--brand-blue);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.15);
        }

        .btn-submit-premium:hover {
            background-color: #1e3a8a;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(30, 64, 175, 0.2);
        }

        .btn-submit-premium-green {
            background-color: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }

        .btn-submit-premium-green:hover {
            background-color: #059669;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.2);
        }

        /* Divider OU */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 1.5rem 0;
        }

        .auth-divider hr {
            flex: 1;
            border-color: #e2e8f0;
            margin: 0;
            opacity: 1;
        }

        .auth-divider span {
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* Google Button */
        .btn-google-premium {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .btn-google-premium:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Bottom texts */
        .auth-footer-text {
            font-size: 0.85rem;
            color: #64748b;
            text-align: center;
            margin-top: 1.75rem;
        }

        .auth-footer-text a {
            color: var(--brand-blue);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer-text a:hover {
            text-decoration: underline;
        }

        .customer-support {
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            text-align: center;
            margin-top: 1.25rem;
            display: block;
            text-decoration: none;
            transition: color 0.15s;
        }

        .customer-support:hover {
            color: #64748b;
        }

        .legal-notice {
            font-size: 0.72rem;
            color: #94a3b8;
            text-align: center;
            line-height: 1.5;
            margin-top: 2.25rem;
        }

        /* Toast Container */
        .toast-container {
            z-index: 9999;
        }

        /* Responsive styling */
        @media (max-width: 991px) {
            .split-left-panel {
                display: none;
            }

            .split-right-panel {
                width: 100%;
                padding: 1.5rem;
            }

            .login-card {
                padding: 2rem 1.5rem;
                box-shadow: none;
                border: none;
                background: transparent;
            }
        }
    </style>
</head>

<body>
    @if (session('status'))
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="logoutToast" class="toast align-items-center text-white bg-success border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('status') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="login-split-container">
        <!-- Left Panel: Brand & Value Prop -->
        <div class="split-left-panel">
            <div class="left-content">
                <div class="brand-logo-container">
                    <i class="bx bx-cabinet brand-icon"></i>
                    <span class="brand-title">Comptaflow</span>
                </div>
                <div class="yellow-badge">
                    La gestion comptable simplifiée
                </div>
                <h1 class="left-main-title">
                    Pilotez votre comptabilité avec précision
                </h1>
                <p class="left-subtitle">
                    La plateforme moderne pour vos écritures comptables, journaux de saisie, déclarations de TVA et ventilations analytiques.
                </p>
            </div>
        </div>

        <!-- Right Panel: Auth Forms -->
        <div class="split-right-panel">
            <div class="login-card">

                <!-- Bouton retour accueil -->
                <div class="mb-4">
                    <a href="{{ route('landing.index') }}" class="btn btn-sm btn-label-secondary rounded-pill">
                        <i class="bx bx-arrow-back me-1"></i> Retour à l'accueil
                    </a>
                </div>

                <h2 class="card-header-title">Connexion</h2>
                <p class="card-header-subtitle">Connectez-vous pour accéder à votre dashboard</p>

                <!-- Tabs Controller (Kept Functional) -->
                <div class="custom-tabs-container">
                    <button type="button" class="tab-btn active" id="btnTabEspace" onclick="switchLoginTab('espace')">
                        <i class="bx bx-briefcase me-1"></i>Mon Espace Comptable
                    </button>
                    <button type="button" class="tab-btn" id="btnTabEntreprise" onclick="switchLoginTab('entreprise')">
                        <i class="bx bx-building me-1"></i>Mon Entreprise
                    </button>
                </div>

                <!-- Form 1: MON ESPACE COMPTABLE -->
                <form method="POST" action="{{ route('login.post') }}" id="formAuthenticationEspace">
                    @csrf
                    <div class="mb-4">
                        <label for="email_espace" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_espace" name="email_adresse"
                            placeholder="vous@entreprise.com" autofocus autocomplete="username" value="{{ old('email_adresse') }}" required />
                        @error('email_adresse')
                            <div class="text-danger small mt-1" style="font-size: 0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-password-toggle">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0" for="password_espace">Mot de passe</label>
                        </div>
                        <div class="input-group input-group-merge">
                            <input type="password" id="password_espace" class="form-control" name="password"
                                placeholder="*********" autocomplete="current-password" required />
                            <button type="button" class="input-group-text btn border border-start-0" onclick="togglePasswordVisibility('password_espace', this)">
                                <i class="bx bx-hide"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1" style="font-size: 0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember" id="remember_me">
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="#" class="link-forgot">Mot de passe oublié ?</a>
                    </div>

                    <div class="mb-3">
                        <button class="btn-submit-premium" type="submit">Se connecter</button>
                    </div>

                    <!-- OU Divider -->
                    <div class="auth-divider">
                        <hr>
                        <span>ou</span>
                        <hr>
                    </div>

                    <!-- Google Button -->
                    <a href="{{ route('auth.google') }}" class="btn-google-premium">
                        <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        </svg>
                        Se connecter avec Google
                    </a>
                </form>

                <!-- Form 2: MON ENTREPRISE -->
                <form method="POST" action="{{ route('login.company') }}" id="formAuthenticationEntreprise" style="display: none;">
                    @csrf
                    <div class="mb-4">
                        <label for="email_entreprise" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email_entreprise" name="email_adresse"
                            placeholder="vous@entreprise.com" autocomplete="username" value="{{ old('email_adresse') }}" required />
                        @error('email_adresse')
                            <div class="text-danger small mt-1" style="font-size: 0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="company_code" class="form-label">Code unique de l'entreprise</label>
                        <input type="text" class="form-control" id="company_code" name="company_code"
                            placeholder="Ex: ENT-XYZ-1234" value="{{ old('company_code') }}" required />
                        @error('company_code')
                            <div class="text-danger small mt-1" style="font-size: 0.78rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <button class="btn-submit-premium-green" type="submit">Accéder à la comptabilité</button>
                    </div>
                </form>

                <!-- Footer elements -->
                <p class="auth-footer-text">
                    Pas encore de compte ? <a href="{{ route('landing.pricing') }}">S'inscrire gratuitement</a>
                </p>

                <a href="#" class="customer-support">Service client & Contact</a>

                <div class="legal-notice">
                    En vous connectant, vous acceptez nos <a href="#" class="text-secondary text-decoration-underline">Conditions</a> et notre <a href="#" class="text-secondary text-decoration-underline">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Fix Back Button Navigation -->
    <script>
        if (window.history && window.history.pushState) {
            window.history.replaceState('landing', null, "{{ url('/') }}");
            window.history.pushState('login', null, window.location.href);

            window.addEventListener('popstate', function (e) {
                if (e.state === 'landing') {
                    window.location.href = "{{ url('/') }}";
                }
            });
        }

        // Toggle password visibility helper
        function togglePasswordVisibility(fieldId, btnEl) {
            const input = document.getElementById(fieldId);
            const icon = btnEl.querySelector('i');
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'bx bx-show';
                } else {
                    input.type = 'password';
                    icon.className = 'bx bx-hide';
                }
            }
        }

        // Tab switcher (keeps original controller functionality)
        function switchLoginTab(type) {
            const btnEspace = document.getElementById('btnTabEspace');
            const btnEntreprise = document.getElementById('btnTabEntreprise');
            const formEspace = document.getElementById('formAuthenticationEspace');
            const formEntreprise = document.getElementById('formAuthenticationEntreprise');

            if (type === 'espace') {
                btnEspace.classList.add('active');
                btnEntreprise.classList.remove('active');
                formEspace.style.display = 'block';
                formEntreprise.style.display = 'none';
            } else {
                btnEntreprise.classList.add('active');
                btnEspace.classList.remove('active');
                formEspace.style.display = 'none';
                formEntreprise.style.display = 'block';
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            @if($errors->has('company_code') || session('active_tab') == 'entreprise')
                switchLoginTab('entreprise');
            @else
                switchLoginTab('espace');
            @endif
        });
    </script>
</body>

</html>