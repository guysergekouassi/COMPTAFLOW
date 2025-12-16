<!DOCTYPE html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            {{-- La sidebar est incluse --}}
            @include('components.sidebar', ['habilitations' => []])
            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">Paramétrage /</span> Gestion des Comptes Comptabilité
                        </h4>

                        <div class="row g-6 mb-6">
                            <div class="col-sm-6 col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Total Comptes Comptabilité</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ number_format($totalAccounts ?? 0) }}</h4>
                                                </div>
                                                <small class="mb-0">Total des entités gérées</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="icon-base bx bx-building-house icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Comptes Actifs</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ number_format($activeAccounts ?? 0) }}</h4>
                                                </div>
                                                <small class="mb-0">Actuellement utilisés</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-success">
                                                    <i class="icon-base bx bx-check-circle icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Comptes Inactifs</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ number_format($inactiveAccounts ?? 0) }}</h4>
                                                </div>
                                                <small class="mb-0">Désactivés ou en attente</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-warning">
                                                    <i class="icon-base bx bx-power-off icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Message de succès/erreur --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                <strong>Erreur de Validation:</strong> Veuillez vérifier les champs du formulaire.
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif


                        <div class="card">
                                {{-- MODIFICATION : Remplacement du h5.card-header par une div pour inclure le bouton --}}
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Liste des Comptes Comptabilités</h5>
                                            {{-- BOUTON CRÉER --}}
                                          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#modalCreateComptaAccount">
                                            <i class="bx bx-plus me-1"></i> Créer un Compte
                                        </button>
                                            {{-- FIN BOUTON CRÉER --}}
                                        </div>
                                <div class="table-responsive text-nowrap">

                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nom de l'Entreprise</th>
                                                <th>Forme Juridique</th>
                                                <th>Activité</th>
                                                <th>Ville</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            {{-- Parcourir la liste des comptes --}}
                                            @forelse ($comptaAccounts as $comptaAccount)
                                                {{-- Rendre la ligne cliquable. La route 'compta_accounts.access' est à définir --}}
                                                <tr class="clickable-row"
                                                    data-href="{{ route('compta_accounts.access', ['companyId' => $comptaAccount->id]) }}"
                                                    style="cursor: pointer;"
                                                >
                                                    {{-- Suppression des onclick redondants dans les TD, la gestion est faite par JS --}}
                                                    <td>
                                                        <i class="bx bx-buildings me-2"></i>
                                                        <strong>{{ $comptaAccount->company_name }}</strong>
                                                    </td>
                                                    <td>
                                                        {{ $comptaAccount->juridique_form ?? 'N/A' }}
                                                    </td>
                                                    <td>
                                                        {{ $comptaAccount->activity ?? 'N/A' }}
                                                    </td>
                                                    <td>
                                                        {{ $comptaAccount->city ?? 'N/A' }}
                                                    </td>
                                                    <td>
                                                        @if ($comptaAccount->is_active)
                                                            <span class="badge bg-label-success me-1">Actif</span>
                                                        @else
                                                            <span class="badge bg-label-danger me-1">Inactif</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                {{-- Correction: Les data-bs-target pointent vers le bon ID de modal --}}
                                                                <a class="dropdown-item details-btn" href="javascript:void(0);"
                                                                    data-bs-toggle="modal" data-bs-target="#modalSeeComptaAccount"
                                                                    data-company-name="{{ $comptaAccount->company_name }}"
                                                                    data-activity="{{ $comptaAccount->activity }}"
                                                                    data-juridique-form="{{ $comptaAccount->juridique_form }}"
                                                                    data-social-capital="{{ $comptaAccount->social_capital }}"
                                                                    data-adresse="{{ $comptaAccount->adresse }}"
                                                                    data-code-postal="{{ $comptaAccount->code_postal }}"
                                                                    data-city="{{ $comptaAccount->city }}"
                                                                    data-country="{{ $comptaAccount->country }}"
                                                                    data-phone-number="{{ $comptaAccount->phone_number }}"
                                                                    data-email-adresse="{{ $comptaAccount->email_adresse }}"
                                                                    data-identification-tva="{{ $comptaAccount->identification_TVA }}"
                                                                    data-is-active="{{ $comptaAccount->is_active }}">
                                                                    <i class="bx bx-show me-1"></i> Détails
                                                                </a>
                                                                <a class="dropdown-item edit-btn" href="javascript:void(0);"
                                                                    data-bs-toggle="modal" data-bs-target="#modalUpdateComptaAccount"
                                                                    data-account-id="{{ $comptaAccount->id }}"
                                                                    data-company-name="{{ $comptaAccount->company_name }}"
                                                                    data-activity="{{ $comptaAccount->activity }}"
                                                                    data-juridique-form="{{ $comptaAccount->juridique_form }}"
                                                                    data-social-capital="{{ $comptaAccount->social_capital }}"
                                                                    data-adresse="{{ $comptaAccount->adresse }}"
                                                                    data-code-postal="{{ $comptaAccount->code_postal }}"
                                                                    data-city="{{ $comptaAccount->city }}"
                                                                    data-country="{{ $comptaAccount->country }}"
                                                                    data-phone-number="{{ $comptaAccount->phone_number }}"
                                                                    data-email-adresse="{{ $comptaAccount->email_adresse }}"
                                                                    data-identification-tva="{{ $comptaAccount->identification_TVA }}"
                                                                    data-is-active="{{ $comptaAccount->is_active }}">
                                                                    <i class="bx bx-edit-alt me-1"></i> Éditer
                                                                </a>
                                                                <a class="dropdown-item delete-btn" href="javascript:void(0);"
                                                                    data-bs-toggle="modal" data-bs-target="#deleteAccountModal"
                                                                    data-account-id="{{ $comptaAccount->id }}"
                                                                    data-company-name="{{ $comptaAccount->company_name }}">
                                                                    <i class="bx bx-trash me-1"></i> Supprimer
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">Aucun compte comptabilité trouvé.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <div class="modal fade" id="modalCreateComptaAccount" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Créer un nouveau Compte Comptabilité</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="createAccountForm" method="POST" action="{{ route('compta_accounts.store') }}">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="company_name" class="form-label">Nom de la Société <span class="text-danger">*</span></label>
                                                    <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name') }}" required />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="activity" class="form-label">Secteur d'activité</label>
                                                    <input type="text" id="activity" name="activity" class="form-control" value="{{ old('activity') }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="juridique_form" class="form-label">Forme Juridique</label>
                                                    <input type="text" id="juridique_form" name="juridique_form" class="form-control" value="{{ old('juridique_form') }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="social_capital" class="form-label">Capital Social</label>
                                                    <input type="number" step="0.01" id="social_capital" name="social_capital" class="form-control" value="{{ old('social_capital') }}" />
                                                </div>
                                                <div class="col-12">
                                                    <label for="adresse" class="form-label">Adresse Complète</label>
                                                    <input type="text" id="adresse" name="adresse" class="form-control" value="{{ old('adresse') }}" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="code_postal" class="form-label">Code Postal</label>
                                                    <input type="text" id="code_postal" name="code_postal" class="form-control" value="{{ old('code_postal') }}" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="city" class="form-label">Ville</label>
                                                    <input type="text" id="city" name="city" class="form-control" value="{{ old('city') }}" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="country" class="form-label">Pays</label>
                                                    <input type="text" id="country" name="country" class="form-control" value="{{ old('country') }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="email_adresse_create" class="form-label">Email de contact <span class="text-danger">*</span></label>
                                                    <input type="email" id="email_adresse_create" name="email_adresse" class="form-control" value="{{ old('email_adresse') }}" required />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="phone_number" class="form-label">Numéro de Téléphone</label>
                                                    <input type="text" id="phone_number" name="phone_number" class="form-control" value="{{ old('phone_number') }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="identification_TVA" class="form-label">Identification TVA</label>
                                                    <input type="text" id="identification_TVA" name="identification_TVA" class="form-control" value="{{ old('identification_TVA') }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="is_active_create" class="form-label">Statut</label>
                                                    <select id="is_active_create" name="is_active" class="form-select">
                                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Actif</option>
                                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="modal-footer justify-content-end mt-4">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">
                                                    Enregistrer le Compte
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="modal fade" id="modalUpdateComptaAccount" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Modifier le Compte Comptabilité</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{-- L'action sera mise à jour par JS pour inclure l'ID --}}
                                        <form id="updateAccountForm" method="POST" action="">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="id" id="updateAccountId" />

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="update_company_name" class="form-label">Nom de la Société <span class="text-danger">*</span></label>
                                                    <input type="text" id="update_company_name" name="company_name" class="form-control" required />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="update_activity" class="form-label">Secteur d'activité</label>
                                                    <input type="text" id="update_activity" name="activity" class="form-control" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="update_juridique_form" class="form-label">Forme Juridique</label>
                                                    <input type="text" id="update_juridique_form" name="juridique_form" class="form-control" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="update_social_capital" class="form-label">Capital Social</label>
                                                    <input type="number" step="0.01" id="update_social_capital" name="social_capital" class="form-control" />
                                                </div>
                                                <div class="col-12">
                                                    <label for="update_adresse" class="form-label">Adresse Complète</label>
                                                    <input type="text" id="update_adresse" name="adresse" class="form-control" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="update_code_postal" class="form-label">Code Postal</label>
                                                    <input type="text" id="update_code_postal" name="code_postal" class="form-control" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="update_city" class="form-label">Ville</label>
                                                    <input type="text" id="update_city" name="city" class="form-control" />
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="update_country" class="form-label">Pays</label>
                                                    <input type="text" id="update_country" name="country" class="form-control" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="update_email_adresse" class="form-label">Email de contact <span class="text-danger">*</span></label>
                                                    <input type="email" id="update_email_adresse" name="email_adresse" class="form-control" required />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="update_phone_number" class="form-label">Numéro de Téléphone</label>
                                                    <input type="text" id="update_phone_number" name="phone_number" class="form-control" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="update_identification_TVA" class="form-label">Identification TVA</label>
                                                    <input type="text" id="update_identification_TVA" name="identification_TVA" class="form-control" />
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="update_is_active" class="form-label">Statut</label>
                                                    <select id="update_is_active" name="is_active" class="form-select">
                                                        <option value="1">Actif</option>
                                                        <option value="0">Inactif</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="modal-footer justify-content-end mt-4">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">Fermer</button>
                                                <button type="submit" class="btn btn-primary">
                                                    Enregistrer les modifications
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modalSeeComptaAccount" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="seeAccountTitle">Détails du Compte</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <h6 class="border-bottom pb-2 mb-3 text-primary">Informations Générales</h6>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nom de la Société</label>
                                                <input type="text" id="see_company_name" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Secteur d'activité</label>
                                                <input type="text" id="see_activity" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Forme Juridique</label>
                                                <input type="text" id="see_juridique_form" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Capital Social</label>
                                                <input type="text" id="see_social_capital" class="form-control" readonly />
                                            </div>

                                            <div class="col-12 mt-4">
                                                <h6 class="border-bottom pb-2 mb-3 text-primary">Coordonnées</h6>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Adresse</label>
                                                <input type="text" id="see_adresse" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Code Postal</label>
                                                <input type="text" id="see_code_postal" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Ville</label>
                                                <input type="text" id="see_city" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Pays</label>
                                                <input type="text" id="see_country" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email de contact</label>
                                                <input type="email" id="see_email_adresse" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Numéro de Téléphone</label>
                                                <input type="text" id="see_phone_number" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Identification TVA</label>
                                                <input type="text" id="see_identification_TVA" class="form-control" readonly />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Statut</label>
                                                <input type="text" id="see_is_active" class="form-control" readonly />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer justify-content-end">
                                        <button type="button" class="btn btn-label-secondary"
                                            data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="deleteAccountModal" tabindex="-1"
                            aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content border-0 shadow">
                                    {{-- L'action sera mise à jour par JS pour inclure l'ID --}}
                                    <form id="deleteAccountForm" method="POST" action="">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header text-dark justify-content-center">
                                            <h5 class="modal-title" id="deleteAccountModalLabel">
                                                <i class="bx bx-error-circle me-2"></i>Confirmer la suppression
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <p class="mb-0">
                                                Êtes-vous sûr de vouloir supprimer le compte
                                                <strong><span id="accountToDeleteName" class="text-danger"></span></strong> ?
                                                Cette action est <strong>irréversible</strong>.
                                            </p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                Supprimer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    @include('components.footer')

    {{-- Script JS pour la gestion des modales et des actions --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Définition des URL de base pour la modification et la suppression
            // Ces URLs utilisent le paramètre de route {id} qui sera remplacé
            const accountsUpdateBaseUrl = "{{ route('compta_accounts.update', ['id' => '__ID__']) }}";
            const accountsDeleteBaseUrl = "{{ route('compta_accounts.destroy', ['id' => '__ID__']) }}";

            // Fonction utilitaire pour formater le capital social
            function formatCurrency(amount) {
                if (amount === null || amount === '' || amount === '0' || amount === 0) return 'N/A';
                return parseFloat(amount).toLocaleString('fr-FR', {
                    style: 'currency',
                    currency: 'XOF', // Vous pouvez ajuster la devise si nécessaire
                    minimumFractionDigits: 0
                });
            }


            // --- Logique pour la MODALE DE MODIFICATION (Update) ---
            // Correction: Utiliser le sélecteur .edit-btn
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-account-id');
                    const updateForm = document.getElementById('updateAccountForm');

                    // 1. Mise à jour de l'action du formulaire avec l'ID correct
                    // L'URL de modification est supposée être: /compta_accounts/{id} avec la méthode PUT
                    updateForm.action = accountsUpdateBaseUrl.replace('__ID__', id);

                    // 2. Remplissage des champs (directement depuis les data-attributs du bouton)
                    document.getElementById('updateAccountId').value = id;
                    document.getElementById('update_company_name').value = this.getAttribute('data-company-name') || '';
                    document.getElementById('update_activity').value = this.getAttribute('data-activity') || '';
                    document.getElementById('update_juridique_form').value = this.getAttribute('data-juridique-form') || '';

                    // Utilisez un nombre ou null pour le champ number (pas de formatage)
                    document.getElementById('update_social_capital').value = this.getAttribute('data-social-capital') || '';

                    document.getElementById('update_adresse').value = this.getAttribute('data-adresse') || '';
                    document.getElementById('update_code_postal').value = this.getAttribute('data-code-postal') || '';
                    document.getElementById('update_city').value = this.getAttribute('data-city') || '';
                    document.getElementById('update_country').value = this.getAttribute('data-country') || '';
                    document.getElementById('update_phone_number').value = this.getAttribute('data-phone-number') || '';
                    document.getElementById('update_email_adresse').value = this.getAttribute('data-email-adresse') || '';
                    document.getElementById('update_identification_TVA').value = this.getAttribute('data-identification-tva') || '';

                    // Sélection correcte du statut (doit être '1' ou '0')
                    // Utiliser parseInt(value) == 1 pour être sûr que '1' ou 1 est traité
                    const isActive = (parseInt(this.getAttribute('data-is-active')) === 1) ? '1' : '0';
                    document.getElementById('update_is_active').value = isActive;
                });
            });

            // --- Logique pour la MODALE D'AFFICHAGE (See) ---
            // Correction: Utiliser le sélecteur .details-btn
            document.querySelectorAll('.details-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Remplissage des champs de lecture seule
                    document.getElementById('see_company_name').value = this.getAttribute('data-company-name') || '';
                    document.getElementById('see_activity').value = this.getAttribute('data-activity') || '';
                    document.getElementById('see_juridique_form').value = this.getAttribute('data-juridique-form') || '';

                    // Formatage du capital social pour l'affichage
                    const socialCapital = this.getAttribute('data-social-capital');
                    document.getElementById('see_social_capital').value = formatCurrency(socialCapital);

                    document.getElementById('see_adresse').value = this.getAttribute('data-adresse') || '';
                    document.getElementById('see_code_postal').value = this.getAttribute('data-code-postal') || '';
                    document.getElementById('see_city').value = this.getAttribute('data-city') || '';
                    document.getElementById('see_country').value = this.getAttribute('data-country') || '';
                    document.getElementById('see_phone_number').value = this.getAttribute('data-phone-number') || '';
                    document.getElementById('see_email_adresse').value = this.getAttribute('data-email-adresse') || '';
                    document.getElementById('see_identification_TVA').value = this.getAttribute('data-identification-tva') || '';

                    // Affichage du statut
                    const isActive = this.getAttribute('data-is-active');
                    document.getElementById('see_is_active').value = (parseInt(isActive) === 1) ? 'Actif' : 'Inactif';

                    // Mise à jour du titre
                    document.getElementById('seeAccountTitle').textContent = `Détails du Compte : ${this.getAttribute('data-company-name')}`;
                });
            });

            // --- Logique pour la MODALE DE SUPPRESSION (Delete) ---
            // Correction: Utiliser le sélecteur .delete-btn n'est pas nécessaire pour le trigger de modal.
            // On utilise l'événement natif show.bs.modal sur la modal elle-même.
            document.getElementById('deleteAccountModal').addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget; // Bouton qui a déclenché la modal (.delete-btn)
                const accountId = button.getAttribute('data-account-id');
                const companyName = button.getAttribute('data-company-name');

                // Mise à jour de l'action du formulaire avec l'ID correct
                const deleteForm = document.getElementById('deleteAccountForm');
                deleteForm.action = accountsDeleteBaseUrl.replace('__ID__', accountId);

                // Mise à jour du nom de la société à supprimer dans le message
                document.getElementById('accountToDeleteName').textContent = companyName;
            });

            // --- Logique pour la LIGNE CLICABLE ---
             document.querySelectorAll('.clickable-row').forEach(row => {
                const rowDataHref = row.getAttribute('data-href');

                // Gérer le clic sur la ligne entière (y compris les cellules TD)
                row.addEventListener('click', function(e) {
                    // Clic sur un bouton d'action ou un lien dans la dernière colonne (Actions)
                    if (e.target.closest('.dropdown') || e.target.closest('a')) {
                        // Ne rien faire si l'utilisateur clique sur le menu déroulant ou une action
                        return;
                    }
                    if (rowDataHref) {
                        window.location.href = rowDataHref;
                    }
                });
            });
        });
    </script>
</body>

</html>
