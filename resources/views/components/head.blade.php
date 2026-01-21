<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Flow Compta</title>
    <meta name="description" content="" />

    <!-- Font Awesome pour le nouveau design -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Police Inter pour le nouveau design -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap (conservé pour compatibilité) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css" rel="stylesheet" />

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap"
        rel="stylesheet" />
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css">

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/premium_admin.css') }}" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Core JS (Moved to head for guaranteed availability) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    @vite(['resources/css/app.css'])

    <style>
        /* Styles globaux pour le nouveau design */
        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
        }

        /* Masquer la barre de défilement */
        ::-webkit-scrollbar {
            display: none;
        }

        /* Bouton principal (le select visible) */
        .bootstrap-select .dropdown-toggle {
            background-color: rgba(255, 255, 255, 0.85) !important;
            color: #000 !important;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            min-height: 40px;
            box-shadow: none !important;
            transition: background-color 0.2s ease;
        }

        .bootstrap-select .dropdown-toggle:hover,
        .bootstrap-select .dropdown-toggle:focus {
            background-color: rgba(255, 255, 255, 0.95) !important;
            border-color: #adb5bd;
        }

        /* Menu déroulant */
        .bootstrap-select .dropdown-menu {
            font-size: 0.9rem;
            background-color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Options du menu */
        .bootstrap-select .dropdown-menu li a {
            padding: 12px 16px;
            color: #212529;
            transition: background-color 0.2s ease;
        }

        /* Option survolée */
        .bootstrap-select .dropdown-menu li a:hover {
            background-color: #f8f9fa;
            color: #000;
        }

        /* Option sélectionnée */
        .bootstrap-select .dropdown-menu li.active a {
            background-color: #007bff !important;
            color: #fff !important;
        }

        /* Amélioration du curseur */
        .bootstrap-select .dropdown-menu li a {
            cursor: pointer;
        }

        /* Forcer le bouton visible à s'adapter au contenu */
        .bootstrap-select[data-width="auto"] .dropdown-toggle {
            width: auto !important;
            min-width: 100px;
            white-space: nowrap;
        }

        /* CORRECTION GLOBALE DES CARTES (UNIQUEMENT POUR LE CONTENU ANCIEN) */
        .content-wrapper:not(.premium-mode) .bg-white,
        .content-wrapper:not(.premium-mode) .card,
        .content-wrapper:not(.premium-mode) [class*="bg-white"] {
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }

        /* Typography spacing (non-premium only) */
        .content-wrapper:not(.premium-mode) h3 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
            color: #111827;
        }

        .content-wrapper:not(.premium-mode) h6 {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            margin: 0 0 0.5rem 0;
        }

        /* FORCER LES COULEURS DE FOND */
        .bg-blue-100 { background-color: #dbeafe !important; }
        .bg-purple-100 { background-color: #f3e8ff !important; }
        .bg-green-100 { background-color: #d1fae5 !important; }
        .bg-orange-100 { background-color: #fed7aa !important; }

        .text-primary { color: #3b82f6 !important; }
        .text-purple-600 { color: #9333ea !important; }
        .text-success { color: #10b981 !important; }
        .text-orange-600 { color: #ea580c !important; }

        /* Stabilisation du layout */
        .layout-page, .content-wrapper {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        /* Skeleton loading placeholder color */
        .glass-card:empty {
            min-height: 200px;
            background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }

        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
