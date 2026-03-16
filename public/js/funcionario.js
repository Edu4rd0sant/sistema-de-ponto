// funcionario.js - Enhanced with PWA, Offline mode, Sync and Feedback
let db;
const DB_NAME = 'PrimusPointDB';
const STORE_NAME = 'pendingRegistrations';

document.addEventListener('DOMContentLoaded', () => {
    iniciarRelogio();
    initDB();
    carregarPontoHoje();

    const btnRegistrar = document.querySelector('.btn-register');
    if (btnRegistrar) {
        btnRegistrar.addEventListener('click', registrarPonto);
    }

    // Escuta por volta da conexão
    window.addEventListener('online', syncOfflineData);

    // Iniciar checagem de notificações do funcionário
    checarNotificacoesFunc();
    setInterval(checarNotificacoesFunc, 10000); // Poll a cada 10s
});

// --- IndexedDB Setup ---
function initDB() {
    const request = indexedDB.open(DB_NAME, 1);
    request.onupgradeneeded = (e) => {
        const db = e.target.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
            db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
        }
    };
    request.onsuccess = (e) => {
        db = e.target.result;
        syncOfflineData(); // Tenta sincronizar ao iniciar
    };
    request.onerror = (e) => console.error('Erro ao abrir IndexedDB', e);
}

function saveOffline(data) {
    const transaction = db.transaction([STORE_NAME], 'readwrite');
    const store = transaction.objectStore(STORE_NAME);
    store.add(data);
}

async function syncOfflineData() {
    if (!navigator.onLine || !db) return;

    const transaction = db.transaction([STORE_NAME], 'readwrite');
    const store = transaction.objectStore(STORE_NAME);
    const request = store.getAll();

    request.onsuccess = async () => {
        const pending = request.result;
        if (pending.length === 0) return;

        console.log(`Sincronizando ${pending.length} registros offline...`);
        
        for (const item of pending) {
            try {
                const response = await fetch('/ponto/registrar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item)
                });
                const resData = await response.json();
                if (resData.sucesso) {
                    // Remove do DB local após sucesso
                    const deleteTx = db.transaction([STORE_NAME], 'readwrite');
                    deleteTx.objectStore(STORE_NAME).delete(item.id);
                }
            } catch (e) {
                console.error('Falha ao sincronizar item', item, e);
            }
        }
        carregarPontoHoje();
    };
}

// --- UI & Clock ---
function iniciarRelogio() {
    const clockDisplay = document.querySelector('.clock-display');
    const dateDisplay = document.querySelector('.date-display');
    
    if (!clockDisplay || !dateDisplay) return;

    const atualizar = () => {
        const agora = new Date();
        const horas = String(agora.getHours()).padStart(2, '0');
        const minutos = String(agora.getMinutes()).padStart(2, '0');
        clockDisplay.innerText = `${horas}:${minutos}`;
        
        const dia = String(agora.getDate()).padStart(2, '0');
        const meses = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        const mes = meses[agora.getMonth()];
        const ano = agora.getFullYear();
        dateDisplay.innerText = `${dia} de ${mes} de ${ano}`;
    };

    atualizar();
    setInterval(atualizar, 1000);
}

async function carregarPontoHoje() {
    if (!navigator.onLine) {
         // Se offline, poderíamos tentar ler do cache ou simplesmente avisar
         return;
    }
    try {
        const response = await fetch('/api/ponto/hoje');
        const data = await response.json();
        if (data.sucesso) {
            if (typeof renderizarRegistros === 'function') {
                renderizarRegistros(data.data);
            }
            if (typeof atualizarStatusDoBotao === 'function') {
                atualizarStatusDoBotao(data.data.length);
            }
        }
    } catch (e) {
        console.error("Erro ao carregar o ponto de hoje:", e);
    }
}

// --- Geolocation & Camera Helpers ---

function getGeolocation() {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            console.warn("Geolocalização não suportada");
            return resolve(null);
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
            (err) => {
                console.warn("Erro ao obter localização", err);
                resolve(null);
            },
            { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
        );
    });
}

function startCamera() {
    const modal = document.getElementById('modal-camera');
    const video = document.getElementById('video-preview');
    modal.classList.remove('hidden');
    
    return navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false })
        .then(stream => {
            video.srcObject = stream;
            return stream;
        })
        .catch(err => {
            console.error("Erro ao acessar câmera", err);
            modal.classList.add('hidden');
            showToast("Erro ao acessar a câmera. Verifique as permissões.", "error");
            throw err;
        });
}

function stopCamera(stream) {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
    document.getElementById('modal-camera').classList.add('hidden');
}

function takeSnapshot() {
    const video = document.getElementById('video-preview');
    const canvas = document.getElementById('canvas-capture');
    const context = canvas.getContext('2d');
    
    // Configura o canvas para o mesmo tamanho do vídeo (ou quadrado se preferir)
    const size = Math.min(video.videoWidth, video.videoHeight);
    canvas.width = 640;
    canvas.height = 640;
    
    // Inverte o contexto para compensar o preview espelhado
    context.translate(640, 0);
    context.scale(-1, 1);
    
    // Desenha o frame atual do vídeo no canvas
    context.drawImage(video, (video.videoWidth - size) / 2, (video.videoHeight - size) / 2, size, size, 0, 0, 640, 640);
    
    return canvas.toDataURL('image/jpeg', 0.8);
}

// --- Main Action ---
async function registrarPonto() {
    const btn = document.querySelector('.btn-register');
    const originalContent = btn.innerHTML;
    
    // 1. Capturar Localização (Assíncrono, não bloqueia UI ainda)
    const location = await getGeolocation();

    // 2. Abrir Câmera e Modal
    let stream;
    try {
        stream = await startCamera();
    } catch (e) {
        return; // Caso falhe o acesso à câmera
    }

    // 3. Aguardar clique no botão "Tirar Foto"
    return new Promise((resolve) => {
        const btnSnapshot = document.getElementById('btn-snapshot');
        const btnCancel = document.getElementById('btn-cancel-camera');

        const onCancel = () => {
            stopCamera(stream);
            btnSnapshot.removeEventListener('click', onSnapshot);
            btnCancel.removeEventListener('click', onCancel);
            resolve();
        };

        const onSnapshot = async () => {
            const photo = takeSnapshot();
            stopCamera(stream);
            btnSnapshot.removeEventListener('click', onSnapshot);
            btnCancel.removeEventListener('click', onCancel);

            // Agora sim procede com o envio
            btn.disabled = true;
            btn.innerText = "REGISTRANDO...";

            const agora = new Date();
            const dataHoraFormatted = agora.getFullYear() + '-' + 
                String(agora.getMonth() + 1).padStart(2, '0') + '-' + 
                String(agora.getDate()).padStart(2, '0') + ' ' + 
                String(agora.getHours()).padStart(2, '0') + ':' + 
                String(agora.getMinutes()).padStart(2, '0') + ':' + 
                String(agora.getSeconds()).padStart(2, '0');

            const payload = {
                data_hora: dataHoraFormatted,
                latitude: location ? location.lat : null,
                longitude: location ? location.lng : null,
                foto: photo
            };

            if (!navigator.onLine) {
                saveOffline(payload);
                playFeedbackSound();
                showToast("Ponto registrado OFFLINE com foto e localização.", "info");
                btn.innerHTML = originalContent;
                btn.disabled = false;
                return resolve();
            }

            try {
                const response = await fetch('/ponto/registrar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                
                if (data.sucesso) {
                    playFeedbackSound();
                    showToast(data.mensagem, "success");
                    carregarPontoHoje();
                } else {
                    showToast("Erro: " + data.erro, "error");
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (e) {
                console.error("Erro na requisição:", e);
                saveOffline(payload);
                playFeedbackSound();
                showToast("Conexão instável. Ponto salvo localmente.", "info");
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
            resolve();
        };

        btnSnapshot.addEventListener('click', onSnapshot);
        btnCancel.addEventListener('click', onCancel);
    });
}

// --- Feedback Helpers ---
function playFeedbackSound() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.1, audioCtx.currentTime + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);

        oscillator.start();
        oscillator.stop(audioCtx.currentTime + 0.5);
    } catch (e) {
        console.warn("Audio Context não suportado ou bloqueado.", e);
    }
}

function showToast(message, type) {
    // Remove toast anterior se houver
    const oldToast = document.getElementById('primus-toast');
    if (oldToast) oldToast.remove();

    const toast = document.createElement('div');
    toast.id = 'primus-toast';
    
    const colors = {
        success: 'bg-emerald-600',
        error: 'bg-red-600',
        info: 'bg-blue-600'
    };

    toast.className = `fixed bottom-10 left-1/2 -translate-x-1/2 ${colors[type] || 'bg-slate-800'} text-white px-6 py-3 rounded-full shadow-2xl z-50 flex items-center gap-3 animate-bounce`;
    
    let icon = 'ph-info';
    if (type === 'success') icon = 'ph-check-circle';
    if (type === 'error') icon = 'ph-warning-circle';

    toast.innerHTML = `<i class="ph ${icon} text-xl"></i> <span class="font-medium">${message}</span>`;
    
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('animate-bounce');
        toast.classList.add('transition-opacity', 'duration-500', 'opacity-0');
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

// --- Notificações do Funcionário (HR Approvals) ---
async function checarNotificacoesFunc() {
    try {
        const response = await fetch('/api/funcionario/notificacoes');
        const data = await response.json();
        
        const badge = document.getElementById('badge-funcionario-notificacoes');
        if (!badge) return;

        if (data.sucesso && data.count > 0) {
            badge.innerText = data.count > 9 ? '9+' : data.count;
            badge.classList.remove('scale-0');
            badge.classList.add('scale-100');
            
            // Emite um alerta visual no ícone
            const bellIcon = document.querySelector('.notif-bell i');
            if (bellIcon) {
                bellIcon.classList.add('text-red-400');
                setTimeout(() => bellIcon.classList.remove('text-red-400'), 1000);
            }
            
            // Popula o modal de notificações funcionário
            const listaModal = document.getElementById('lista-notificacoes-funcionario');
            if (listaModal && data.data) {
                let html = '<ul class="space-y-3">';
                data.data.forEach(sol => {
                    const statusColor = sol.status === 'aprovada' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-red-400 bg-red-500/10 border-red-500/20';
                    const statusIcon = sol.status === 'aprovada' ? 'ph-check-circle' : 'ph-x-circle';
                    const statusText = sol.status === 'aprovada' ? 'Deferido' : 'Indeferido';
                    const dataFormatada = new Date(sol.atualizada_em).toLocaleString('pt-BR', {hour: '2-digit', minute:'2-digit', day:'2-digit', month:'short'});
                    
                    html += `
                        <li class="bg-slate-900/50 border border-slate-700/50 rounded-lg p-4 flex gap-4 transition-colors">
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-1">
                                    <h4 class="text-sm font-medium text-slate-200 capitalize">${sol.tipo.replace('_', ' ')}</h4>
                                    <span class="text-[11px] text-slate-500 whitespace-nowrap">${dataFormatada}</span>
                                </div>
                                <p class="text-xs text-slate-400 italic line-clamp-2 mb-3">"${sol.descricao}"</p>
                                
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center gap-1 py-1 px-2.5 rounded-md text-xs font-semibold ${statusColor} border">
                                        <i class="ph ${statusIcon} text-sm"></i> ${statusText}
                                    </span>
                                </div>
                                
                                ${sol.motivo_recusa ? `<div class="mt-2 text-xs text-red-400 bg-red-950/30 p-2 rounded border border-red-500/20"><strong>Motivo:</strong> ${sol.motivo_recusa}</div>` : ''}
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                listaModal.innerHTML = html;
            }
        } else {
            badge.classList.remove('scale-100');
            badge.classList.add('scale-0');
            
            const listaModal = document.getElementById('lista-notificacoes-funcionario');
            if (listaModal) {
                 listaModal.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-slate-500 py-8">
                        <i class="ph ph-tray text-4xl mb-2 opacity-50"></i>
                        <p class="text-sm text-center">Nenhuma atualização no momento.</p>
                    </div>`;
            }
        }
    } catch (error) {
        // Silêncio em erros de polling
    }
}
