<!doctype html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<style>
    .markdown-source{white-space:pre-wrap;overflow-wrap:anywhere;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;color:#243247;font:13px/1.7 ui-monospace,SFMono-Regular,Menlo,monospace}
</style>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')
        <div class="layout-page">
            @include('components.header', ['page_title' => $document['title']])
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <a href="{{ route('superadmin.integration-docs.index') }}" class="text-muted"><i class="fa-solid fa-arrow-left me-1"></i> Documentation des intégrations</a>
                            <h3 class="fw-bold mt-2 mb-1">{{ $document['title'] }}</h3>
                            <p class="text-muted mb-0">{{ $document['description'] }}</p>
                        </div>
                        <a href="{{ route('superadmin.integration-docs.download', $slug) }}" class="btn btn-primary"><i class="fa-solid fa-download me-1"></i> Télécharger le Markdown</a>
                    </div>
                    <pre class="markdown-source">{{ $content }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
