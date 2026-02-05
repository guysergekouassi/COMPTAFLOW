<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<!-- Inclusions supplémentaires pour le profil premium -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .premium-profile-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    .profile-header-bg {
        height: 200px;
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        position: relative;
    }
    .avatar-wrapper {
        margin-top: -80px;
        position: relative;
        z-index: 2;
    }
    .img-profile-large {
        width: 160px;
        height: 160px;
        border: 7px solid #fff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        object-fit: cover;
    }
    .stat-card {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    .stat-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        transform: translateY(-5px);
    }
    .activity-item {
        position: relative;
        padding-left: 30px;
        padding-bottom: 20px;
        border-left: 2px solid #e2e8f0;
    }
    .activity-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #3b82f6;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Mon <span class="text-blue-600">Profil</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Profil Premium -->
                        <div class="card premium-profile-card shadow-lg mb-5">
                            <div class="profile-header-bg">
                                <div class="position-absolute bottom-0 end-0 p-4 opacity-10">
                                    <i class="fa-solid fa-user-tie fa-9x text-white"></i>
                                </div>
                            </div>
                            <div class="card-body pb-5">
                                <div class="row align-items-end">
                                    <div class="col-auto">
                                        <div class="avatar-wrapper ms-4">
                                            @if($user->profile_photo_path)
                                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" class="rounded-circle img-profile-large bg-white">
                                            @else
                                                <div class="rounded-circle img-profile-large bg-blue-500 d-flex align-items-center justify-content-center text-white fs-1 fw-bold">
                                                    {{ $user->initiales }}
                                                </div>
                                            @endif
                                            <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 cursor-pointer shadow-sm border border-white border-3">
                                                <i class="fa-solid fa-camera"></i>
                                                <form action="{{ route('user.settings.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                                                    @csrf
                                                    <input type="file" name="avatar" id="avatarInput" class="d-none" onchange="document.getElementById('avatarForm').submit()">
                                                </form>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col mt-3 mt-md-0">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                                            <div>
                                                <h2 class="fw-black mb-1 text-slate-800">{{ $user->name }} {{ $user->last_name }}</h2>
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="badge bg-blue-50 text-blue-600 px-3 py-2 rounded-pill font-bold">
                                                        <i class="fa-solid fa-shield-halved me-2"></i>{{ ucfirst($user->role) }}
                                                    </span>
                                                    <span class="text-slate-500"><i class="fa-solid fa-envelope me-2"></i>{{ $user->email_adresse }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-3 mt-md-0">
                                                <a href="{{ route('user.settings') }}" class="btn btn-primary px-4 py-2 rounded-xl fw-bold shadow-blue-200">
                                                    <i class="fa-solid fa-user-gear me-2"></i>Paramètres
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Statistiques & Chart -->
                            <div class="col-lg-8">
                                <div class="card h-100 border-0 shadow-sm rounded-20 p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="fw-bold m-0 text-slate-800">Analyse de l'activité</h5>
                                        <select class="form-select w-auto border-0 bg-slate-50 fw-bold text-slate-500 rounded-pill">
                                            <option>7 derniers jours</option>
                                            <option>30 derniers jours</option>
                                        </select>
                                    </div>
                                    <canvas id="activityChart" style="max-height: 350px;"></canvas>
                                    
                                    <div class="row g-3 mt-4">
                                        <div class="col-md-4">
                                            <div class="stat-card">
                                                <div class="text-blue-500 mb-2"><i class="fa-solid fa-file-invoice fa-lg"></i></div>
                                                <h3 class="fw-black m-0">{{ $stats['total_entries'] }}</h3>
                                                <p class="text-slate-400 small m-0 uppercase font-black">Écritures saisies</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-card">
                                                <div class="text-emerald-500 mb-2"><i class="fa-solid fa-check-double fa-lg"></i></div>
                                                <h3 class="fw-black m-0">95%</h3>
                                                <p class="text-slate-400 small m-0 uppercase font-black">Fiabilité Saisie</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="stat-card">
                                                <div class="text-purple-500 mb-2"><i class="fa-solid fa-calendar-day fa-lg"></i></div>
                                                <h3 class="fw-black m-0">{{ $user->created_at->diffInDays(now()) }}j</h3>
                                                <p class="text-slate-400 small m-0 uppercase font-black">Ancienneté</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Infos & Habilitations -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm rounded-20 mb-4 p-4">
                                    <h5 class="fw-bold mb-4 text-slate-800">Détails Professionnels</h5>
                                    <div class="space-y-4">
                                        <div class="d-flex justify-content-between align-items-center p-3 rounded-15 bg-slate-50 mb-3">
                                            <span class="text-slate-500 fw-bold">Entreprise</span>
                                            <span class="fw-black text-slate-800">{{ $company->company_name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-3 rounded-15 bg-slate-50 mb-3">
                                            <span class="text-slate-500 fw-bold">Compte id</span>
                                            <span class="fw-black text-slate-800">#{{ $user->id }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center p-3 rounded-15 bg-slate-50">
                                            <span class="text-slate-500 fw-bold">Inscrit le</span>
                                            <span class="fw-black text-slate-800">{{ $user->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm rounded-20 p-4">
                                    <h5 class="fw-bold mb-4 text-slate-800">Habilitations Actives</h5>
                                    <div style="max-height: 300px; overflow-y: auto;">
                                        @foreach($habilitations as $key => $value)
                                            @if($value)
                                                <div class="d-flex align-items-center gap-3 mb-3 p-2 hover-bg-slate-50 rounded-10 transition-all">
                                                    <div class="bg-emerald-100 text-emerald-600 p-2 rounded-8">
                                                        <i class="fa-solid fa-check fs-small"></i>
                                                    </div>
                                                    <span class="text-slate-600 fw-bold small">{{ ucfirst(str_replace(['_', '.'], ' ', $key)) }}</span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique d'activité Premium
        const ctx = document.getElementById('activityChart').getContext('2d');
        const activityData = @json($stats['activity_data']);
        const activityLabels = @json($stats['activity_labels']);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: activityLabels,
                datasets: [{
                    label: 'Opérations effectuées',
                    data: activityData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 5,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>
