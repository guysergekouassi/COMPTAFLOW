<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Flow Compta - L'élégance de la comptabilité moderne</title>

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

    <!-- Bootstrap CSS is included in core.css -->

    <style>
        body {
            background-color: #f8fafc;
            overflow-x: hidden;
            font-family: "Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
        }

        /* Glassmorphism Navbar */
        .glass-nav {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
            transition: all 0.3s ease;
        }

        .hero-section {
            padding-top: 150px;
            padding-bottom: 100px;
            position: relative;
        }

        /* Blobs */
        .bg-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            z-index: -1;
            animation: floatBlob 8s infinite alternate ease-in-out;
        }
        .blob-1 {
            width: 400px; height: 400px;
            background: #696cff;
            top: -10%; left: -5%;
        }
        .blob-2 {
            width: 350px; height: 350px;
            background: #e83e8c;
            top: 20%; right: -5%;
            animation-delay: 2s;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 30px) scale(1.1); }
            100% { transform: translate(-30px, -20px) scale(0.9); }
        }

        .hero-gradient-text {
            background: linear-gradient(135deg, #1e3a8a 0%, #696cff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 900;
        }

        .btn-glow {
            box-shadow: 0 10px 25px -5px rgba(105, 108, 255, 0.5);
            transition: all 0.3s ease;
        }
        .btn-glow:hover {
            box-shadow: 0 15px 35px -5px rgba(105, 108, 255, 0.6);
            transform: translateY(-2px);
        }

        .dashboard-preview-container {
            border-radius: 20px;
            padding: 8px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.8);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            margin-top: 60px;
            animation: floatUp 6s ease-in-out infinite;
        }

        .dashboard-preview-img {
            border-radius: 12px;
            border: 1px solid #eee;
            width: 100%;
            display: block;
        }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .nav-link-custom {
            color: #566a7f;
            font-weight: 600;
            padding: 0.5rem 1rem;
            transition: 0.2s;
        }
        .nav-link-custom:hover {
            color: #696cff;
        }
    </style>
</head>
<body>

    <!-- Background Blobs -->
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top glass-nav py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <span class="avatar avatar-sm bg-primary rounded d-flex align-items-center justify-content-center text-white" style="width:40px;height:40px;">
                    <i class="fa-solid fa-bolt fs-5"></i>
                </span>
                <span class="fw-bolder fs-4 text-dark ms-2">Flow Compta</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link nav-link-custom" href="{{ route('login') }}">Connexion</a>
                    </li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <a class="btn btn-dark rounded-pill px-4" href="{{ route('landing.pricing') }}">S'inscrire</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white border text-primary fw-bold mb-4 shadow-sm" style="font-size: 0.85rem;">
                <span class="badge bg-primary rounded-circle p-1"></span>
                La nouvelle norme comptable en Côte d'Ivoire
            </div>

            <h1 class="display-3 fw-bolder text-dark mb-4">
                Pilotez votre finance avec <br/> <span class="hero-gradient-text">intelligence.</span>
            </h1>

            <p class="lead text-muted mb-5 mx-auto" style="max-width: 600px;">
                Flow Compta réinvente la gestion comptable pour les cabinets et les entreprises. Centralisez, automatisez et analysez vos données en un clic avec l'IA.
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('landing.pricing') }}" class="btn btn-primary btn-lg rounded-pill btn-glow px-4">
                    Démarrer gratuitement <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
                <a href="{{ route('login') }}" class="btn btn-label-secondary btn-lg rounded-pill px-4">
                    Mon espace <i class="fa-solid fa-lock ms-2"></i>
                </a>
            </div>

            <!-- Preview Dashboard -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="dashboard-preview-container">
                        <img src="{{ asset('rapport_img_dashboard.png') }}" alt="Tableau de bord" class="dashboard-preview-img shadow-lg" />
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-white border-top py-4 mt-5">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-2 mb-md-0 text-muted">
                © 2026 Flow Compta by DCKnowing. Tous droits réservés.
            </div>
            <div class="text-muted">
                Support: 07 67 13 19 93 | <a href="mailto:it.dcknowing@gmail.com" class="text-primary">it.dcknowing@gmail.com</a>
            </div>
        </div>
    </footer>

    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
</body>
</html>
