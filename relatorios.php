<?php
require_once __DIR__ . '/config/session.php';
// Apenas ADMIN puro ou funcionario/gerente que tenha permissao "analisar_relatorios"
if (!isset($_SESSION['logado'])) {
    header("Location: login.php");
    exit;
}
$is_admin = ($_SESSION['nivel_acesso'] === 'admin');
$pode_ver = $is_admin || (is_array($_SESSION['permissoes']) && in_array('analisar_relatorios', $_SESSION['permissoes']));

if (!$pode_ver) {
    header("Location: admin.php?erro=sem_permissao");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Primus Point</title>
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
        <!-- SIDEBAR -->
        <?php include 'includes/sidebar.php'; ?>

        <main class="flex-1 ml-64 flex flex-col min-h-screen relative overflow-hidden">
            <!-- HEADER -->
            <?php include 'includes/header.php'; ?>
            
            <div class="p-8 pb-12 flex-1 relative z-10 flex flex-col">
                <div class="mb-6 flex justify-between items-center text-white">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Centro de Relatórios</h1>
                        <p class="text-slate-400 text-sm mt-1">Extração de dados para contabilidade e fechamento</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- COLUNA ESQUERDA (Filtros e Exportação) -->
                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl h-full flex flex-col">
                            <h2 class="text-lg font-semibold text-white tracking-tight mb-6 flex items-center gap-2">
                                <i class="ph ph-funnel-simple text-blue-400"></i> Filtros de Extração
                            </h2>
                            
                            <!-- Painel de Filtros -->
                            <div class="bg-slate-900/50 border border-slate-700/50 rounded-xl p-5 flex gap-4 md:flex-row flex-col mb-8">
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Mês de Referência</label>
                                    <select class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-blue-500 transition-colors appearance-none">
                                        <option>Março</option>
                                        <option>Fevereiro</option>
                                        <option>Janeiro</option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ano</label>
                                    <select class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-blue-500 transition-colors appearance-none">
                                        <option>2026</option>
                                        <option>2025</option>
                                    </select>
                                </div>
                                <div class="flex-[1.5]">
                                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Colaborador</label>
                                    <select class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-blue-500 transition-colors appearance-none">
                                        <option>Todos os Colaboradores</option>
                                        <option>João da Silva</option>
                                        <option>Maria Oliveira</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Área de Preview Vazia / Upload -->
                            <div class="flex-1 border-2 border-dashed border-slate-700 rounded-xl p-10 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mb-4">
                                    <i class="ph ph-file-text text-3xl text-slate-400"></i>
                                </div>
                                <h3 class="text-white font-medium mb-1">Espelho de Ponto</h3>
                                <p class="text-sm text-slate-400 max-w-sm mb-8">Selecione os filtros acima para preparar os dados de espelho de ponto aptos para a Contabilidade.</p>
                                
                                <div class="flex flex-wrap gap-4 justify-center">
                                    <button class="bg-red-500/10 hover:bg-red-500/20 text-red-500 border border-red-500/20 px-6 py-2.5 rounded-lg flex items-center gap-2 font-medium transition-all shadow-lg hover:shadow-red-500/10 active:scale-95 text-sm">
                                        <i class="ph ph-file-pdf text-lg"></i> Exportar PDF
                                    </button>
                                    <button class="bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 px-6 py-2.5 rounded-lg flex items-center gap-2 font-medium transition-all shadow-lg hover:shadow-emerald-500/10 active:scale-95 text-sm">
                                        <i class="ph ph-file-xls text-lg"></i> Exportar Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUNA DIREITA (Visão Rápida) -->
                    <div class="lg:col-span-1">
                        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl h-full">
                            <h2 class="text-lg font-semibold text-white tracking-tight mb-6 flex items-center gap-2">
                                <i class="ph ph-chart-line-up text-blue-400"></i> Métricas do Mês Atual
                            </h2>
                            
                            <div class="flex flex-col gap-4">
                                <!-- Card de Métrica 1 -->
                                <div class="bg-blue-500/10 border-l-4 border-l-blue-500 rounded-r-lg p-4 transition-colors hover:bg-blue-500/20">
                                    <span class="block text-xs font-semibold text-blue-400 uppercase tracking-wide mb-1">Horas Extras (Geral)</span>
                                    <div class="flex items-end gap-2">
                                        <span class="text-2xl font-bold text-white">+ 42h 15m</span>
                                        <span class="text-xs text-slate-400 mb-1">neste mês</span>
                                    </div>
                                </div>

                                <!-- Card de Métrica 2 -->
                                <div class="bg-red-500/10 border-l-4 border-l-red-500 rounded-r-lg p-4 transition-colors hover:bg-red-500/20">
                                    <span class="block text-xs font-semibold text-red-400 uppercase tracking-wide mb-1">Faltas Injustificadas</span>
                                    <div class="flex items-end gap-2">
                                        <span class="text-2xl font-bold text-white">3</span>
                                        <span class="text-xs text-slate-400 mb-1">ocorrências</span>
                                    </div>
                                </div>

                                <!-- Card de Métrica 3 -->
                                <div class="bg-emerald-500/10 border-l-4 border-l-emerald-500 rounded-r-lg p-4 transition-colors hover:bg-emerald-500/20">
                                    <span class="block text-xs font-semibold text-emerald-400 uppercase tracking-wide mb-1">Atestados Médicos Aceitos</span>
                                    <div class="flex items-end gap-2">
                                        <span class="text-2xl font-bold text-white">12h</span>
                                        <span class="text-xs text-slate-400 mb-1">abonadas</span>
                                    </div>
                                </div>
                                
                                <!-- Card de Métrica Extra -->
                                <div class="bg-purple-500/10 border-l-4 border-l-purple-500 rounded-r-lg p-4 transition-colors hover:bg-purple-500/20 mt-2 border border-dashed border-slate-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-semibold text-purple-400 uppercase tracking-wide">Folha Pronta</span>
                                        <i class="ph ph-check-circle text-purple-400"></i>
                                    </div>
                                    <button class="w-full bg-slate-900 border border-slate-700 text-slate-300 text-sm py-2 rounded-md hover:bg-slate-800 transition-colors">
                                        Analisar Prévia
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</body>
</html>
