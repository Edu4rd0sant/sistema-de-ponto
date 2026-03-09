<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);

// Funções para facilitar a renderização de classes ativas
function getNavItemClass($pageName, $current_page) {
    $baseClass = "flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium transition-colors ";
    if ($current_page == $pageName) {
        return $baseClass . "bg-blue-600 text-white shadow-lg shadow-blue-900/20";
    }
    return $baseClass . "text-slate-400 hover:bg-slate-800 hover:text-slate-200";
}
?>
<aside class="w-64 bg-slate-900 border-r border-slate-800 h-screen fixed top-0 left-0 flex flex-col pt-8 pb-4 z-30">
    <div class="flex justify-center mb-10 px-6">
        <img src="primuslogocompleta.png" alt="Primus Bank Logo" class="max-w-full max-h-[120px] object-contain brightness-0 invert opacity-90">
    </div>
    <nav class="flex flex-col gap-1 px-4 flex-1">
        <a href="admin.php" class="<?php echo getNavItemClass('admin.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-squares-four text-lg"></i>
                <span>Dashboard RH</span>
            </div>
        </a>
        <a href="gestao_ponto.php" class="<?php echo getNavItemClass('gestao_ponto.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-clock-user text-lg"></i>
                <span>Gestão de Ponto</span> 
            </div>
        </a>
        <a href="escalas.php" class="<?php echo getNavItemClass('escalas.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-calendar-blank text-lg"></i>
                <span>Escalas & Horários</span> 
            </div>
        </a>
        <?php if ($_SESSION['nivel_acesso'] === 'admin' || in_array('analisar_relatorios', $_SESSION['permissoes']??[])): ?>
        <a href="relatorios.php" class="<?php echo getNavItemClass('relatorios.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-chart-bar text-lg"></i>
                <span>Relatórios</span> 
            </div>
        </a>
        <?php endif; ?>
        
        <div class="my-4 border-t border-slate-800"></div>
        
        <?php if ($_SESSION['nivel_acesso'] === 'admin' || in_array('aprovar_solicitacoes', $_SESSION['permissoes']??[])): ?>
        <a href="#" class="<?php echo getNavItemClass('', $current_page); ?>" onclick="abrirModalSolicitacoes(); return false;">
            <div class="flex items-center gap-3">
                <i class="ph ph-envelope-simple text-lg"></i>
                <span>Central de Solicitações</span>
            </div>
            <span id="badge-sidebar-notificacoes" class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full" style="display: none;">0</span>
        </a>
        <?php endif; ?>
        <a href="perfil_admin.php" class="<?php echo getNavItemClass('perfil_admin.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-user-circle text-lg"></i>
                <span>Meu Perfil</span>
            </div>
        </a>
    </nav>
</aside>
