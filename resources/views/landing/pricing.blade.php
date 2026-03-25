<!DOCTYPE html>
<html lang="fr" class="light-style layout-menu-fixed">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Flow Compta - Choisissez votre pack</title>

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
            filter: blur(80px);
            opacity: 0.2;
            z-index: -1;
        }
        .blob-1 {
            width: 400px; height: 400px;
            background: #696cff;
            top: -10%; left: -5%;
        }
        .blob-2 {
            width: 350px; height: 350px;
            background: #e83e8c;
            bottom: -10%; right: -5%;
        }

        .pricing-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-radius: 20px;
        }
        .pricing-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(105, 108, 255, 0.2);
        }

        .pricing-card-premium {
            border: 2px solid #696cff;
            position: relative;
        }

        .popular-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: linear-gradient(135deg, #696cff 0%, #e83e8c 100%);
            color: white;
            padding: 5px 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom-left-radius: 15px;
            border-top-right-radius: 18px;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .icon-standard {
            background-color: #f0f8ff;
            color: #696cff;
            border: 1px solid #e0eaff;
        }
        .icon-premium {
            background: linear-gradient(135deg, #696cff 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(105, 108, 255, 0.4);
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }
        .feature-list li {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #566a7f;
            font-weight: 500;
        }
        .feature-list li i {
            margin-top: 4px;
        }

        .price-text {
            font-size: 2.5rem;
            font-weight: 900;
            color: #32475c;
            border-bottom: 1px solid #ebeef0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>

    <div class="container" style="max-width: 1000px;">
        
        <div class="text-center mb-5">
            <a href="{{ route('landing.index') }}" class="btn btn-label-secondary btn-sm mb-4 rounded-pill">
                <i class="fa-solid fa-arrow-left me-2"></i> Retour à l'accueil
            </a>
            <h1 class="display-5 fw-bolder text-dark mb-3">Une solution, <span class="text-primary">deux profils.</span></h1>
            <p class="lead text-muted mx-auto" style="max-width: 600px;">Choisissez le pack qui correspond parfaitement à la nature de votre activité.</p>
        </div>

        <div class="row g-4 px-3 px-md-0">
            
            <!-- Pack Entreprise (Premium/Recommended) -->
            <div class="col-md-6 d-flex">
                <div class="pricing-card pricing-card-premium p-4 p-md-5 w-100 d-flex flex-column">
                    <div class="popular-badge">Recommandé</div>
                    
                    <div class="icon-box icon-premium">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    
                    <h2 class="h3 fw-bold text-dark mb-2">Pack Entreprise</h2>
                    <p class="text-muted mb-4 small">La solution complète pour gérer toutes vos entreprises avec un contrôle total, sans aucune limite logicielle.</p>
                    
                    <div class="price-text text-primary">Complet</div>

                    <ul class="feature-list mb-5">
                        <li><i class="fa-solid fa-check text-primary"></i> Multi-entreprises et multi-dossiers centralisés</li>
                        <li><i class="fa-solid fa-check text-primary"></i> Accès à 100% des fonctionnalités avancées</li>
                        <li><i class="fa-solid fa-check text-primary"></i> Utilisateurs et collaborateurs illimités</li>
                        <li><i class="fa-solid fa-check text-primary"></i> Automatisation IA, Edition et Reporting de pointe</li>
                    </ul>

                    <a href="{{ route('landing.register_form', 'entreprise') }}" class="btn btn-primary btn-lg rounded-pill w-100 mt-auto shadow-sm">
                        S'inscrire comme Entreprise <i class="fa-solid fa-arrow-right ms-2 opacity-75"></i>
                    </a>
                </div>
            </div>

            <!-- Pack Comptable (Standard) -->
            <div class="col-md-6 d-flex">
                <div class="pricing-card p-4 p-md-5 w-100 d-flex flex-column">
                    <div class="icon-box icon-standard">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    
                    <h2 class="h3 fw-bold text-dark mb-2">Pack Comptable</h2>
                    <p class="text-muted mb-4 small">Idéal pour la gestion centralisée d'une seule entreprise ou entité avec des options de base.</p>
                    
                    <div class="price-text">Standard</div>

                    <ul class="feature-list mb-5">
                        <li><i class="fa-solid fa-check text-success"></i> Limité à une seule et unique entreprise</li>
                        <li><i class="fa-solid fa-check text-success"></i> Outils de saisie comptable et lettrage</li>
                        <li><i class="fa-solid fa-check text-success"></i> Suivi financier essentiel</li>
                    </ul>

                    <a href="{{ route('landing.register_form', 'comptable') }}" class="btn btn-dark btn-lg rounded-pill w-100 mt-auto shadow-sm">
                        Sélectionner ce pack <i class="fa-solid fa-arrow-right ms-2 opacity-75"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
