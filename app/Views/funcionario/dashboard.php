<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Funcionário - Primus Point</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/img/icon-192.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-900 text-slate-200 font-sans min-h-screen">
    <div class="flex w-full">
        <!-- ÁREA LATERAL (SIDEBAR) -->
        <?php include __DIR__ . '/../../../includes/sidebar_func.php'; ?>

        <!-- ÁREA DE CONTEÚDO PRINCIPAL (MAIN CONTENT) -->
        <main class="flex-1 lg:ml-64 flex flex-col min-h-screen relative overflow-hidden">
            <!-- Fundo decorativo sutil -->
            <div class="absolute top-[10%] left-[20%] w-[600px] h-[600px] bg-blue-900/10 blur-[150px] rounded-full pointer-events-none z-0"></div>
            
            <!-- HEADER -->
            <?php include __DIR__ . '/../../../includes/header_func.php'; ?>

            <!-- DASHBOARD CONTAINER -->
            <div class="p-8 pb-12 flex-1 relative z-10 flex flex-col items-center justify-center">
                
                <div class="w-full max-w-6xl grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- CARD 1: Seu Banco de Horas -->
                    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                        <h2 class="text-lg font-semibold text-white tracking-tight mb-2 flex items-center gap-2">
                            <i class="ph ph-bank text-blue-400"></i> Seu Banco de Horas
                        </h2>
                        
                        <!-- Gráfico de Semicírculo Simulado com Tailwind -->
                        <div class="relative w-full aspect-[2/1] mt-4 mb-2 flex flex-col items-center justify-end overflow-hidden">
                            <!-- SVG puro para simular perfeitamente a barra de progresso -->
                            <svg viewBox="0 0 200 110" class="w-[85%] absolute bottom-0">
                                <!-- Arco de Fundo Escuro -->
                                <path d="M 10,100 A 90,90 0 0,1 190,100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="15" stroke-linecap="round"/>
                                <!-- Arco de Progresso (Simulado vazio) -->
                                <path d="M 10,100 A 90,90 0 0,1 10,100" fill="none" stroke="#3b82f6" stroke-width="15" stroke-linecap="round" stroke-dasharray="283" stroke-dashoffset="283" class="transition-all duration-1000 ease-out"/>
                            </svg>
                            <div class="text-center z-10 mb-2">
                                <div class="text-3xl font-bold text-white tracking-tight">0h <span class="text-xl text-slate-400">00m</span></div>
                                <div class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1">Sem Saldo</div>
                            </div>
                        </div>

                        <!-- Lista horizontal com histórico de meses -->
                        <div class="flex justify-between items-center bg-slate-900/50 rounded-lg p-3 border border-slate-700/50 mt-4">
                            <div class="text-center">
                                <span class="block text-xs font-semibold text-slate-400 uppercase">Jul</span>
                                <span class="block text-sm font-medium text-slate-200">0h 00m</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-xs font-semibold text-slate-400 uppercase">Ago</span>
                                <span class="block text-sm font-medium text-slate-200">0h 00m</span>
                            </div>
                            <div class="text-center">
                                <span class="block text-xs font-semibold text-slate-400 uppercase">Set</span>
                                <span class="block text-sm font-medium text-slate-200">0h 00m</span>
                            </div>
                            <div class="text-center opacity-50">
                                <span class="block text-xs font-semibold text-slate-400 uppercase">Out</span>
                                <span class="block text-sm font-medium text-slate-200">--</span>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: Registro de Ponto Centralizado -->
                    <div class="bg-gradient-to-b from-blue-900/40 to-slate-800/80 backdrop-blur-xl border border-blue-500/20 rounded-2xl p-8 shadow-2xl flex flex-col items-center justify-center relative overflow-hidden ring-1 ring-inset ring-white/5">
                        <!-- Efeito de brilho de fundo -->
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[200px] h-[100px] bg-blue-500/20 blur-[60px] rounded-full pointer-events-none"></div>

                        <h2 class="text-sm font-medium text-blue-400 uppercase tracking-widest mb-6">Registro de Ponto</h2>
                        
                        <div class="clock-display text-5xl sm:text-6xl font-black text-white tracking-tighter mb-1 drop-shadow-md">00:00</div>
                        <div class="date-display text-base font-medium text-slate-400 mb-8">-- de --</div>
                        
                        <button class="btn-register w-full max-w-[280px] sm:max-w-xs h-20 sm:h-auto bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(37,99,235,0.4)] transition-all hover:-translate-y-1 hover:shadow-[0_0_25px_rgba(37,99,235,0.6)] active:translate-y-0 active:scale-95 flex flex-col sm:flex-row items-center justify-center gap-2 mb-6">
                            <i class="ph ph-fingerprint text-3xl sm:text-xl"></i> 
                            <span class="text-lg sm:text-base">REGISTRAR AGORA</span>
                        </button>
                        
                        <div class="last-record-notice bg-slate-900/60 border border-slate-700/50 rounded-full px-4 py-1.5 text-xs text-slate-300 font-medium flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500 animate-pulse"></span>
                            <span id="label-ultimo-registro">Sem registros hoje</span>
                        </div>
                    </div>

                    <!-- CARD 3: Meus Últimos Registros -->
                    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl flex flex-col">
                        <h2 class="text-lg font-semibold text-white tracking-tight mb-4 flex items-center gap-2">
                            <i class="ph ph-list-dashes text-slate-400"></i> Registros de Hoje
                        </h2>
                        
                        <ul class="records-list flex-1 flex flex-col gap-3 overflow-y-auto pr-1">
                            <!-- Será preenchido pelo funcionario.js -->
                            <li class="flex items-center justify-center h-full text-slate-500 text-sm">
                                <div class="flex flex-col items-center">
                                    <i class="ph ph-spinner-gap animate-spin text-2xl mb-2"></i>
                                    Carregando registros...
                                </div>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </main>
    </div>
    
    <!-- Script modificado para Tailwind -->
    <script>
        // Intercepta e redefine as funções de renderização do funcionario.js para não usar as antigas strings HTML do admin.css
        document.addEventListener('DOMContentLoaded', () => {
            // Backup the original function if we want, but we just override it
            window.renderizarRegistros = function(registros) {
                const listContainer = document.querySelector('.records-list');
                const lastRecordNotice = document.getElementById('label-ultimo-registro');
                const dotStatus = document.querySelector('.last-record-notice span:first-child');
                
                if (!listContainer || !lastRecordNotice) return;
                
                const mapTipos = {
                    'entrada': { label: 'Entrada', icon: 'ph-sign-in', color: 'text-emerald-400 bg-emerald-500/10' },
                    'saida_almoco': { label: 'Saída Almoço', icon: 'ph-fork-knife', color: 'text-orange-400 bg-orange-500/10' },
                    'retorno_almoco': { label: 'Retorno Almoço', icon: 'ph-arrow-u-down-left', color: 'text-blue-400 bg-blue-500/10' },
                    'saida': { label: 'Saída Expediente', icon: 'ph-sign-out', color: 'text-purple-400 bg-purple-500/10' }
                };

                if (registros.length === 0) {
                    listContainer.innerHTML = `
                        <li class="flex flex-col items-center justify-center h-full text-slate-500 py-10 opacity-70">
                            <i class="ph ph-clock-dashed text-4xl mb-2"></i>
                            <span class="text-sm font-medium">Nenhum registro ainda hoje.</span>
                        </li>
                    `;
                    lastRecordNotice.innerText = "Sem registros hoje";
                    if(dotStatus) {
                        dotStatus.classList.remove('bg-emerald-500');
                        dotStatus.classList.add('bg-slate-500');
                    }
                    return;
                }

                let html = '';
                registros.forEach((r, idx) => {
                    const tipoConfig = mapTipos[r.tipo] || { label: r.tipo, icon: 'ph-clock', color: 'text-slate-400 bg-slate-500/10' };
                    
                    // Conecta as linhas, exceto no último item (para dar o visual de timeline)
                    const isLast = idx === registros.length - 1;
                    const lineHtml = !isLast ? `<div class="absolute left-5 top-10 bottom-[-12px] w-px bg-slate-700 z-0"></div>` : '';

                    html += `
                        <li class="relative flex items-center p-3 rounded-xl bg-slate-900/40 border border-slate-800 hover:bg-slate-800 transition-colors">
                            ${lineHtml}
                            <div class="relative z-10 w-10 h-10 rounded-lg flex items-center justify-center ${tipoConfig.color} shadow-inner mr-4">
                                <i class="ph ${tipoConfig.icon} text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-slate-200">${tipoConfig.label}</div>
                                <div class="text-xs text-slate-500">Registrado via Web</div>
                            </div>
                            <div class="text-lg font-bold text-white tracking-tight">${r.hora}</div>
                        </li>
                    `;
                });
                
                listContainer.innerHTML = html;

                // Atualiza o notice inferior
                const ultimo = registros[registros.length - 1];
                const ultimoConfig = mapTipos[ultimo.tipo] || { label: ultimo.tipo };
                lastRecordNotice.innerHTML = `Último registro: <strong class="text-white">${ultimoConfig.label} às ${ultimo.hora}</strong>`;
                
                if(dotStatus) {
                    dotStatus.classList.remove('bg-slate-500');
                    dotStatus.classList.add('bg-emerald-500');
                }
            };
            
            // Override botão status pra Tailwind classes
            window.atualizarStatusDoBotao = function(qtdRegistros) {
                const btn = document.querySelector('.btn-register');
                if (!btn) return;
                
                if (qtdRegistros >= 4) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="ph ph-check-circle text-xl"></i> JORNADA CONCLUÍDA';
                    // Remove blue classes, add gray classes
                    btn.className = 'btn-register w-full max-w-[280px] bg-slate-700/50 text-slate-400 font-bold py-4 rounded-xl cursor-not-allowed border border-slate-600/50 flex items-center justify-center gap-2 mb-6';
                    
                    // Zera pulse do circulo verde abaixo do relogio
                    const dotStatus = document.querySelector('.last-record-notice span:first-child');
                    if(dotStatus) dotStatus.classList.remove('animate-pulse');
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-fingerprint text-xl"></i> REGISTRAR AGORA';
                    btn.className = 'btn-register w-full max-w-[280px] bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-[0_0_20px_rgba(37,99,235,0.4)] transition-all hover:-translate-y-1 hover:shadow-[0_0_25px_rgba(37,99,235,0.6)] active:translate-y-0 active:scale-95 flex items-center justify-center gap-2 mb-6';
                    
                    const dotStatus = document.querySelector('.last-record-notice span:first-child');
                    if(dotStatus) dotStatus.classList.add('animate-pulse');
                }
            };
        });
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('service-worker.js')
                    .then(reg => console.log('Service Worker registrado!', reg))
                    .catch(err => console.log('Erro ao registrar SW:', err));
            });
        }
    </script>
    <!-- MODAL DE CAPTURA DE SELFIE -->
    <div id="modal-camera" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-sm">
        <div class="bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl overflow-hidden shadow-2xl">
            <div class="p-4 border-b border-slate-700 flex justify-between items-center">
                <h3 class="text-white font-semibold flex items-center gap-2">
                    <i class="ph ph-camera"></i> Identificação por Selfie
                </h3>
            </div>
            <div class="relative aspect-square bg-black flex items-center justify-center overflow-hidden">
                <video id="video-preview" class="absolute inset-0 w-full h-full object-cover -scale-x-100" autoplay playsinline></video>
                <div class="absolute inset-0 border-4 border-blue-500/30 rounded-full m-8 pointer-events-none"></div>
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-full h-px bg-white/10"></div>
                    <div class="h-full w-px bg-white/10 absolute"></div>
                </div>
            </div>
            <div class="p-6 flex flex-col gap-3">
                <p class="text-xs text-slate-400 text-center">Posicione seu rosto no centro do círculo e certifique-se de estar em um local iluminado.</p>
                <button id="btn-snapshot" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 rounded-xl transition-all flex items-center justify-center gap-2 shadow-lg shadow-blue-600/20">
                    <i class="ph ph-aperture text-xl"></i> TIRAR FOTO
                </button>
                <button id="btn-cancel-camera" class="w-full bg-transparent hover:bg-slate-700 text-slate-400 py-2 rounded-lg text-sm transition-colors">
                    CANCELAR
                </button>
            </div>
        </div>
    </div>

    <!-- CANVAS OCULTO PARA CAPTURA -->
    <canvas id="canvas-capture" width="640" height="640" class="hidden"></canvas>

    <script src="/js/funcionario.js"></script>
</body>
</html>




