<?php
// includes/header.php
?>
<header class="bg-slate-800 border-b border-slate-700 h-16 flex items-center justify-between px-6 sticky top-0 z-20">
    <h1 class="text-xl font-semibold text-slate-100 tracking-tight">Primus Point - Portal do RH</h1>
    <div class="flex items-center gap-6">
        <!-- Notification Bell -->
        <div class="relative cursor-pointer flex items-center notif-bell" onclick="typeof abrirModalSolicitacoes === 'function' ? abrirModalSolicitacoes() : window.location.href='solicitacoes.php'">
            <svg class="text-slate-400 hover:text-slate-200 transition-colors" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span id="badge-notificacoes" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 flex flex-col items-center justify-center rounded-full" style="display: none;">0</span>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-400">Olá, <strong class="text-slate-100 font-medium"><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Usuário'); ?></strong></span>
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-semibold text-white border-2 border-slate-800 ring-2 ring-slate-700 uppercase">
                <?php echo substr($_SESSION['nome'], 0, 2); ?>
            </div>
        </div>
        <a href="actions/logout_action.php" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Sair</a>
    </div>
</header>
<script>
    // Global notification checker for the bell in header
    async function updateHeaderBell() {
        try {
            const response = await fetch('api_get_solicitacoes.php');
            const data = await response.json();
            const badge = document.getElementById('badge-notificacoes');
            if (data.sucesso && data.count > 0) {
                if (badge) {
                    badge.innerText = data.count;
                    badge.style.display = 'flex';
                }
            } else {
                if (badge) badge.style.display = 'none';
            }
        } catch (e) {
            console.error('Erro silent notif:', e);
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        updateHeaderBell();
        setInterval(updateHeaderBell, 15000);
    });
</script>
