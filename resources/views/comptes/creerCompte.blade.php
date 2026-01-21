<!DOCTYPE html>
<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                @include('components.header')
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Alerts -->
                        @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                        @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <!-- Card Table -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">COMPTE UTILISATEURS</h5>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                                    <i class="bx bx-plus"></i> Nouveau
                                </button>
                            </div>

                            <div class="table-responsive text-nowrap p-3">
                                <table class="table table-hover table-striped table-bordered align-middle" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Prenoms</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Permissions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($users) && $users->isNotEmpty())
                                            @foreach($users as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->last_name }}</td>
                                                    <td>{{ $user->email_adresse }}</td>
                                                    <td>{{ $user->role }}</td>
                                                    <td>
                                                        @forelse($user->habilitations as $key => $value)
                                                            <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                                                                {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                            </span>
                                                        @empty
                                                            <span class="badge bg-secondary">Aucune habilitation</span>
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center">Aucun utilisateur trouvé</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modal Create -->
                        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Créer un compte Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    {{-- 1. NOM --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" id="name" autocomplete="name" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 2. PRÉNOM --}}
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Prénom</label>
                        <input type="text" id="last_name" autocomplete="family-name" name="last_name"
                            class="form-control @error('last_name') is-invalid @enderror"
                            value="{{ old('last_name') }}" required>
                        @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 3. EMAIL --}}
                    <div class="mb-3">
                        <label for="email_adresse" class="form-label">Email</label>
                        <input type="email" id="email_adresse" autocomplete="email" name="email_adresse"
                            class="form-control @error('email_adresse') is-invalid @enderror"
                            value="{{ old('email_adresse') }}" required>
                        @error('email_adresse')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 4. MOT DE PASSE --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" id="password" autocomplete="new-password" name="password"
                            class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 5. RÔLE --}}
                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle</label>
                        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">Sélectionner un rôle</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="comptable" {{ old('role') == 'comptable' ? 'selected' : '' }}>Comptable</option>

                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 6. HABILITATIONS (Utilisation de bootstrap-select pour le style) --}}
                    <div class="mb-3">
                        <label for="habilitations" class="form-label">Habilitations</label>
                        <select name="habilitations[]" id="habilitations" class="form-select @error('habilitations') is-invalid @enderror" multiple required>
                            <option value="dashboard" {{ in_array('dashboard', old('habilitations', [])) ? 'selected' : '' }}>Dashboard</option>
                            <option value="plan_comptable" {{ in_array('plan_comptable', old('habilitations', [])) ? 'selected' : '' }}>Plan Comptable</option>
                            <option value="parametre" {{ in_array('parametre', old('habilitations', [])) ? 'selected' : '' }}>Parametre</option>
                            <option value="reservations" {{ in_array('reservations', old('habilitations', [])) ? 'selected' : '' }}>Réservations</option>
                        </select>
                        @error('habilitations')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

                    </div>
                </div>
                <!-- / Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
@include('components.footer')


   <script>
    // Initialisation de bootstrap-select
    $(document).ready(function() {
        $('.selectpicker').selectpicker();
    });
</script>

</body>
</html>
