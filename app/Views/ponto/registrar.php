<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logado'])) {
    header("Location: ../../../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bater Ponto - App</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .header {
            background-color: #0d6efd;
            color: white;
            padding: 15px. 20px;
            border-bottom-left-radius: 20px;
            border-bottom-right-radius: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .camera-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: #000;
            position: relative;
        }
        video {
            width: 100%;
            border-radius: 15px;
            display: block;
        }
        .bater-ponto-btn {
            font-size: 1.3rem;
            padding: 15px 30px;
            border-radius: 50px;
            letter-spacing: 1px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.4);
            transition: all 0.2s ease;
        }
        .bater-ponto-btn:active {
            transform: scale(0.96);
        }
    </style>
</head>
<body>

    <div class="header text-center pt-4 pb-3 mb-4">
        <h4 class="mb-0 fw-bold">Registro de Ponto</h4>
        <p class="mb-0 text-white-50"><small>Olá, <?= htmlspecialchars($_SESSION['nome_usuario'] ?? 'Usuário') ?></small></p>
    </div>

    <div class="container pb-5">
        <div class="text-center mb-3">
            <h1 class="display-3 fw-bold text-primary" id="relogio">00:00:00</h1>
            <p class="text-muted" id="dataAtual">Carregando data...</p>
        </div>

        <!-- Câmera Container -->
        <div class="camera-container mb-4">
            <video id="videoElement" autoplay playsinline muted></video>
            <canvas id="canvasElement" style="display:none;"></canvas>
        </div>

        <!-- Botão principal -->
        <div class="d-grid gap-2 px-3">
            <button id="btnBaterPonto" class="btn btn-primary btn-lg bater-ponto-btn d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-fingerprint" style="font-size: 1.5rem;"></i>
                <span id="btnText">REGISTRAR PONTO</span>
            </button>
            <a href="../../../index.php" class="btn btn-outline-secondary mt-2 rounded-pill">Voltar ao Painel</a>
        </div>
    </div>

    <!-- Inicialização -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

    <script>
        // Relogio
        function atualizarRelogio() {
            const agora = new Date();
            document.getElementById('relogio').innerText = agora.toLocaleTimeString('pt-BR');
            document.getElementById('dataAtual').innerText = agora.toLocaleDateString('pt-BR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(atualizarRelogio, 1000);
        atualizarRelogio();

        // Configuração Vídeo
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvasElement');
        const btnBaterPonto = document.getElementById('btnBaterPonto');

        async function initCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user' } 
                });
                video.srcObject = stream;
            } catch (err) {
                Swal.fire('Aviso', 'Não foi possível acessar a câmera. Você não poderá salvar a selfie.', 'warning');
            }
        }
        initCamera();

        // Envio Assíncrono via Fetch API
        btnBaterPonto.addEventListener('click', async () => {
            // Loader State
            const btnOriginalText = btnBaterPonto.innerHTML;
            btnBaterPonto.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...';
            btnBaterPonto.disabled = true;

            try {
                // 1. Tira a Foto se tiver câmera
                let fotoBase64 = null;
                if (video.srcObject) {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    canvas.getContext('2d').drawImage(video, 0, 0);
                    fotoBase64 = canvas.toDataURL('image/jpeg', 0.8);
                }

                // 2. Tenta conseguir a geolocalização
                const pos = await new Promise((resolve, reject) => {
                    if (!navigator.geolocation) {
                        reject(new Error('Geolocalização não suportada.'));
                    }
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    });
                });

                const latitude = pos.coords.latitude;
                const longitude = pos.coords.longitude;

                // 3. Montar Payload JSON (Simulando uma batida MVC /api/ponto/registrar)
                const payload = {
                    latitude: latitude,
                    longitude: longitude,
                    foto: fotoBase64
                };

                // Puxaremos o caminho dinamicamente (base baseado na rota atual)
                // /public/index.php/ponto/registrar -> ../api/ponto/registrar
                const fetchUrl = '../api/ponto/registrar';

                // 4. Enviar via Fetch
                const response = await fetch(fetchUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                let responseData;
                try {
                    responseData = await response.json();
                } catch(e) {
                    throw new Error('Retorno inválido do servidor');
                }

                if (response.ok && responseData.sucesso) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: responseData.mensagem || 'Ponto registrado com sucesso.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(responseData.erro || 'Não foi possível processar seu registro.');
                }

            } catch (error) {
                // Tratamento de falhas (GPS negado, Geofencing falhou, etc)
                console.error("Erro Ponto:", error);
                
                let errorMsg = error.message;
                if(error.code === 1) errorMsg = "Permissão de Localização negada. Ative o GPS para registrar o ponto.";
                
                Swal.fire({
                    icon: 'error',
                    title: 'Ops!',
                    text: errorMsg,
                    confirmButtonColor: '#0d6efd'
                });
            } finally {
                // Retorna Botão
                btnBaterPonto.innerHTML = btnOriginalText;
                btnBaterPonto.disabled = false;
            }
        });

    </script>
</body>
</html>



