<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Résultat <span class="text-gradient">Analytique</span>'])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y text-center">
                        <div class="glass-card p-12 mt-12">
                            <i class="fa-solid fa-scale-balanced text-blue-100 text-9xl mb-6"></i>
                            <h2 class="text-2xl font-bold text-slate-800">Module de Reporting en cours</h2>
                            <p class="text-slate-500 max-w-md mx-auto mt-4">
                                Cette interface sera disponible lors de l'Étape 4.
                            </p>
                            <a href="{{ route('analytique.axes.index') }}" class="btn btn-primary mt-6 rounded-pill">Retour à la gestion</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('components.footer')
</body>
</html>
