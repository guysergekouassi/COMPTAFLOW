<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('components.header')

                <!-- / Navbar -->

                <!-- Content wrapper -->

                {{-- Bouton Modifier (dans l'entête de la carte) --}}


                <div class="content-wrapper">

                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row g-6 mb-6">

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Fermer"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Fermer"></button>
                                </div>
                            @endif
                            <div class="card mb-6">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">Informations sur l'entreprise</h5>
                                        <small class="text-muted">Détails enregistrés dans le système</small>
                                    </div>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#editCompanyModal">
                                        <i class="fas fa-edit me-1"></i> Modifier
                                    </button>
                                </div>
                                <div class="card-body pt-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Nom de l'entreprise</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->company_name ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Forme Juridique</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->juridique_form ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Activité</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->activity ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Capital Social</label>
                                            <input type="text" class="form-control"
                                                value="{{ number_format($company->social_capital, 2, ',', ' ') }}"
                                                disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Adresse</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->adresse ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Code Postal</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->code_postal ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Ville</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->city ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Pays</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->country ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Téléphone</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->phone_number ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Adresse e-mail</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->email_adresse ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Identification TVA</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->identification_TVA ?? 'N/A' }}" disabled>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Statut de l'entreprise</label>
                                            <input type="text" class="form-control"
                                                value="{{ $company->is_blocked ? 'Bloquée' : 'Active' }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Modal d'édition -->
                            <div class="modal fade" id="editCompanyModal" tabindex="-1"
                                aria-labelledby="editCompanyModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <form method="POST"
                                        action="{{ route('compagny_information.update', $company->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header text-white">
                                                <h5 class="modal-title" id="editCompanyModalLabel">Modifier les
                                                    informations de l'entreprise</h5>
                                                <button type="button" class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    @php
                                                        $fields = [
                                                            ['company_name', "Nom de l'entreprise"],
                                                            ['juridique_form', 'Forme Juridique'],
                                                            ['activity', 'Activité'],
                                                            ['social_capital', 'Capital Social'],
                                                            ['adresse', 'Adresse'],
                                                            ['code_postal', 'Code Postal'],
                                                            ['city', 'Ville'],
                                                            ['country', 'Pays'],
                                                            ['phone_number', 'Téléphone'],
                                                            ['email_adresse', 'Adresse e-mail'],
                                                            ['identification_TVA', 'Identification TVA'],
                                                        ];
                                                    @endphp

                                                    @foreach ($fields as [$field, $label])
                                                        <div class="col-md-6">
                                                            <label class="form-label">{{ $label }}</label>
                                                            <input
                                                                type="{{ $field === 'email_adresse' ? 'email' : 'text' }}"
                                                                name="{{ $field }}" class="form-control"
                                                                value="{{ old($field, $company->$field) }}">
                                                        </div>
                                                    @endforeach


                                                </div>
                                            </div>
                                            <div class="modal-footer justify-content-between">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-success">Enregistrer les
                                                    modifications</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>




                        </div>
                    </div>
                    <!-- Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <!-- Overlay -->
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
        <!-- / Layout wrapper -->

        <!-- Core JS -->

        @include('components.footer')
</body>

</html>
