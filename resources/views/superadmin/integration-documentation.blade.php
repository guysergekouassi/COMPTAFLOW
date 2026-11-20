<!doctype html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<style>
    .integration-doc-card{height:100%;border:1px solid #e2e8f0;border-radius:14px;transition:box-shadow .2s ease,transform .2s ease}
    .integration-doc-card:hover{box-shadow:0 12px 28px rgba(15,23,42,.08);transform:translateY(-2px)}
    .integration-doc-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#eef2ff;color:#4338ca;font-size:20px}
</style>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.header', ['page_title' => 'Documentation des intégrations'])
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h3 class="fw-bold mb-1"><i class="fa-solid fa-book-open text-primary me-2"></i>Documentation des intégrations</h3>
                            <p class="text-muted mb-0">Cahiers des charges officiels pour connecter les applications maison et tierces à COMPTAFLOW.</p>
                        </div>
                        <a href="{{ route('superadmin.liaison_compta.maison') }}" class="btn btn-outline-primary">
                            <i class="fa-solid fa-diagram-project me-1"></i> Gérer les liaisons
                        </a>
                    </div>

                    <div class="alert alert-info">
                        <strong>À respecter :</strong> les applications doivent suivre les endpoints, les formats JSON, les règles de sécurité et les tests décrits dans leur cahier des charges. Une route mentionnée comme « à développer/valider » n'est pas considérée comme opérationnelle avant ses tests.
                    </div>

                    <div class="row g-4">
                        @foreach($documents as $document)
                        <div class="col-12 col-xl-4">
                            <div class="card integration-doc-card">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="integration-doc-icon"><i class="fa-solid fa-file-lines"></i></div>
                                        <span class="badge bg-label-primary">Cahier des charges</span>
                                    </div>
                                    <h5 class="fw-bold">{{ $document['title'] }}</h5>
                                    <p class="text-muted flex-grow-1">{{ $document['description'] }}</p>
                                    @if($document['available'])
                                        <small class="text-muted mb-3">Fichier présent sur le serveur · {{ $document['updated_at'] }}</small>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('superadmin.integration-docs.show', $document['slug']) }}" class="btn btn-primary">
                                                <i class="fa-solid fa-eye me-1"></i> Consulter
                                            </a>
                                            <a href="{{ route('superadmin.integration-docs.download', $document['slug']) }}" class="btn btn-outline-secondary">
                                                <i class="fa-solid fa-download me-1"></i> Télécharger
                                            </a>
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-0">Le fichier n'est pas encore présent sur ce déploiement. Déployez la dernière version du dépôt.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
