<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Génération du Grand Livre | ComptaFlow</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            overflow: hidden;
        }

        /* Effet de lumières en arrière-plan */
        .bg-glow-1 {
            position: absolute;
            top: 20%;
            left: 20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            z-index: 1;
            filter: blur(50px);
            animation: floatGlow 15s infinite alternate ease-in-out;
        }

        .bg-glow-2 {
            position: absolute;
            bottom: 20%;
            right: 20%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.12) 0%, transparent 70%);
            z-index: 1;
            filter: blur(60px);
            animation: floatGlow 18s infinite alternate-reverse ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 30px) scale(1.1); }
        }

        /* Glassmorphism Card */
        .loading-card {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 50px 40px;
            width: 90%;
            max-width: 520px;
            text-align: center;
            z-index: 10;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo/Branding */
        .branding {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 35px;
        }

        .branding i {
            background: linear-gradient(135deg, #6366f1 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 24px;
        }

        .branding span {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Circular Spinner */
        .spinner-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 35px;
        }

        .outer-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 3px solid rgba(99, 102, 241, 0.05);
            border-radius: 50%;
        }

        .spinning-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 3px solid transparent;
            border-top: 3px solid #6366f1;
            border-right: 3px solid #ec4899;
            border-radius: 50%;
            animation: spin 1.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 40px;
            color: #6366f1;
            text-shadow: 0 0 20px rgba(99, 102, 241, 0.4);
            animation: pulseIcon 2s infinite ease-in-out;
        }

        @keyframes pulseIcon {
            0%, 100% { opacity: 0.8; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.08); }
        }

        /* Typography & Messages */
        h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        /* Progress Steps */
        .progress-box {
            background: rgba(15, 23, 42, 0.3);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.03);
            text-align: left;
            margin-bottom: 25px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            font-size: 13.5px;
            color: #64748b;
            transition: all 0.4s ease;
        }

        .step:last-child {
            margin-bottom: 0;
        }

        .step i {
            font-size: 12px;
            width: 16px;
            text-align: center;
        }

        .step.active {
            color: #6366f1;
            font-weight: 600;
        }

        .step.active i {
            animation: blink 1s infinite alternate;
        }

        .step.completed {
            color: #10b981;
        }

        @keyframes blink {
            0% { opacity: 0.4; }
            100% { opacity: 1; }
        }

        /* Safe Action Info */
        .info-bar {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.02);
            padding: 10px;
            border-radius: 12px;
        }

        .info-bar i {
            color: #f59e0b;
        }
    </style>
</head>
<body>

    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="loading-card">
        
        <!-- Branding logo -->
        <div class="branding">
            <i class="fa-solid fa-wind"></i>
            <span>ComptaFlow</span>
        </div>

        <!-- Animated Spinner -->
        <div class="spinner-container">
            <div class="outer-ring"></div>
            <div class="spinning-ring"></div>
            <div class="spinner-icon">
                <i class="fa-solid fa-file-pdf"></i>
            </div>
        </div>

        <!-- Titles -->
        <h1>Génération du Grand Livre</h1>
        <p class="subtitle">Votre rapport comptable est en cours de compilation sécurisée en arrière-plan. Cela évite les lenteurs d'affichage.</p>

        <!-- Progress Steps -->
        <div class="progress-box">
            <div class="step" id="step-1"><i class="fa-solid fa-circle-notch fa-spin"></i> Initialisation des données comptables</div>
            <div class="step" id="step-2"><i class="fa-solid fa-circle-notch"></i> Analyse et regroupement par comptes</div>
            <div class="step" id="step-3"><i class="fa-solid fa-circle-notch"></i> Calcul des reports de soldes</div>
            <div class="step" id="step-4"><i class="fa-solid fa-circle-notch"></i> Finalisation et édition du PDF</div>
        </div>

        <!-- Security Info -->
        <div class="info-bar">
            <i class="fa-solid fa-circle-info"></i>
            <span>Ne fermez pas cette page. Téléchargement automatique.</span>
        </div>

    </div>

    <script>
        const filename = "{{ $filename }}";
        const type = "{{ $type }}";
        const redirectUrl = "{{ $redirect }}";

        let currentStep = 1;

        // Met à jour l'état visuel des étapes de chargement
        function updateSteps() {
            if (currentStep === 1) {
                setStepActive(1);
            } else if (currentStep === 2) {
                setStepCompleted(1);
                setStepActive(2);
            } else if (currentStep === 3) {
                setStepCompleted(2);
                setStepActive(3);
            } else if (currentStep === 4) {
                setStepCompleted(3);
                setStepActive(4);
            }
        }

        function setStepActive(stepNum) {
            const el = document.getElementById(`step-${stepNum}`);
            if (el) {
                el.className = 'step active';
                el.querySelector('i').className = 'fa-solid fa-circle-notch fa-spin';
            }
        }

        function setStepCompleted(stepNum) {
            const el = document.getElementById(`step-${stepNum}`);
            if (el) {
                el.className = 'step completed';
                el.querySelector('i').className = 'fa-solid fa-circle-check';
            }
        }

        // Cycle de messages simulé pour l'utilisateur pendant l'attente du vrai fichier
        const stepInterval = setInterval(() => {
            if (currentStep < 4) {
                currentStep++;
                updateSteps();
            }
        }, 3500);

        // Appel de vérification Ajax régulier
        function checkFileStatus() {
            fetch(`/accounting_ledger/check-status?file=${filename}&type=${type}`)
                .then(response => response.json())
                .then(data => {
                    if (data.completed) {
                        clearInterval(stepInterval);
                        
                        // Tout est complété !
                        setStepCompleted(1);
                        setStepCompleted(2);
                        setStepCompleted(3);
                        setStepCompleted(4);

                        // Lancement immédiat du téléchargement
                        const link = document.createElement('a');
                        link.href = data.download_url;
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Redirection finale après 1.5s
                        setTimeout(() => {
                            window.location.href = redirectUrl;
                        }, 1500);
                    }
                })
                .catch(error => {
                    console.error("Erreur de statut:", error);
                });
        }

        // Lancement du polling toutes les 2.5 secondes
        updateSteps();
        setInterval(checkFileStatus, 2500);
    </script>
</body>
</html>
