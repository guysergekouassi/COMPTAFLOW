<!doctype html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.header', ['page_title' => 'Contrats de payload COMPTAFLOW'])
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h3>Contrats d'intégration COMPTAFLOW et gouvernance des applications</h3>
                    <p class="text-muted">Formats à respecter par SELFLOW, RH-FLOW et toute application externe.</p>
                    <div class="card mb-3"><div class="card-body">
                        <h5>1. Référentiel</h5>
                        <p>Plan comptable, codes journaux et tiers.</p>
                        <a class="btn btn-primary" href="{{ route('superadmin.payloads.download', 'referentiel') }}">Télécharger le TXT</a>
                    </div></div>
                    <div class="card mb-3"><div class="card-body">
                        <h5>2. Écritures comptables</h5>
                        <p>Payload JSON du endpoint et rappel du fichier historique à neuf colonnes.</p>
                        <a class="btn btn-primary" href="{{ route('superadmin.payloads.download', 'ecritures') }}">Télécharger le TXT</a>
                    </div></div>
                    <div class="card mb-3"><div class="card-body">
                        <h5>3. Pièces justificatives</h5>
                        <p>Endpoint de dépôt PDF disponible ; l'envoi automatique depuis SELFLOW reste à intégrer.</p>
                        <a class="btn btn-primary" href="{{ route('superadmin.payloads.download', 'pieces') }}">Télécharger le TXT</a>
                    </div></div>
                    <div class="card mb-3"><div class="card-body">
                        <h5>4. Liaison COMPTA-APK</h5>
                        <p>Gouvernance des applications maison et externes, rattachement des dossiers, permissions et sécurité.</p>
                        <a class="btn btn-primary" href="{{ route('superadmin.payloads.download', 'liaison') }}">Télécharger le TXT</a>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
