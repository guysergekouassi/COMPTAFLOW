<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<style>
    .notification-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        cursor: pointer;
    }
    .notification-card:hover {
        background-color: #f8fafc;
        transform: translateX(5px);
    }
    .notification-unread {
        background-color: #eff6ff;
        border-left-color: #3b82f6;
    }
    .type-badge {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Centre de <span class="text-blue-600">Notifications</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row g-4">
                            <!-- Liste des notifications -->
                            <div class="col-lg-8">
                                <div class="card h-100 shadow-sm border-0">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-white py-4">
                                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-inbox me-2 text-blue-500"></i>Boîte de réception</h5>
                                        <span class="badge bg-blue-100 text-blue-600 px-3">{{ $notifications->total() }} total</span>
                                    </div>
                                    <div class="card-body p-0">
                                        @if($notifications->isEmpty())
                                            <div class="text-center py-5">
                                                <i class="fa-solid fa-bell-slash fa-4x text-slate-200 mb-3"></i>
                                                <p class="text-slate-500">Vous n'avez aucune notification pour le moment.</p>
                                            </div>
                                        @else
                                            <div class="list-group list-group-flush">
                                                @foreach($notifications as $notif)
                                                    <div class="list-group-item notification-card {{ !$notif->is_read ? 'notification-unread' : '' }} border-0 py-4 px-4" 
                                                         onclick="markAsRead({{ $notif->id }})">
                                                        <div class="d-flex gap-4">
                                                            <div class="type-badge bg-{{ $notif->type === 'error' ? 'red' : ($notif->type === 'warning' ? 'orange' : ($notif->type === 'success' ? 'green' : 'blue')) }}-50 text-{{ $notif->type === 'error' ? 'red' : ($notif->type === 'warning' ? 'orange' : ($notif->type === 'success' ? 'green' : 'blue')) }}-600">
                                                                <i class="fa-solid {{ $notif->type === 'message' ? 'fa-envelope' : 'fa-circle-info' }}"></i>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                                    <h6 class="mb-0 fw-bold">{{ $notif->title }}</h6>
                                                                    <small class="text-slate-400">{{ $notif->created_at->diffForHumans() }}</small>
                                                                </div>
                                                                <p class="text-slate-600 mb-2">{{ $notif->message }}</p>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="small text-slate-400">De : <strong>{{ $notif->sender->name }} {{ $notif->sender->last_name }}</strong></span>
                                                                    @if(!$notif->is_read)
                                                                        <span class="badge bg-blue-500 rounded-pill p-1" style="width: 8px; height: 8px;"></span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="p-4 border-top">
                                                {{ $notifications->links() }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Formulaire de nouveau message/notification -->
                            <div class="col-lg-4">
                                <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                                    <div class="card-header bg-white py-4 border-bottom">
                                        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-paper-plane me-2 text-indigo-500"></i>Nouvelle Communication</h5>
                                    </div>
                                    <div class="card-body py-4">
                                        <form action="{{ route('notifications.store') }}" method="POST">
                                            @csrf
                                            <div class="mb-4">
                                                <label class="form-label font-bold text-slate-500 small uppercase tracking-wider">Destinataire</label>
                                                <select name="receiver_id" class="form-select border-slate-200 py-3 rounded-xl shadow-none" required>
                                                    <option value="">Sélectionner un contact...</option>
                                                    @foreach($recipients as $recipient)
                                                        <option value="{{ $recipient->id }}">{{ $recipient->name }} {{ $recipient->last_name }} ({{ ucfirst($recipient->role) }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label font-bold text-slate-500 small uppercase tracking-wider">Objet</label>
                                                <input type="text" name="title" class="form-control border-slate-200 py-3 rounded-xl shadow-none" placeholder="Ex: Rappel clôture..." required>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label font-bold text-slate-500 small uppercase tracking-wider">Priorité & Type</label>
                                                <select name="type" class="form-select border-slate-200 py-3 rounded-xl shadow-none" required>
                                                    <option value="message">Message simple</option>
                                                    <option value="info">Information importante</option>
                                                    <option value="warning">Avertissement</option>
                                                    <option value="error">Action requise / Urgence</option>
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label font-bold text-slate-500 small uppercase tracking-wider">Message</label>
                                                <textarea name="message" rows="4" class="form-control border-slate-200 py-3 rounded-xl shadow-none" placeholder="Rédigez votre message ici..." required></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-premium-blue w-100" style="background:#6366f1 !important;">
                                                <i class="fa-solid fa-paper-plane me-2"></i>Envoyer le message
                                            </button>
                                        </form>
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
        function markAsRead(id) {
            fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>
