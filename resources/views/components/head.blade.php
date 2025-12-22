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

    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

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
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    
    <!-- Configuration Tailwind pour le nouveau design -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif']
                    },
                    colors: {
                        'primary': '#1e40af',
                        'primary-dark': '#1e3a8a',
                        'primary-light': '#3b82f6'
                    }
                }
            }
        }
    </script>

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

        /* CORRECTION GLOBALE DES CARTES DEFORMEES */
        .content-wrapper .bg-white,
        .content-wrapper .card,
        .content-wrapper [class*="bg-white"] {
            border-radius: 0.75rem !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24) !important;
            border: 1px solid #e5e7eb !important;
            overflow: hidden !important;
            transition: all 0.3s ease !important;
            background: white !important;
        }

        .content-wrapper .bg-white:hover,
        .content-wrapper .card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06) !important;
            transform: translateY(-1px) !important;
        }

        .content-wrapper .w-12,
        .content-wrapper [class*="w-12"] {
            width: 3rem !important;
            height: 3rem !important;
            border-radius: 0.5rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
        }

        .content-wrapper .badge {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.5rem !important;
            border-radius: 0.25rem !important;
            font-weight: 500 !important;
            white-space: nowrap !important;
        }

        .content-wrapper h3 {
            font-size: 2rem !important;
            font-weight: 700 !important;
            line-height: 1.2 !important;
            margin: 0 !important;
            color: #111827 !important;
        }

        .content-wrapper h6 {
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #6b7280 !important;
            margin: 0 0 0.5rem 0 !important;
        }

        .content-wrapper h5 {
            font-size: 1.125rem !important;
            font-weight: 600 !important;
            color: #111827 !important;
            margin: 0 !important;
        }

        .content-wrapper .d-flex {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .content-wrapper .row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-right: -1rem !important;
            margin-left: -1rem !important;
        }

        .content-wrapper .col-md-3,
        .content-wrapper .col-md-6 {
            position: relative !important;
            width: 100% !important;
            padding-right: 1rem !important;
            padding-left: 1rem !important;
        }

        @media (min-width: 768px) {
            .content-wrapper .col-md-3 {
                flex: 0 0 25% !important;
                max-width: 25% !important;
            }
            .content-wrapper .col-md-6 {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
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
    </style>
</head>
