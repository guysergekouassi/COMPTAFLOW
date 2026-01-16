<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')
{{-- @if (session()->has('original_admin_id'))
    <div class="alert alert-danger text-center mb-0 rounded-0" role="alert" style="z-index: 1000;">
        <i class="bx bx-shield-alt-2 me-2"></i>

        <a href="{{ route('admin.leave_impersonation') }}" class="alert-link text-decoration-underline ms-3 fw-bold">
            Quitter le compte
        </a>
    </div>
@endif --}}
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
         {{-- <pre>
    @php dd($habilitations); @endphp
</pre> --}}
            @include('components.sidebar', ['habilitations'=> $habilitations])
            <div class="layout-page">
                @include('components.header')

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row g-6 mb-6">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Nombre d'utilisateurs</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ number_format($totalUsers) }}</h4>
                                                </div>
                                                <small class="mb-0">Total Users</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    <i class="icon-base bx bx-group icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Utilisateurs connectés</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ number_format($connectedUsers) }}</h4>
                                                </div>
                                                <small class="mb-0">En ligne</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-danger">
                                                    <i class="icon-base bx bx-user-plus icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left">
                                                <span class="text-heading">Utilisateurs hors ligne</span>
                                                <div class="d-flex align-items-center my-1">
                                                    <h4 class="mb-0 me-2">{{ number_format($offlineUsers) }}</h4>
                                                </div>
                                                <small class="mb-0">Hors ligne</small>
                                            </div>
                                            <div class="avatar">
                                                <span class="avatar-initial rounded bg-label-warning">
                                                    <i class="icon-base bx bx-user-voice icon-lg"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>
                        @endif


                        <div class="card mb-5">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Administrateurs ({{ $admins->count() }})</h5>
                                <div>
                                    <button class="btn btn-outline-primary me-2 btn-sm" data-bs-toggle="collapse"
                                        data-bs-target="#filterPanel">
                                        <i class="bx bx-filter-alt me-1"></i> Filtrer
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#modalCenterCreate">
                                        Ajouter un utilisateur
                                    </button>
                                </div>
                            </div>

                            <div class="collapse px-3 pt-2" id="filterPanel">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <input type="text" id="filter-name" class="form-control"
                                            placeholder="Filtrer par nom..." />
                                    </div>

                                </div>
                            </div>

                            <div class="table-responsive text-nowrap mt-3">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nom complet</th>
                                            <th>Email</th>
                                            <th>Rôle</th>

                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="adminTableBody">
                                        @foreach($admins as $user)
                                            <tr>
                                                <td>{{ $user->name }} {{ $user->last_name }}</td>
                                                <td>{{ $user->email_adresse }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ $user->role === 'admin' ? 'warning' : 'info' }}">
                                                        {{ ucfirst($user->role) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ $user->is_online ? 'success' : 'secondary' }}">
                                                        {{ $user->is_online ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                <div class="d-flex gap-2">
                                                <button
                                                        class="btn p-0 border-0 bg-transparent text-primary btn-see-user"
                                                        data-bs-toggle="modal" data-bs-target="#modalCenterSee"
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}"
                                                        data-user-lastname="{{ $user->last_name }}"
                                                        data-user-email="{{ $user->email_adresse }}"
                                                        data-user-role="{{ $user->role }}"
                                                        data-user-habilitations='@json($user->habilitations)'
                                                        data-user-company-id="{{ $user->company->id ?? '' }}"
                                                        data-user-company-name="{{ $user->company->company_name ?? 'N/A' }}">
                                                        <i class="bx bx-user-circle fs-5"></i>
                                                    </button>


                                                    <button type="button"
                                                        class="btn p-0 border-0 bg-transparent text-primary btn-edit-user"
                                                        data-bs-toggle="modal" data-bs-target="#modalCenterUpdate"
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}"
                                                        data-user-lastname="{{ $user->last_name }}"
                                                        data-user-email="{{ $user->email_adresse }}"
                                                        data-user-role="{{ $user->role }}"
                                                        data-user-habilitations='@json($user->habilitations)'
                                                        data-user-company-id="{{ $user->company->id ?? '' }}"
                                                        data-user-company-name="{{ $user->company->company_name ?? 'N/A' }}">
                                                        <i class="bx bx-edit-alt fs-5"></i>
                                                    </button>



                                                            <button class="btn p-0 border-0 bg-transparent text-danger"
                                                                data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                                                data-user-id="{{ $user->id }}"
                                                                data-user-name="{{ $user->name }} {{ $user->last_name }}">
                                                                <i class="bx bx-trash fs-5"></i>
                                                            </button>

                                                        </div>
                                                     </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Comptables ({{ $comptables->count() }})</h5>
                            </div>

                            <div class="table-responsive text-nowrap mt-3">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nom complet</th>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                            <th>statut</th>
                                            <th>Comptabilte</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="comptableTableBody">
                                        @foreach($comptables as $user)
                                            <tr>
                                                <td>{{ $user->name }} {{ $user->last_name }}</td>
                                                <td>{{ $user->email_adresse }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ $user->role === 'admin' ? 'warning' : 'info' }}">
                                                        {{ ucfirst($user->role) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ $user->is_online ? 'success' : 'secondary' }}">
                                                        {{ $user->is_online ? 'Actif' : 'Inactif' }}
                                                    </span>
                                                </td>
                                                <td>
                                                        @if ($user->company)
                                                            {{ $user->company->company_name }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                       <button
                                                            class="btn p-0 border-0 bg-transparent text-primary btn-see-user"
                                                            data-bs-toggle="modal" data-bs-target="#modalCenterSee"
                                                            data-user-id="{{ $user->id }}"
                                                            data-user-name="{{ $user->name }}"
                                                            data-user-lastname="{{ $user->last_name }}"
                                                            data-user-email="{{ $user->email_adresse }}"
                                                            data-user-role="{{ $user->role }}"
                                                            data-user-habilitations='@json($user->habilitations)'
                                                            data-user-company-id="{{ $user->company->id ?? '' }}"
                                                            data-user-company-name="{{ $user->company->company_name ?? 'N/A' }}">
                                                            <i class="bx bx-user-circle fs-5"></i>
                                                        </button>


                                                        <button type="button"
                                                                class="btn p-0 border-0 bg-transparent text-primary btn-edit-user"
                                                                data-bs-toggle="modal" data-bs-target="#modalCenterUpdate"
                                                                data-user-id="{{ $user->id }}"
                                                                data-user-name="{{ $user->name }}"
                                                                data-user-lastname="{{ $user->last_name }}"
                                                                data-user-email="{{ $user->email_adresse }}"
                                                                data-user-role="{{ $user->role }}"
                                                                data-user-habilitations='@json($user->habilitations)'
                                                                data-user-company-id="{{ $user->company->id ?? '' }}"
                                                                data-user-company-name="{{ $user->company->company_name ?? 'N/A' }}">
                                                                <i class="bx bx-edit-alt fs-5"></i>
                                                            </button>



                                                        <button class="btn p-0 border-0 bg-transparent text-danger"
                                                            data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                                            data-user-id="{{ $user->id }}"
                                                            data-user-name="{{ $user->name }} {{ $user->last_name }}">
                                                            <i class="bx bx-trash fs-5"></i>
                                                        </button>

                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalCenterTitle">
                                            Fiche utilisateur
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="createUserForm" method="POST" action="{{ route('users.store') }}"
                                            novalidate>
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="name" class="form-label">Nom</label>
                                                    <input type="text" id="name" name="name" class="form-control"
                                                        required  />
                                                    <div class="invalid-feedback" id="errorFirstName"></div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="last_name" class="form-label">Prénom</label>
                                                    <input type="text" id="last_name" name="last_name"
                                                        class="form-control" required />
                                                    <div class="invalid-feedback" id="errorLastName"></div>
                                                </div>
                                                <div class="col-12">
                                                    <label for="email_adresse" class="form-label">Email</label>
                                                    <input type="email" id="email_adresse" name="email_adresse"
                                                        class="form-control" required />
                                                    <div class="invalid-feedback" id="errorEmail"></div>
                                                </div>
                                                <div class="col-6">
                                                    <label for="password" class="form-label">Mot de passe</label>
                                                    <input type="password" id="password" name="password"
                                                        class="form-control" required />
                                                    <div class="form-text">
                                                        8 caractères minimum, une majuscule, un chiffre.
                                                    </div>
                                                    <div class="invalid-feedback" id="errorPassword"></div>
                                                </div>
                                                <div class="col-6">
                                                    <label for="confirmPassword" class="form-label">Confirmer le mot de
                                                        passe</label>
                                                    <input type="password" id="confirmPassword" class="form-control"
                                                        required />
                                                    <div class="invalid-feedback" id="errorConfirmPassword"></div>
                                                </div>



                                                    <div class="col-12 mt-3" id="newCompanyNameField" style="display: none;">
                                                        <label for="new_company_name" class="form-label">Nom de la comptabilite</label>
                                                        <input type="text" id="new_company_name" name="new_company_name" class="form-control" placeholder="Entrez le nom de la comptabilité." />
                                                    </div>
                                                     <div class="col-6">
                                                     <label for="company_id" class="form-label">Comptabilite rattachéé a l'utilisateur</label>
                                                        <select name="company_id" id="company_id" class="form-select">
                                                            <option value="new">-- selectionnez une comptabilité --</option>

                                                            {{-- Cette boucle va maintenant utiliser la liste filtrée du contrôleur --}}
                                                            @foreach($managedCompanies as $company)
                                                                <option value="{{ $company->id }}"
                                                                    {{ $company->id == $currentCompanyId ? 'selected' : '' }}>
                                                                    {{ $company->company_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                     </div>
                                                <div class="col-6">
                                                    <label for="role" class="form-label">Rôle</label>
                                                    <select id="role" name="role" class="form-select" required>
                                                        <option value="">Sélectionner un rôle</option>
                                                        <option value="admin">Administrateur</option>
                                                        <option value="comptable">Comptable</option>
                                                    </select>
                                                    <div class="invalid-feedback" id="errorRole"></div>
                                                </div>
                                                <div id="habilitationsGroup" class="row g-3 mt-3 d-none">
                                                    <label class="form-label fw-bold">Habilitations</label>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="dashboard"
                                                                id="hab_dashboard">
                                                            <label class="form-check-label"
                                                                for="hab_dashboard">Dashboard</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="plan_comptable"
                                                                id="hab_plan_comptable">
                                                            <label class="form-check-label"
                                                                for="hab_plan_comptable">Plan comptable</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="plan_tiers"
                                                                id="hab_plan_tiers">
                                                            <label class="form-check-label" for="hab_plan_tiers">Plan
                                                                tiers</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="journaux"
                                                                id="hab_journaux">
                                                            <label class="form-check-label"
                                                                for="hab_journaux">Journaux</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="grand_livre"
                                                                id="hab_grand_livre">
                                                            <label class="form-check-label" for="hab_grand_livre">Grand
                                                                livre</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="balance" id="hab_balance">
                                                            <label class="form-check-label"
                                                                for="hab_balance">Balance</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="etats_financiers"
                                                                id="hab_etats_financiers">
                                                            <label class="form-check-label"
                                                                for="hab_etats_financiers">États financiers</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="fichier_joindre"
                                                                id="hab_fichier_joindre">
                                                            <label class="form-check-label"
                                                                for="hab_fichier_joindre">Fichier à joindre</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="parametre"
                                                                id="hab_parametre">
                                                            <label class="form-check-label"
                                                                for="hab_parametre">Paramètre</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="tresorerie"
                                                                id="hab_tresorerie">
                                                            <label class="form-check-label"
                                                                for="hab_tresorerie">Trésorerie</label>
                                                        </div>
                                                    </div>
                                                     <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="poste"
                                                                id="hab_poste">
                                                            <label class="form-check-label"
                                                                for="hab_poste">Poste de trésorerie </label>
                                                        </div>
                                                    </div>
                                                     <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="modal_saisie_direct"
                                                                id="hab_modal_saisie_direct">
                                                            <label class="form-check-label"
                                                                for="hab_modal_saisie_direct">Nouvelle saisie</label>
                                                        </div>
                                                    </div>
                                                      <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="exercice_comptable"
                                                                id="hab_exercice_comptable">
                                                            <label class="form-check-label"
                                                                for="hab_exercice_comptable">Exercice comptable</label>
                                                        </div>
                                                    </div>
                                                     <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="Balance_Tiers"
                                                                id="hab_Balance_Tiers">
                                                            <label class="form-check-label"
                                                                for="hab_Balance_Tiers">Balance Des Tiers</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="habilitations[]" value="grand_livre_tiers"
                                                                id="hab_grand_livre_tiers">
                                                            <label class="form-check-label"
                                                                for="hab_grand_livre_tiers">Grand Livre Tiers</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>



                                            </div>
                                            <div class="modal-footer justify-content-end">
                                                <button type="button" class="btn btn-label-secondary"
                                                    data-bs-dismiss="modal">
                                                    Fermer
                                                </button>
                                                <button type="submit" class="btn btn-primary"
                                                    onclick="return validerCreationUtilisateur(event)">
                                                    Enregistrer
                                                </button>
                                            </div>

                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>


{{-- Modification --}}
 <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            {{-- ATTENTION : L'action de la route doit être mise à jour par JS avant l'envoi --}}
            <form id="updateUserForm" method="POST"
                action="{{ route('users.update', ['id' => '__ID__']) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="updateUserId" />

                <div class="modal-header">
                    <h5 class="modal-title">Modification de l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="updateFirstName" class="form-label">Prénom</label>
                        <input type="text" id="updateFirstName" name="name" class="form-control"
                            placeholder="Entrer le prénom" />
                        <div class="invalid-feedback" id="updateFirstNameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="updateLastName" class="form-label">Nom de famille</label>
                        <input type="text" id="updateLastName" name="last_name"
                            class="form-control" placeholder="Entrer le nom de famille" />
                        <div class="invalid-feedback" id="updateLastNameError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="updateEmail" class="form-label">Email</label>
                        <input type="email" id="updateEmail" name="email_adresse"
                            class="form-control" placeholder="exemple@email.com" />
                        <div class="invalid-feedback" id="updateEmailError"></div>
                    </div>

                    {{-- Champ Rôle --}}
                    <div class="mb-3">
                        <label for="updateRole" class="form-label">Rôle</label>
                        <select id="updateRole" name="role" class="form-select">
                            <option value="">-- Sélectionner un rôle --</option>
                            <option value="admin">Administrateur</option>
                            <option value="comptable">Comptable</option>
                        </select>
                        <div class="invalid-feedback" id="updateRoleError"></div>
                    </div>

                    {{-- **AJOUT DU CHAMP COMPAGNIE** --}}
                    <div class="mb-3" id="updateCompanyField">
                        <label for="updateCompanyId" class="form-label">Comptabilite rattachée</label>
                        <select name="company_id" id="updateCompanyId" class="form-select">
                            <option value="">-- Sélectionner une comptabilité --</option>
                            @foreach($managedCompanies as $company)
                                <option value="{{ $company->id }}">
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Section Habilitations (CORRECTION APPLIQUÉE ICI) --}}
                    <div id="updateHabilitationsSection" class="mt-4">
                        <h6 class="mb-2">Habilitations</h6>
                        <div class="row">
                            @php
                                // Liste complète des habilitations
                                $habilitations = [
                                    'dashboard',
                                    'plan_comptable',
                                    'plan_tiers',
                                    'journaux',
                                    'grand_livre',
                                    'balance',
                                    'tresorerie',
                                    'etats_financiers',
                                    'fichier_joindre',
                                    'parametre',
                                    'accounting_journals',
                                    'exercice_comptable',
                                    'Etat de rapprochement bancaire',
                                    'Gestion de la trésorerie',
                                    'gestion_analytique',
                                    'gestion_tiers',
                                    'user_management',
                                    'gestion_immobilisations',
                                    'gestion_reportings',
                                    'compagny_information',
                                    'gestion_stocks',
                                    'modal_saisie_direct',
                                    'nouvelle_saisie',
                                    'grand_livre_tiers',
                                    'poste',
                                    'Balance_Tiers',

                                ];
                            @endphp

                            @foreach ($habilitations as $habilitation)
                                <div class="mb-2 col-md-4">
                                    <div class="form-check">
                                        {{-- CHAMP CACHÉ DE SÉCURITÉ (SOLUTION) --}}
                                        <input type="hidden" name="habilitations[{{ $habilitation }}]" value="0">

                                        <input class="form-check-input" type="checkbox"
                                            id="update_{{ $habilitation }}"
                                            name="habilitations[{{ $habilitation }}]" value="1">
                                        <label class="form-check-label"
                                            for="update_{{ $habilitation }}">
                                            {{ ucfirst(str_replace('_', ' ', $habilitation)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary"
                        data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les
                        modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCenterSee" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Informations de l'utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="seeFirstName" class="form-label">Prénom</label>
                        <input type="text" id="seeFirstName" class="form-control" readonly />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="seeLastName" class="form-label">Nom de famille</label>
                        <input type="text" id="seeLastName" class="form-control" readonly />
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="seeEmail" class="form-label">Email</label>
                        <input type="email" id="seeEmail" class="form-control" readonly />
                    </div>

                    {{-- Le champ Compagnie et Rôle sont maintenant correctement dans le même row que l'Email --}}
                    <div class="mb-3 col-md-6">
                        <label for="seeRole" class="form-label">Rôle</label>
                        <input type="text" id="seeRole" class="form-control" readonly />
                    </div>
                </div>

                <div class="row">
                    {{-- Le champ Compagnie est dans un nouveau row pour une meilleure disposition (ou déplacez-le dans le row précédent si vous voulez 3 champs) --}}
                    <div class="mb-3 col-md-6">
                        <label for="seeCompany" class="form-label">Comptabilite rattachée</label>
                        <input type="text" id="seeCompany" class="form-control" readonly />
                    </div>
                </div>


                <div id="seeHabilitationsSection" class="mt-4" style="display: none;">
                    <h6 class="mb-2">Habilitations</h6>
                    <div class="row">
                        @php
                            $habilitations = [
                                        'dashboard',
                                        'plan_comptable',
                                        'plan_tiers',
                                        'journaux',
                                        'grand_livre',
                                        'balance',
                                        'etats_financiers',
                                        'fichier_joindre',
                                        'tresorerie',
                                        'parametre',
                                        'accounting_journals',
                                        'exercice_comptable',
                                        'Etat de rapprochement bancaire',
                                        'Gestion de la trésorerie',
                                        'gestion_analytique',
                                        'gestion_tiers',
                                        'user_management',
                                        'gestion_immobilisations',
                                        'gestion_reportings',
                                        'compagny_information',
                                        'gestion_stocks',
                                        'gestion_reportings',
                                        'modal_saisie_direct',
                                        'nouvelle_saisie',
                                        'grand_livre_tiers',
                                        'poste',
                                        'Balance_Tiers',
                            ];
                        @endphp

                        @foreach ($habilitations as $habilitation)
                            <div class="mb-2 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        id="see_{{ $habilitation }}" disabled >
                                    <label class="form-check-label" for="see_{{ $habilitation }}">
                                        {{ ucfirst(str_replace('_', ' ', $habilitation)) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
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


                        <div class="modal fade" id="deleteUserModal" tabindex="-1"
                            aria-labelledby="deleteUserModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content border-0 shadow">
                                    <form id="deleteUserForm" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <div class="modal-header text-dark justify-content-center">
                                            <h5 class="modal-title" id="deleteUserModalLabel">
                                                <i class="bx bx-error-circle me-2"></i>Confirmer la suppression
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Fermer"></button>
                                        </div>

                                        <div class="modal-body text-center">
                                            <p class="mb-0">
                                                Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est
                                                <strong>irréversible</strong>.
                                            </p>
                                            <p class="fw-bold text-danger mt-2" id="userToDelete">Nom Prénom</p>
                                        </div>

                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Annuler
                                            </button>
                                            <button type="submit" class="btn btn-danger" id="confirmDeleteUserBtn">
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

    <script>
        const usersUpdateBaseUrl = "{{ route('users.update', ['id' => '__ID__']) }}";
        const usersDeleteUrl = "{{ route('users.destroy', ['id' => '__ID__']) }}";
    </script>
    <script src="{{ asset('js/user_m.js') }}"></script>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const companySelect = document.getElementById('company_id');
    const newCompanyNameField = document.getElementById('newCompanyNameField');
    const newCompanyNameInput = document.getElementById('new_company_name');

    // Fonction pour basculer l'affichage
    function toggleNewCompanyField() {
        if (companySelect.value === 'new') {
            newCompanyNameField.style.display = 'block';
            newCompanyNameInput.setAttribute('required', 'required'); // Rendez le champ obligatoire
        } else {
            newCompanyNameField.style.display = 'none';
            newCompanyNameInput.removeAttribute('required'); // Supprimez l'obligation
            newCompanyNameInput.value = ''; // Réinitialisez la valeur
        }
    }

    // Écouteur d'événement sur le changement de sélection
    companySelect.addEventListener('change', toggleNewCompanyField);

    // Initialisation au chargement de la modale/page
    toggleNewCompanyField();
});
    </script>
    <script>
    // 1. Convertir les données PHP en JavaScript
    // Assurez-vous que les utilisateurs sont passés avec leurs habilitations (user.habilitations)
    const allHabilitations = @json($allHabilitations);
    const allUsersData = @json($users->keyBy('id'));
    const usersUpdateBaseUrl = "{{ route('users.update', ['id' => '__ID__']) }}";

    // 2. Gestion de l'ouverture de la modale d'Update
    $('#modalCenterUpdate').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const userId = button.data('id');
        const user = allUsersData[userId]; // Récupérer les données complètes de l'utilisateur

        const modal = $(this);
        const form = modal.find('#updateForm');
        const container = modal.find('#habilitations-container-update'); // Assurez-vous que ce conteneur existe

        // Mise à jour de l'action du formulaire
        form.attr('action', usersUpdateBaseUrl.replace('__ID__', userId));

        // 3. Génération et pré-sélection des checkboxes
        container.empty();

        // Récupérer l'objet des habilitations de l'utilisateur
        const userHabilitations = user.habilitations || {};

        allHabilitations.forEach(function(permissionKey) {
            // VÉRIFICATION CRUCIALE : est-ce que cette clé existe et a la valeur TRUE pour cet utilisateur ?
            const isChecked = userHabilitations[permissionKey] === true;

            // Rendre le nom de la permission lisible (ex: grand_livre -> Grand Livre)
            const readableLabel = permissionKey.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

            // Créer la structure HTML de la checkbox
            const html = `
                <div class="form-check me-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="habilitations[]"
                        value="${permissionKey}"
                        id="perm_update_${permissionKey}"
                        ${isChecked ? 'checked' : ''} />
                    <label class="form-check-label" for="perm_update_${permissionKey}">
                        ${readableLabel}
                    </label>
                </div>
            `;
            container.append(html);
        });

        // N'oubliez pas de mettre à jour les autres champs de la modale (nom, email, rôle)
        modal.find('#name_update').val(user.name);
        modal.find('#last_name_update').val(user.last_name);
        modal.find('#email_update').val(user.email);
        // ... (etc. pour les autres champs)
    });
</script>
</body>

</html>
