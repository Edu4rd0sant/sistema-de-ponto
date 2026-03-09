<?php
// includes/sidebar_func.php
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
        <a href="index.php" class="<?php echo getNavItemClass('index.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-squares-four text-lg"></i>
                <span>Dashboard Func</span>
            </div>
        </a>
        <a href="historico.php" class="<?php echo getNavItemClass('historico.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-clock-counter-clockwise text-lg"></i>
                <span>Histórico</span>
            </div>
        </a>
        <a href="perfil.php" class="<?php echo getNavItemClass('perfil.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-user-circle text-lg"></i>
                <span>Perfil</span>
            </div>
        </a>
        <a href="solicitacoes.php" class="<?php echo getNavItemClass('solicitacoes.php', $current_page); ?>">
            <div class="flex items-center gap-3">
                <i class="ph ph-envelope-simple text-lg"></i>
                <span>Minhas Solicitações</span>
            </div>
        </a>
    </nav>
</aside>
