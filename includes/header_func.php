<?php
// includes/header_func.php
?>
<header class="bg-slate-800 border-b border-slate-700 h-16 flex items-center justify-between px-6 sticky top-0 z-20">
    <h1 class="text-xl font-semibold text-slate-100 tracking-tight">Primus Point - Portal do Funcionário</h1>
    <div class="flex items-center gap-6">
        <div class="flex items-center gap-3">
            <span class="text-sm text-slate-400">Olá, <strong class="text-slate-100 font-medium"><?php echo htmlspecialchars($_SESSION['nome'] ?? 'Usuário'); ?></strong></span>
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-semibold text-white border-2 border-slate-800 ring-2 ring-slate-700 uppercase">
                <?php echo substr($_SESSION['nome'], 0, 2); ?>
            </div>
        </div>
        <a href="actions/logout_action.php" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Sair</a>
    </div>
</header>
