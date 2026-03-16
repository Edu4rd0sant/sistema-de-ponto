<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'funcionario') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Carrega as solicitações do usuário
try {
    $stmt = $pdo->prepare("SELECT id, tipo, descricao, data_solicitacao, status FROM solicitacoes WHERE usuario_id = :uid ORDER BY data_solicitacao DESC");
    $stmt->bindParam(':uid', $usuario_id);
    $stmt->execute();
    $minhas_solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $minhas_solicitacoes = [];
    $erro_db = "Erro ao carregar solicitações: " . $e->getMessage();
}

// Formatar badge visual
function formataStatusBadge($status) {
    switch($status) {
        case 'pendente': return '<span class="bg-orange-500/10 text-orange-400 border border-orange-500/20 px-2.5 py-1 rounded-full text-[11px] font-semibold flex items-center justify-center gap-1 w-fit whitespace-nowrap"><i class="ph ph-hourglass-high"></i> Em Análise</span>';
        case 'aprovada': return '<span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2.5 py-1 rounded-full text-[11px] font-semibold flex items-center justify-center gap-1 w-fit whitespace-nowrap"><i class="ph ph-check-circle"></i> Aprovada</span>';
        case 'recusada': return '<span class="bg-red-500/10 text-red-500 border border-red-500/20 px-2.5 py-1 rounded-full text-[11px] font-semibold flex items-center justify-center gap-1 w-fit whitespace-nowrap"><i class="ph ph-x-circle"></i> Recusada</span>';
        default: return $status;
    }
}

function formataTipoBadge($tipo) {
    switch($tipo) {
        case 'ferias': return '<div class="flex items-center gap-2"><div class="w-8 h-8 rounded bg-orange-500/10 flex items-center justify-center text-orange-400 shrink-0"><i class="ph ph-airplane-tilt text-lg"></i></div><span class="font-medium text-slate-200">Férias (Descanso)</span></div>';
        case 'atestado': return '<div class="flex items-center gap-2"><div class="w-8 h-8 rounded bg-red-500/10 flex items-center justify-center text-red-400 shrink-0"><i class="ph ph-first-aid text-lg"></i></div><span class="font-medium text-slate-200">Falta / Atestado</span></div>';
        case 'banco_horas': return '<div class="flex items-center gap-2"><div class="w-8 h-8 rounded bg-blue-500/10 flex items-center justify-center text-blue-400 shrink-0"><i class="ph ph-hourglass-high text-lg"></i></div><span class="font-medium text-slate-200">Acerto Banco Horas</span></div>';
        default: return '<div class="flex items-center gap-2"><div class="w-8 h-8 rounded bg-slate-500/10 flex items-center justify-center text-slate-400 shrink-0"><i class="ph ph-folder-notch text-lg"></i></div><span class="font-medium text-slate-200">Outros Assuntos</span></div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações - Primus Point</title>
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
        <?php include 'includes/sidebar_func.php'; ?>

        <main class="flex-1 lg:ml-64 flex flex-col min-h-screen relative overflow-hidden">
            <!-- HEADER -->
            <?php include 'includes/header_func.php'; ?>

            <div class="p-8 pb-12 flex-1 relative z-10 flex flex-col">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">Central de Solicitações</h1>
                            <p class="text-slate-400 text-sm mt-1">Acione o RH para férias, atestados e acertos de horas</p>
                        </div>
                        <button onclick="abrirModalNovaSolicitacao()" class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-2 px-4 rounded-lg shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 text-sm w-full sm:w-auto">
                            <i class="ph ph-paper-plane-tilt font-bold"></i> Nova Solicitação
                        </button>
                    </div>

                <?php if(isset($_GET['sucesso'])): ?>
                    <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-lg flex items-start gap-3 mb-6 shadow-sm shadow-emerald-500/5">
                        <i class="ph ph-check-circle text-xl mt-0.5"></i>
                        <div>Sua solicitação foi enviada para o RH com sucesso e está pendente de análise.</div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Minhas Solicitações (Tabela) -->
                    <div class="lg:col-span-2 flex flex-col">
                        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl flex flex-col h-full">
                            <div class="p-6 border-b border-slate-700/50 flex items-center gap-2">
                                <i class="ph ph-list-dashes text-blue-400 text-xl"></i>
                                <h2 class="text-lg font-semibold text-white">Histórico de Chamados</h2>
                            </div>
                            
                            <div class="overflow-x-auto flex-1">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-900/30">
                                            <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Data do Pedido</th>
                                            <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tipo/Assunto</th>
                                            <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Ocorrência</th>
                                            <th class="py-4 px-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700/50">
                                        <?php if(count($minhas_solicitacoes) > 0): ?>
                                            <?php foreach($minhas_solicitacoes as $req): 
                                                $data_obj = new DateTime($req['data_solicitacao']);
                                            ?>
                                            <tr class="hover:bg-slate-700/10 transition-colors">
                                                <td class="py-4 px-6 text-sm text-slate-400 whitespace-nowrap">
                                                    <?= $data_obj->format('d/m/Y \à\s H:i'); ?>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <?= formataTipoBadge($req['tipo']); ?>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <div class="max-w-[200px] xl:max-w-xs overflow-hidden text-ellipsis whitespace-nowrap text-sm text-slate-400 italic" title="<?= htmlspecialchars($req['descricao']); ?>">
                                                        "<?= htmlspecialchars($req['descricao']); ?>"
                                                    </div>
                                                </td>
                                                <td class="py-4 px-6">
                                                    <?= formataStatusBadge($req['status']); ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="py-16">
                                                    <div class="flex flex-col items-center justify-center text-slate-500">
                                                        <i class="ph ph-tray text-5xl mb-4 opacity-50"></i>
                                                        <h3 class="text-lg font-medium text-slate-300 mb-1">Caixa Vazia</h3>
                                                        <p class="text-sm">Você não possui nenhuma solicitação pendente ou concluída.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tipos de Solicitação Info Box -->
                    <div class="lg:col-span-1">
                        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl h-full">
                            <h2 class="text-base font-semibold text-white tracking-tight mb-6 flex items-center gap-2">
                                <i class="ph ph-info text-blue-400"></i> Guia Rápido
                            </h2>
                            
                            <ul class="flex flex-col gap-4">
                                <!-- Card Férias -->
                                <li class="bg-slate-900/40 border border-slate-700 rounded-xl p-4 flex gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-400 shrink-0">
                                        <i class="ph ph-airplane-tilt text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-white mb-1">Férias</h4>
                                        <p class="text-xs text-slate-400 leading-relaxed">Solicite preferencialmente com 30 dias de antecedência para aprovação prévia.</p>
                                    </div>
                                </li>
                                
                                <!-- Card Atestado -->
                                <li class="bg-slate-900/40 border border-slate-700 rounded-xl p-4 flex gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center text-red-500 shrink-0">
                                        <i class="ph ph-first-aid text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-white mb-1">Atestado Médico</h4>
                                        <p class="text-xs text-slate-400 leading-relaxed">Indique o período e detalhes. O RH poderá solicitar que você anexe o documento depois.</p>
                                    </div>
                                </li>

                                <!-- Card Banco de Horas -->
                                <li class="bg-slate-900/40 border border-slate-700 rounded-xl p-4 flex gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400 shrink-0">
                                        <i class="ph ph-hourglass-high text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-white mb-1">Ajuste de Ponto</h4>
                                        <p class="text-xs text-slate-400 leading-relaxed">Esqueceu de bater o ponto? Justifique a ocorrência para abonar o banco de horas.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            <!-- MODAL NOVA SOLICITAÇÃO (TAILWIND) -->
            <div id="modalNovaSolicitacao" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
                <div class="bg-slate-800 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 modal-content-anim flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800 shrink-0">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2"><i class="ph ph-envelope-open text-blue-400"></i> Falar com o RH</h2>
                        <button onclick="fecharModalNovaSolicitacao()" class="text-slate-400 hover:text-red-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
                    </div>
                    
                    <form action="actions/criar_solicitacao.php" method="POST" id="formNovaSolicitacao" class="p-6 overflow-y-auto">
                        
                        <div class="mb-5">
                            <label for="tipo_solicitacao" class="block text-sm font-medium text-slate-300 mb-1.5">Assunto Principal (Classificação)</label>
                            <select id="tipo_solicitacao" name="tipo_solicitacao" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2.5 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm appearance-none">
                                <option value="" disabled selected>Escolha o tipo da ocorrência</option>
                                <option value="ferias">Férias (Descanso Regulamentar)</option>
                                <option value="atestado">Falta / Atestado Médico</option>
                                <option value="banco_horas">Ajuste de Banco de Horas</option>
                                <option value="outro">Outro Assunto</option>
                            </select>
                        </div>

                        <div class="mb-8">
                            <label for="descricao" class="block text-sm font-medium text-slate-300 mb-1.5 flex justify-between items-end">
                                <span>Justificativa e Detalhamento da Ocorrência</span>
                                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-normal">Obrigatório</span>
                            </label>
                            <textarea id="descricao" name="descricao" rows="4" placeholder="Ex: Solicito o abono do dia de ontem pois esqueci de registrar o ponto no período da manhã. As horas extras já cobrem." required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg p-3 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm placeholder:text-slate-600 resize-y min-h-[100px]"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-700/50">
                            <button type="button" onclick="fecharModalNovaSolicitacao()" class="px-5 py-2.5 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar Míssiva</button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition-colors shadow-lg shadow-blue-500/20 flex items-center gap-2">
                                <i class="ph ph-paper-plane-right"></i> Enviar Solicitação
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function abrirModalNovaSolicitacao() {
                    const modal = document.getElementById('modalNovaSolicitacao');
                    const content = modal.querySelector('.modal-content-anim');
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        content.classList.remove('scale-95');
                        content.classList.add('scale-100');
                    }, 10);
                }

                function fecharModalNovaSolicitacao() {
                    const modal = document.getElementById('modalNovaSolicitacao');
                    const content = modal.querySelector('.modal-content-anim');
                    modal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        document.getElementById('formNovaSolicitacao').reset();
                    }, 300);
                }
            </script>
        </main>
    </div>
</body>
</html>
