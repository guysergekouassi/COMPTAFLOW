<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">
@include('components.head')
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => "Assistant IA Premium"])
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="container-fluid py-4" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: 90vh;">
    <div class="row">
        <!-- Sidebar des Agents -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm blur-card mb-4" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border-radius: 20px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4" style="color: #1e293b;">Nos Experts IA</h5>
                    <div class="agent-list">
                        @foreach($agents as $key => $agent)
                        <div class="agent-item d-flex align-items-center p-3 mb-3 cursor-pointer @if($key == 'superviseur') active-agent @endif" 
                             onclick="selectAgent('{{ $key }}', '{{ $agent['name'] }}', '{{ $agent['color'] }}')"
                             id="agent-{{ $key }}"
                             style="border-radius: 15px; transition: all 0.3s ease;">
                            <div class="icon-box me-3 text-white d-flex align-items-center justify-content-center" 
                                 style="width: 45px; height: 45px; background: {{ $agent['color'] }}; border-radius: 12px; box-shadow: 0 4px 12px {{ $agent['color'] }}44;">
                                <i class="{{ $agent['icon'] }}"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold" style="font-size: 0.95rem;">{{ $agent['name'] }}</h6>
                                <small class="text-muted" style="font-size: 0.75rem;">{{ $agent['desc'] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-md-9">
            <div class="card border-0 shadow-lg blur-card d-flex flex-column" style="height: 80vh; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(15px); border-radius: 25px; overflow: hidden;">
                <!-- Header -->
                <div class="chat-header p-4 d-flex align-items-center justify-content-between" style="border-bottom: 1px solid rgba(0,0,0,0.05); background: white;">
                    <div class="d-flex align-items-center">
                        <div id="current-agent-icon" class="icon-box me-3 text-white d-flex align-items-center justify-content-center" 
                             style="width: 50px; height: 50px; background: #4f46e5; border-radius: 15px;">
                            <i class="fas fa-user-shield fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" id="current-agent-name">Superviseur IA</h5>
                            <span class="badge bg-success-soft text-success rounded-pill px-3" style="font-size: 0.7rem;">En ligne</span>
                        </div>
                    </div>
                    <button class="btn btn-light rounded-circle shadow-sm" onclick="clearChat()">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>

                <!-- Messages -->
                <div id="chat-messages" class="flex-grow-1 p-4 overflow-auto d-flex flex-column" style="scroll-behavior: smooth;">
                    <!-- Message Bienvenue -->
                    <div class="message-ai mb-4 d-flex align-items-start">
                        <div class="ai-avatar me-3" style="width: 35px; height: 35px; background: #4f46e5; border-radius: 10px; flex-shrink: 0;"></div>
                        <div class="message-bubble p-3 shadow-sm" style="background: white; border-radius: 0 15px 15px 15px; max-width: 80%;">
                            Bonjour ! Je suis votre Superviseur IA. Comment puis-je vous assister aujourd'hui dans la gestion de votre cabinet ?
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="chat-footer p-4" style="background: white;">
                    <form id="chat-form" onsubmit="return handleSendMessage(event)">
                        <div class="input-group p-1 bg-light rounded-pill shadow-sm" style="border: 1px solid #e2e8f0;">
                            <input type="text" id="user-input" class="form-control border-0 bg-transparent px-4 py-2" 
                                   placeholder="Posez votre question ici..." autocomplete="off">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 ms-2" id="btn-send">
                                <i class="fas fa-paper-plane me-2"></i> Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .blur-card {
        border: 1px solid rgba(255, 255, 255, 0.4);
    }
    .agent-item:hover {
        background: rgba(255, 255, 255, 0.9);
        transform: translateX(5px);
    }
    .active-agent {
        background: white !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(79, 70, 229, 0.2);
    }
    .active-agent h6 {
        color: #4f46e5;
    }
    .message-ai .message-bubble {
        border-left: 4px solid #4f46e5;
    }
    .message-user {
        align-self: flex-end;
    }
    .message-user .message-bubble {
        background: #4f46e5 !important;
        color: white;
        border-radius: 15px 15px 0 15px !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .bg-success-soft {
        background-color: #ecfdf5;
    }
    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    #chat-messages::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .typing-indicator {
        display: none;
        font-style: italic;
        color: #64748b;
        font-size: 0.8rem;
    }
</style>

<script>
    let currentAgent = 'superviseur';
    let currentAgentName = 'Superviseur IA';
    let currentAgentColor = '#4f46e5';

    function selectAgent(key, name, color) {
        currentAgent = key;
        currentAgentName = name;
        currentAgentColor = color;

        // UI Updates
        document.querySelectorAll('.agent-item').forEach(el => el.classList.remove('active-agent'));
        document.getElementById('agent-' + key).classList.add('active-agent');
        
        document.getElementById('current-agent-name').innerText = name;
        document.getElementById('current-agent-icon').style.background = color;
        
        const icon = document.querySelector('#agent-' + key + ' i').className;
        document.querySelector('#current-agent-icon i').className = icon + ' fs-4';

        // Message de bienvenue spécifique
        appendAiMessage(`Vous parlez maintenant avec l'expert **${name}**. Comment puis-je vous aider ?`);
    }

    async function handleSendMessage(event) {
        event.preventDefault();
        const input = document.getElementById('user-input');
        const message = input.value.trim();
        
        if (!message) return;

        // 1. Ajouter le message utilisateur à l'UI
        appendUserMessage(message);
        input.value = '';

        // 2. Afficher l'indicateur de chargement
        const loadingId = appendLoadingMessage();

        try {
            const response = await fetch('{{ route("admin.ia.chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: message,
                    agent: currentAgent
                })
            });

            const data = await response.json();
            
            // Retirer l'indicateur
            document.getElementById(loadingId).remove();

            if (data.success) {
                appendAiMessage(data.reponse);
            } else {
                appendAiMessage("⚠️ Désolé, une erreur est survenue : " + data.error);
            }
        } catch (error) {
            document.getElementById(loadingId).remove();
            appendAiMessage("❌ Impossible de contacter l'agent IA. Vérifiez votre connexion.");
        }
    }

    function appendUserMessage(text) {
        const chat = document.getElementById('chat-messages');
        const html = `
            <div class="message-user mb-4 d-flex align-items-start">
                <div class="message-bubble p-3 shadow-sm" style="max-width: 80%;">
                    ${text}
                </div>
            </div>
        `;
        chat.insertAdjacentHTML('beforeend', html);
        scrollToBottom();
    }

    function appendAiMessage(text) {
        const chat = document.getElementById('chat-messages');
        const formattedText = formatMarkdown(text);
        const html = `
            <div class="message-ai mb-4 d-flex align-items-start">
                <div class="ai-avatar me-3 d-none d-sm-block" style="width: 35px; height: 35px; background: ${currentAgentColor}; border-radius: 10px; flex-shrink: 0;"></div>
                <div class="message-bubble p-3 shadow-sm" style="background: white; border-radius: 0 15px 15px 15px; max-width: 80%; border-left: 4px solid ${currentAgentColor};">
                    ${formattedText}
                </div>
            </div>
        `;
        chat.insertAdjacentHTML('beforeend', html);
        scrollToBottom();
    }

    function appendLoadingMessage() {
        const id = 'loading-' + Date.now();
        const chat = document.getElementById('chat-messages');
        const html = `
            <div class="message-ai mb-4 d-flex align-items-start" id="${id}">
                <div class="ai-avatar me-3" style="width: 35px; height: 35px; background: #cbd5e1; border-radius: 10px; flex-shrink: 0;"></div>
                <div class="message-bubble p-3 shadow-sm" style="background: #f1f5f9; border-radius: 0 15px 15px 15px;">
                    <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                    <div class="spinner-grow spinner-grow-sm text-primary mx-1" role="status"></div>
                    <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>
                </div>
            </div>
        `;
        chat.insertAdjacentHTML('beforeend', html);
        scrollToBottom();
        return id;
    }

    function scrollToBottom() {
        const chat = document.getElementById('chat-messages');
        chat.scrollTop = chat.scrollHeight;
    }

    function clearChat() {
        document.getElementById('chat-messages').innerHTML = '';
        appendAiMessage("Historique effacé. Je suis prêt pour une nouvelle demande.");
    }

    // Un formateur Markdown basique pour l'UI
    function formatMarkdown(text) {
        if (!text) return '';
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }
</script>
</script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
