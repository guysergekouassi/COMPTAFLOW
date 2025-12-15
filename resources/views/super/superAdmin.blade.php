@extends('layouts.app') // Assurez-vous que le layout de base est correct

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">🚀 Configuration Initiale de la Plateforme (Super-Admin)</h3>
                    <p class="mb-0">Veuillez créer le compte Super-Admin, la première Compagnie et son Administrateur.</p>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('superadmin.setup') }}">
                        @csrf

                        <div class="mb-5 p-4 border rounded bg-light">
                            <h4 class="text-primary border-bottom pb-2 mb-3">👤 Compte Super-Admin (Système)</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="super_name" class="form-label">Prénom (Super-Admin) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('super_name') is-invalid @enderror" id="super_name" name="super_name" value="{{ old('super_name') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="super_last_name" class="form-label">Nom (Super-Admin) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('super_last_name') is-invalid @enderror" id="super_last_name" name="super_last_name" value="{{ old('super_last_name') }}" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="super_email" class="form-label">Adresse Email (Super-Admin) <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('super_email') is-invalid @enderror" id="super_email" name="super_email" value="{{ old('super_email') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="super_password" class="form-label">Mot de Passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('super_password') is-invalid @enderror" id="super_password" name="super_password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="super_password_confirmation" class="form-label">Confirmer Mot de Passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="super_password_confirmation" name="super_password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5 p-4 border rounded bg-white">
                            <h4 class="text-success border-bottom pb-2 mb-3">🏢 Première Compagnie Cliente</h4>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="company_name" class="form-label">Nom de la Compagnie <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="juridique_form" class="form-label">Forme Juridique</label>
                                    <input type="text" class="form-control @error('juridique_form') is-invalid @enderror" id="juridique_form" name="juridique_form" value="{{ old('juridique_form') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="activity" class="form-label">Activité</label>
                                    <input type="text" class="form-control @error('activity') is-invalid @enderror" id="activity" name="activity" value="{{ old('activity') }}">
                                </div>
                                </div>
                        </div>

                        <div class="mb-5 p-4 border rounded bg-light">
                            <h4 class="text-info border-bottom pb-2 mb-3">👨‍💼 Compte Admin (pour la Compagnie)</h4>
                            <p class="text-muted small">C'est le premier utilisateur de la compagnie, qui gérera ses propres comptables.</p>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="admin_name" class="form-label">Prénom (Admin) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('admin_name') is-invalid @enderror" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="admin_last_name" class="form-label">Nom (Admin) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('admin_last_name') is-invalid @enderror" id="admin_last_name" name="admin_last_name" value="{{ old('admin_last_name') }}" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="admin_email" class="form-label">Adresse Email (Admin) <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('admin_email') is-invalid @enderror" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="admin_password" class="form-label">Mot de Passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('admin_password') is-invalid @enderror" id="admin_password" name="admin_password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="admin_password_confirmation" class="form-label">Confirmer Mot de Passe <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="admin_password_confirmation" name="admin_password_confirmation" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-lg btn-success">✅ Finaliser la Configuration</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
