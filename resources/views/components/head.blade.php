<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Flow Compta</title>
    <meta name="description" content="" />

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
    

    <style>
       <style>
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

        /* Forcer le bouton visible à s’adapter au contenu */
        .bootstrap-select[data-width="auto"] .dropdown-toggle {
            width: auto !important;
            min-width: 100px;
            white-space: nowrap;
        }
    </style>
    </style>
</head>
