<?php
// includes/header_func.php
?>
<header class="bg-slate-800 border-b border-slate-700 h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 text-slate-400 hover:text-white transition-colors">
            <i class="ph ph-list text-2xl"></i>
        </button>
        <h1 class="text-lg sm:text-xl font-semibold text-slate-100 tracking-tight truncate">Primus Point</h1>
    </div>
    <div class="flex items-center gap-6">
        <a href="javascript:void(0)" onclick="abrirModalNotificacoes()" class="relative text-slate-400 hover:text-white transition-colors notif-bell">
            <i class="ph ph-bell text-2xl"></i>
            <span id="badge-funcionario-notificacoes" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center transform scale-0 transition-transform">0</span>
        </a>
        <div class="hidden sm:flex items-center gap-3">
            <span class="text-sm text-slate-400">Olá, <strong class="text-slate-100 font-medium"><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></strong></span>
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-semibold text-white border-2 border-slate-800 ring-2 ring-slate-700 uppercase">
                <?php echo substr($_SESSION['usuario_nome'] ?? 'U', 0, 2); ?>
            </div>
        </div>
        <a href="/logout" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Sair</a>
    </div>
</header>

<!-- MODAL DE NOTIFICAÇÕES (FUNCIONÁRIO) -->
<div id="modalNotificacoesFuncionario" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
    <div class="bg-slate-800 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 modal-nt-content flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800 shrink-0">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2"><i class="ph ph-bell-ringing text-blue-400"></i> Atualizações do RH</h2>
            <button onclick="fecharModalNotificacoes()" class="text-slate-400 hover:text-red-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
        </div>
        <div class="p-6 overflow-y-auto" id="lista-notificacoes-funcionario">
            <!-- Render via JS -->
            <div class="h-full flex flex-col items-center justify-center text-slate-500 py-8">
                <i class="ph ph-tray text-4xl mb-2 opacity-50"></i>
                <p class="text-sm text-center">Nenhuma atualização no momento.</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    
    if (sidebar.classList.contains('-translate-x-full')) {
        // Abrir
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        setTimeout(() => backdrop.classList.add('opacity-100'), 10);
    } else {
        // Fechar
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.remove('opacity-100');
        setTimeout(() => backdrop.classList.add('hidden'), 300);
    }
}

function abrirModalNotificacoes() {
    const modal = document.getElementById('modalNotificacoesFuncionario');
    const content = modal.querySelector('.modal-nt-content');
    if (modal && content) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
            content.classList.add('scale-100');
        }, 10);
    }
}

function fecharModalNotificacoes() {
    const modal = document.getElementById('modalNotificacoesFuncionario');
    const content = modal.querySelector('.modal-nt-content');
    if (modal && content) {
        modal.classList.add('opacity-0');
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }
    
    // Marcar como lidas ao fechar o modal
    fetch('/api/funcionario/notificacoes/ler', { method: 'POST' }).then(() => {
        const badge = document.getElementById('badge-funcionario-notificacoes');
        if (badge) {
            badge.classList.remove('scale-100');
            badge.classList.add('scale-0');
        }
    }).catch(e => console.error(e));
}
</script>

