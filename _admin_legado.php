<?php
require_once __DIR__ . '/config/session.php';
require_once 'config/database.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Buscar funcionários para a tabela
try {
    $stmt = $pdo->prepare("SELECT u.id, u.nome, u.email, u.cargo, u.permissoes, u.escala_id, u.status_trabalho, u.criado_em, 'Atrasado' as status_temp, e.nome as escala_nome FROM usuarios u LEFT JOIN escalas e ON u.escala_id = e.id WHERE u.id != ? ORDER BY u.nome ASC");
    $stmt->execute([$_SESSION['usuario_id']]);
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $funcionarios = [];
    $erro_db = "Erro ao carregar funcionários: " . $e->getMessage();
}

// Buscar escalas disponíveis
try {
    $stmtEscalas = $pdo->query("SELECT id, nome FROM escalas ORDER BY nome ASC");
    $escalas_disponiveis = $stmtEscalas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $escalas_disponiveis = [];
}

function renderStatusBadge($status_trabalho) {
    switch ($status_trabalho) {
        case 'Férias':
            return '<span class="bg-orange-500/10 text-orange-500 border border-orange-500/20 px-2.5 py-1 rounded-full text-xs font-semibold">Férias</span>';
        case 'Afastado':
            return '<span class="bg-red-500/10 text-red-500 border border-red-500/20 px-2.5 py-1 rounded-full text-xs font-semibold">Afastado</span>';
        case 'Trabalhando':
        default:
            return '<span class="bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 px-2.5 py-1 rounded-full text-xs font-semibold">Trabalhando</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do RH - Primus Point</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <style>
        /* Estilos básicos para os modais em Tailwind */
        .modal-overlay {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }
        .modal-content {
            transform: translateY(-20px) scale(0.95);
            transition: transform 0.3s ease;
        }
        .modal-overlay.active .modal-content {
            transform: translateY(0) scale(1);
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-200 font-sans min-h-screen">
    <div class="flex w-full">
        <!-- SIDEBAR -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="flex-1 lg:ml-64 flex flex-col min-h-screen">
            <!-- HEADER -->
            <?php include 'includes/header.php'; ?>

            <!-- CONTEÚDO -->
            <div class="p-8 pb-12 flex-1 relative">
                
                <!-- Fundo decorativo -->
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-900/10 blur-[120px] rounded-full pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 relative z-10">
                    
                    <!-- COLUNA ESQUERDA (Tabela) - Ocupa 2/3 -->
                    <div class="lg:col-span-2 flex flex-col gap-6">
                        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl h-full flex flex-col">
                            
                            <!-- Header do Card -->
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                                <h2 class="text-xl font-semibold text-white tracking-tight">Visão Geral da Equipe e Escalas</h2>
                                
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                                    <div class="relative w-full sm:w-64">
                                        <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                        <input type="text" placeholder="Buscar funcionário..." 
                                            class="w-full bg-slate-900/50 border border-slate-700 rounded-lg pl-10 pr-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-slate-500">
                                    </div>
                                    <button onclick="abrirModalNovoFuncionario()" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium py-2 px-4 rounded-lg shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 whitespace-nowrap w-full sm:w-auto">
                                        <i class="ph ph-plus-bold"></i> Novo Funcionário
                                    </button>
                                </div>
                            </div>

                            <?php if(isset($erro_db)): ?>
                                <div class="bg-red-500/10 border border-red-500/20 text-red-500 text-sm p-3 rounded-lg mb-4">
                                    <?php echo $erro_db; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Tabela -->
                            <div class="overflow-x-auto flex-1">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-slate-700/50">
                                            <th class="py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Foto</th>
                                            <th class="py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Nome</th>
                                            <th class="py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Email</th>
                                            <th class="py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Cargo / Escala</th>
                                            <th class="py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                                            <th class="py-3 px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider text-right">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700/50">
                                        <?php if(count($funcionarios) > 0): ?>
                                            <?php foreach($funcionarios as $idx => $func): 
                                                $iniciais = strtoupper(substr($func['nome'], 0, 2));
                                                $status_trabalho = $func['status_trabalho'] ?: 'Trabalhando';
                                            ?>
                                            <tr class="hover:bg-slate-700/20 transition-colors group">
                                                <td class="py-3 px-4">
                                                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">
                                                        <?php echo $iniciais; ?>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 text-sm font-medium text-slate-100"><?php echo htmlspecialchars($func['nome']); ?></td>
                                                <td class="py-3 px-4 text-sm text-slate-400"><?php echo htmlspecialchars($func['email']); ?></td>
                                                <td class="py-3 px-4">
                                                    <div class="flex flex-col gap-1 items-start">
                                                        <span class="bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded text-[11px] font-medium leading-none"><?php echo htmlspecialchars($func['cargo'] ?: 'Geral'); ?></span>
                                                        <span class="text-[11px] text-slate-500 flex items-center gap-1"><i class="ph ph-calendar-blank"></i> <?php echo htmlspecialchars($func['escala_nome'] ?: 'Sem escala fixa'); ?></span>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <?php echo renderStatusBadge($status_trabalho); ?>
                                                </td>
                                                    <td class="py-3 px-4 text-right">
                                                        <div class="flex justify-end gap-2 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                                            <?php if ($_SESSION['nivel_acesso'] === 'admin' || in_array('ajustar_pontos', $_SESSION['permissoes']??[])): ?>
                                                            <button title="Alterar Ponto" onclick='abrirModalPonto(<?php echo $func['id']; ?>, <?php echo htmlspecialchars(json_encode($func['nome'])); ?>)' class="p-1.5 bg-slate-800 border border-slate-700 rounded text-slate-400 hover:text-blue-400 hover:border-blue-500/50 transition-colors">
                                                                <i class="ph ph-clock text-lg"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                            
                                                            <?php if ($_SESSION['nivel_acesso'] === 'admin' || in_array('gerenciar_senhas', $_SESSION['permissoes']??[])): ?>
                                                            <button title="Forçar Nova Senha" onclick='abrirModalResetSenha(<?php echo $func['id']; ?>, <?php echo htmlspecialchars(json_encode($func['nome'])); ?>)' class="p-1.5 bg-slate-800 border border-slate-700 rounded text-slate-400 hover:text-red-400 hover:border-red-500/50 transition-colors">
                                                                <i class="ph ph-key text-lg"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                            <?php if ($_SESSION['nivel_acesso'] === 'admin'): ?>
                                                            <button title="Acessos, Escala & Status" onclick='abrirModalEditarPermissoes(<?php echo $func['id']; ?>, <?php echo htmlspecialchars(json_encode($func['nome'])); ?>, <?php echo htmlspecialchars(json_encode($func['cargo'] ?: '')); ?>, "<?php echo base64_encode($func['permissoes']??'[]'); ?>", "<?php echo $func['escala_id'] ?: ''; ?>", "<?php echo $func['status_trabalho'] ?: 'Trabalhando'; ?>")' class="p-1.5 bg-slate-800 border border-slate-700 rounded text-slate-400 hover:text-emerald-400 hover:border-emerald-500/50 transition-colors">
                                                                <i class="ph ph-shield-star text-lg"></i>
                                                            </button>
                                                            <button title="Excluir Colaborador" onclick='abrirModalExcluir(<?php echo $func['id']; ?>, <?php echo htmlspecialchars(json_encode($func['nome'])); ?>)' class="p-1.5 bg-slate-800 border border-slate-700 rounded text-slate-400 hover:text-red-500 hover:border-red-500/50 transition-colors">
                                                                <i class="ph ph-trash text-lg"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="py-8 text-center text-slate-500 text-sm">Nenhum funcionário cadastrado no sistema.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- COLUNA DIREITA (Widgets) - Ocupa 1/3 -->
                    <div class="lg:col-span-1 flex flex-col gap-6">
                        
                        <!-- WIDGET 1: Central de Solicitações -->
                        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl flex flex-col min-h-[300px]">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold text-white tracking-tight">Central de Solicitações</h2>
                                <?php if ($_SESSION['nivel_acesso'] === 'admin' || in_array('aprovar_solicitacoes', $_SESSION['permissoes']??[])): ?>
                                <button onclick="abrirModalSolicitacoes()" class="text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors">Ver Histórico</button>
                                <?php endif; ?>
                            </div>
                            
                            <div id="dashboard-lista-solicitacoes" class="flex-1 overflow-y-auto pr-2 -mr-2 space-y-3">
                                <!-- Será preenchido pelo JS, contendo o HTML padrão de vazio caso array empty -->
                                <div class="h-full flex flex-col items-center justify-center text-slate-500 py-8">
                                    <i class="ph ph-tray text-4xl mb-2 opacity-50"></i>
                                    <p class="text-sm text-center">Não há solicitações pendentes no momento.</p>
                                </div>
                            </div>
                        </div>

                        <!-- WIDGET 2 & 3: Presença Hoje e Gráfico -->
                        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl">
                            <!-- Presença Hoje -->
                            <div class="mb-8">
                                <h2 class="text-lg font-semibold text-white tracking-tight mb-4">Presença Hoje</h2>
                                <div class="flex justify-between text-sm font-medium mb-2">
                                    <span class="text-slate-300">Presentes (82%)</span>
                                    <span class="text-blue-400">14 / 17</span>
                                </div>
                                <div class="h-2 w-full bg-slate-900 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full" style="width: 82%;"></div>
                                </div>
                            </div>

                            <!-- Acesso por Dispositivo (Chart.js) -->
                            <div>
                                <h3 class="text-sm font-medium text-slate-400 mb-4 uppercase tracking-wider">Acesso por Dispositivo</h3>
                                <div class="relative h-40 flex items-center justify-center pt-2">
                                    <canvas id="deviceChart"></canvas>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ================= MODAIS EM TAILWIND ================= -->
            
            <!-- MODAL ALTERAR PONTO -->
            <div id="modalPonto" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
                <div class="modal-content bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center shrink-0">
                        <h2 class="text-lg font-semibold text-white">Alterar Ponto Manual</h2>
                        <button onclick="fecharModalPonto()" class="text-slate-400 hover:text-red-400 transition-colors">
                            <i class="ph ph-x text-xl"></i>
                        </button>
                    </div>
                    <form action="actions/salvar_ponto_admin.php" method="POST" id="formAlterarPonto" class="p-6 overflow-y-auto">
                        <input type="hidden" name="usuario_id" id="modal_usuario_id">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-400 mb-1.5">Colaborador</label>
                            <input type="text" id="modal_nome_funcionario" readonly class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-400 cursor-not-allowed text-sm">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="data_registro" class="block text-sm font-medium text-slate-300 mb-1.5">Data</label>
                                <input type="date" id="data_registro" name="data_registro" required value="<?php echo date('Y-m-d'); ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm">
                            </div>
                            <div>
                                <label for="hora_registro" class="block text-sm font-medium text-slate-300 mb-1.5">Hora</label>
                                <input type="time" id="hora_registro" name="hora_registro" required value="<?php echo date('H:i'); ?>" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm">
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="tipo_registro" class="block text-sm font-medium text-slate-300 mb-1.5">Tipo de Batida</label>
                            <select id="tipo_registro" name="tipo_registro" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm appearance-none">
                                <option value="entrada">Entrada</option>
                                <option value="saida_almoco">Saída para Almoço</option>
                                <option value="retorno_almoco">Retorno do Almoço</option>
                                <option value="saida">Saída (Fim do Expediente)</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="fecharModalPonto()" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition-colors shadow-lg shadow-blue-500/20">Salvar Registro</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- MODAL NOVO FUNCIONÁRIO -->
            <div id="modalNovoFuncionario" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
                <div class="modal-content bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center shrink-0">
                        <h2 class="text-lg font-semibold text-white">Novo Funcionário</h2>
                        <button onclick="fecharModalNovoFuncionario()" class="text-slate-400 hover:text-red-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
                    </div>
                    <form action="actions/criar_funcionario.php" method="POST" id="formNovoFuncionario" class="p-6 overflow-y-auto">
                        <div class="mb-4">
                            <label for="novo_nome" class="block text-sm font-medium text-slate-300 mb-1.5">Nome Completo</label>
                            <input type="text" id="novo_nome" name="nome" placeholder="Ex: João da Silva" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm placeholder:text-slate-600">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="novo_email" class="block text-sm font-medium text-slate-300 mb-1.5">E-mail Corporativo</label>
                                <input type="email" id="novo_email" name="email" placeholder="joao.silva@primus.com" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm placeholder:text-slate-600">
                            </div>
                            <div>
                                <label for="novo_cargo" class="block text-sm font-medium text-slate-300 mb-1.5">Cargo/Função</label>
                                <input type="text" id="novo_cargo" name="cargo" placeholder="Ex: Gestor de Tráfego" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm placeholder:text-slate-600">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                            <div>
                                <label for="nova_escala" class="block text-sm font-medium text-slate-300 mb-1.5">Escala de Trabalho</label>
                                <select id="nova_escala" name="escala_id" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm appearance-none">
                                    <option value="">Nenhuma / Horário Livre</option>
                                    <?php foreach($escalas_disponiveis as $esc): ?>
                                        <option value="<?php echo $esc['id']; ?>"><?php echo htmlspecialchars($esc['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="novo_status" class="block text-sm font-medium text-slate-300 mb-1.5">Status Atual</label>
                                <select id="novo_status" name="status_trabalho" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm appearance-none">
                                    <option value="Trabalhando">Trabalhando</option>
                                    <option value="Férias">Férias</option>
                                    <option value="Afastado">Afastado</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-5">
                            <label for="nova_senha" class="block text-sm font-medium text-slate-300 mb-1.5">Senha Provisória</label>
                            <input type="password" id="nova_senha" name="senha" placeholder="Mínimo 6 caracteres" required minlength="6" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm placeholder:text-slate-600">
                        </div>
                        
                        <div class="mb-6 p-4 rounded-lg border border-slate-700/50 bg-slate-900/30">
                            <label class="block text-sm font-medium text-slate-300 mb-3 flex items-center gap-2">
                                <i class="ph ph-shield-check text-blue-400"></i> Permissões Especiais (Exceções)
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="permissoes[]" value="analisar_relatorios" class="w-4 h-4 rounded border-slate-600 text-blue-500 focus:ring-blue-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Analisar Relatórios</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="permissoes[]" value="gerenciar_senhas" class="w-4 h-4 rounded border-slate-600 text-blue-500 focus:ring-blue-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Gerenciar Senhas</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="permissoes[]" value="aprovar_solicitacoes" class="w-4 h-4 rounded border-slate-600 text-blue-500 focus:ring-blue-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Aprovar Solicitações</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" name="permissoes[]" value="ajustar_pontos" class="w-4 h-4 rounded border-slate-600 text-blue-500 focus:ring-blue-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Ajustar Pontos Manualmente</span>
                                </label>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-3 italic">* Ao marcar as opções acima, o funcionário terá acesso a partes sensíveis do Painel de RH.</p>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="fecharModalNovoFuncionario()" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition-colors shadow-lg shadow-blue-500/20">Criar Acesso</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL RESETAR SENHA -->
            <div id="modalResetSenha" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
                <div class="modal-content bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center shrink-0">
                        <h2 class="text-lg font-semibold text-white">Forçar Nova Senha</h2>
                        <button onclick="fecharModalResetSenha()" class="text-slate-400 hover:text-red-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
                    </div>
                    <form action="actions/resetar_senha_admin.php" method="POST" id="formResetSenha" class="p-6 overflow-y-auto">
                        <input type="hidden" id="reset_usuario_id" name="usuario_id">
                        
                        <div class="mb-4">
                            <label for="reset_nome_funcionario" class="block text-sm font-medium text-slate-400 mb-1.5">Colaborador</label>
                            <input type="text" id="reset_nome_funcionario" readonly class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-400 cursor-not-allowed text-sm">
                        </div>
                        <div class="mb-6">
                            <label for="nova_senha_admin" class="block text-sm font-medium text-slate-300 mb-1.5">Nova Senha (Definida pelo RH)</label>
                            <input type="password" id="nova_senha_admin" name="nova_senha" placeholder="Mínimo 6 caracteres" required minlength="6" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-red-500 transition-colors text-sm placeholder:text-slate-600">
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="fecharModalResetSenha()" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-500 transition-colors shadow-lg shadow-red-500/20">Aplicar Senha</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL EDITAR PERMISSÕES -->
            <div id="modalEditarPermissoes" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
                <div class="modal-content bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center shrink-0">
                        <h2 class="text-lg font-semibold text-white">Editar Permissões</h2>
                        <button onclick="fecharModalEditarPermissoes()" class="text-slate-400 hover:text-emerald-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
                    </div>
                    <form action="actions/editar_permissoes.php" method="POST" id="formEditarPermissoes" class="p-6 overflow-y-auto">
                        <input type="hidden" id="edit_perm_id" name="usuario_id">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-400 mb-1.5">Colaborador</label>
                            <input type="text" id="edit_perm_nome" readonly class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-400 cursor-not-allowed text-sm">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-400 mb-1.5">Cargo/Função</label>
                                <input type="text" id="edit_perm_cargo" name="cargo" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500 transition-colors text-sm">
                            </div>
                            <div>
                                <label for="edit_perm_escala" class="block text-sm font-medium text-slate-400 mb-1.5">Escala de Trabalho</label>
                                <select id="edit_perm_escala" name="escala_id" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500 transition-colors text-sm appearance-none">
                                    <option value="">Nenhuma / Horário Livre</option>
                                    <?php foreach($escalas_disponiveis as $esc): ?>
                                        <option value="<?php echo $esc['id']; ?>"><?php echo htmlspecialchars($esc['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label for="edit_perm_status" class="block text-sm font-medium text-slate-400 mb-1.5">Status de Trabalho Atual</label>
                            <select id="edit_perm_status" name="status_trabalho" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-emerald-500 transition-colors text-sm appearance-none">
                                <option value="Trabalhando">Trabalhando</option>
                                <option value="Férias">Férias</option>
                                <option value="Afastado">Afastado</option>
                            </select>
                        </div>
                        
                        <div class="mb-6 p-4 rounded-lg border border-slate-700/50 bg-slate-900/30">
                            <label class="block text-sm font-medium text-slate-300 mb-3 flex items-center gap-2">
                                <i class="ph ph-shield-check text-emerald-400"></i> Permissões Especiais (Módulos)
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" id="chk_analisar_relatorios" name="permissoes[]" value="analisar_relatorios" class="w-4 h-4 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Analisar Relatórios</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" id="chk_gerenciar_senhas" name="permissoes[]" value="gerenciar_senhas" class="w-4 h-4 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Gerenciar Senhas</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" id="chk_aprovar_solicitacoes" name="permissoes[]" value="aprovar_solicitacoes" class="w-4 h-4 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Aprovar Solicitações</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox" id="chk_ajustar_pontos" name="permissoes[]" value="ajustar_pontos" class="w-4 h-4 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500/20 bg-slate-800">
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition-colors">Ajustar Pontos Manualmente</span>
                                </label>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-3 italic">* Ao marcar todas as 4 opções acima, o usuário será promovido permanentemente a nível "Admin".</p>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="fecharModalEditarPermissoes()" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-500 transition-colors shadow-lg shadow-emerald-500/20">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL EXCLUIR FUNCIONÁRIO -->
            <div id="modalExcluir" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
                <div class="modal-content bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh] p-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center mx-auto mb-4">
                        <i class="ph ph-warning-circle text-4xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2">Excluir Colaborador</h2>
                    <p class="text-slate-400 text-sm mb-6">Tem certeza que deseja excluir o cadastro de <strong id="excluir_nome_funcionario" class="text-slate-200"></strong>? Esta ação é irreversível e excluirá todos os pontos e solicitações atrelados a ele.</p>
                    
                    <form action="actions/excluir_funcionario.php" method="POST" id="formExcluir">
                        <input type="hidden" id="excluir_usuario_id" name="usuario_id">
                        <div class="flex justify-center gap-3">
                            <button type="button" onclick="fecharModalExcluir()" class="px-5 py-2.5 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-500 transition-colors shadow-lg shadow-red-500/20">Sim, Excluir</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL SOLICITAÇÕES PENDENTES -->
            <div id="modalSolicitacoes" class="modal-overlay fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4">
                <div class="modal-content bg-slate-800 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800">
                        <h2 class="text-lg font-semibold text-white">Solicitações Pendentes</h2>
                        <button onclick="fecharModalSolicitacoes()" class="text-slate-400 hover:text-red-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
                    </div>
                    <div id="lista-solicitacoes-pendentes" class="p-6 overflow-y-auto flex-1 bg-slate-800/50">
                        <!-- Será preenchido via JS -->
                        <div class="flex flex-col items-center justify-center text-slate-500 py-8">
                            <i class="ph ph-spinner-gap animate-spin text-3xl mb-2"></i>
                            <p class="text-sm">Carregando solicitações...</p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Funções Auxiliares dos Modais
                function toggleModal(id, show) {
                    const modal = document.getElementById(id);
                    if(show) modal.classList.add('active');
                    else modal.classList.remove('active');
                }

                function abrirModalPonto(id, nome) {
                    document.getElementById('modal_usuario_id').value = id;
                    document.getElementById('modal_nome_funcionario').value = nome;
                    const now = new Date();
                    document.getElementById('hora_registro').value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                    toggleModal('modalPonto', true);
                }
                function fecharModalPonto() { toggleModal('modalPonto', false); }

                function abrirModalNovoFuncionario() {
                    toggleModal('modalNovoFuncionario', true);
                    setTimeout(() => document.getElementById('novo_nome').focus(), 50);
                }
                function fecharModalNovoFuncionario() {
                    toggleModal('modalNovoFuncionario', false);
                    document.getElementById('formNovoFuncionario').reset();
                }

                function abrirModalResetSenha(id, nome) {
                    document.getElementById('reset_usuario_id').value = id;
                    document.getElementById('reset_nome_funcionario').value = nome;
                    toggleModal('modalResetSenha', true);
                }
                function fecharModalResetSenha() {
                    toggleModal('modalResetSenha', false);
                    document.getElementById('formResetSenha').reset();
                }

                function abrirModalEditarPermissoes(id, nome, cargo, permissoesB64, escala_id, status_trabalho) {
                    document.getElementById('edit_perm_id').value = id;
                    document.getElementById('edit_perm_nome').value = nome;
                    document.getElementById('edit_perm_cargo').value = cargo;
                    document.getElementById('edit_perm_escala').value = escala_id || "";
                    document.getElementById('edit_perm_status').value = status_trabalho || "Trabalhando";
                    
                    document.getElementById('chk_analisar_relatorios').checked = false;
                    document.getElementById('chk_gerenciar_senhas').checked = false;
                    document.getElementById('chk_aprovar_solicitacoes').checked = false;
                    document.getElementById('chk_ajustar_pontos').checked = false;

                    try {
                        const jsonStr = atob(permissoesB64);
                        if (jsonStr) {
                            const permissoes = JSON.parse(jsonStr);
                            if (Array.isArray(permissoes)) {
                                if (permissoes.includes('analisar_relatorios')) document.getElementById('chk_analisar_relatorios').checked = true;
                                if (permissoes.includes('gerenciar_senhas')) document.getElementById('chk_gerenciar_senhas').checked = true;
                                if (permissoes.includes('aprovar_solicitacoes')) document.getElementById('chk_aprovar_solicitacoes').checked = true;
                                if (permissoes.includes('ajustar_pontos')) document.getElementById('chk_ajustar_pontos').checked = true;
                            }
                        }
                    } catch (e) {
                        console.error("Erro ao fazer parse das permissões", e);
                    }
                    toggleModal('modalEditarPermissoes', true);
                }
                function fecharModalEditarPermissoes() {
                    toggleModal('modalEditarPermissoes', false);
                    document.getElementById('formEditarPermissoes').reset();
                }

                function abrirModalExcluir(id, nome) {
                    document.getElementById('excluir_usuario_id').value = id;
                    document.getElementById('excluir_nome_funcionario').innerText = nome;
                    toggleModal('modalExcluir', true);
                }
                function fecharModalExcluir() {
                    toggleModal('modalExcluir', false);
                    document.getElementById('formExcluir').reset();
                }

                // Chart.js - Doughnut para Dispositivos
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('deviceChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Desktop', 'Mobile'],
                            datasets: [{
                                data: [57, 43],
                                backgroundColor: [
                                    '#2563eb', // blue-600
                                    '#60a5fa'  // blue-400
                                ],
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#cbd5e1', // slate-300
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            family: "'Inter', sans-serif",
                                            size: 12
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            </script>

        </main>
    </div>
    <script src="/js/admin.js"></script>
    
    <!-- Reescrevendo o renderizarListaSolicitacoes aqui para injetar com estilos corretos Tailwind sobrepondo admin.js -->
    <script>
        // Override the list rendering function from admin.js to output Tailwind classes
        window.renderizarListaSolicitacoes = function(solicitacoes) {
            const containerModal = document.getElementById('lista-solicitacoes-pendentes');
            const containerDash = document.getElementById('dashboard-lista-solicitacoes');
            
            if (solicitacoes.length === 0) {
                const msg = `
                    <div class="h-full flex flex-col items-center justify-center text-slate-500 py-8">
                        <i class="ph ph-tray text-4xl mb-2 opacity-50"></i>
                        <p class="text-sm text-center">Não há solicitações pendentes no momento.</p>
                    </div>`;
                if (containerModal) containerModal.innerHTML = msg;
                if (containerDash) containerDash.innerHTML = msg;
                return;
            }
            
            let html = '<ul class="space-y-3">';
            solicitacoes.forEach(sol => {
                let iconStr = '<i class="ph ph-file-text text-xl"></i>';
                let iconColor = 'text-blue-400 bg-blue-500/10';
                
                if (sol.tipo === 'ferias') {
                    iconStr = '<i class="ph ph-airplane-tilt text-xl"></i>';
                    iconColor = 'text-orange-400 bg-orange-500/10';
                }
                if (sol.tipo === 'ajuste_ponto') {
                    iconStr = '<i class="ph ph-clock text-xl"></i>';
                    iconColor = 'text-purple-400 bg-purple-500/10';
                }
                if (sol.tipo === 'atestado') {
                    iconStr = '<i class="ph ph-first-aid text-xl"></i>';
                    iconColor = 'text-red-400 bg-red-500/10';
                }
                if (sol.tipo === 'banco_horas') {
                    iconStr = '<i class="ph ph-hourglass-high text-xl"></i>';
                    iconColor = 'text-yellow-400 bg-yellow-500/10';
                }
                
                const dataFormatada = new Date(sol.solicitada_em).toLocaleString('pt-BR', {hour: '2-digit', minute:'2-digit', day:'2-digit', month:'short'});
                
                html += `
                    <li class="bg-slate-900/50 border border-slate-700/50 rounded-lg p-4 flex gap-4 transition-colors hover:bg-slate-800">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center ${iconColor}">
                            ${iconStr}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="text-sm font-medium text-slate-200 truncate pr-2">${sol.nome_funcionario}</h4>
                                <span class="text-[11px] text-slate-500 whitespace-nowrap">${dataFormatada}</span>
                            </div>
                            <p class="text-xs text-blue-400 font-medium mb-1 uppercase tracking-wider">${sol.tipo.replace('_', ' ')}</p>
                            <p class="text-xs text-slate-400 italic line-clamp-2 mb-3">"${sol.descricao}"</p>
                            
                            <div class="flex gap-2 justify-end">
                                <button onclick="atualizarStatusSolicitacao(${sol.id}, 'aprovada')" class="px-3 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded md:text-xs text-[11px] font-medium transition-colors flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Aprovar
                                </button>
                                <button onclick="atualizarStatusSolicitacao(${sol.id}, 'recusada')" class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded md:text-xs text-[11px] font-medium transition-colors flex items-center gap-1">
                                    <i class="ph ph-x-circle"></i> Recusar
                                </button>
                            </div>
                        </div>
                    </li>
                `;
            });
            html += '</ul>';
            
            if (containerModal) containerModal.innerHTML = html;
            if (containerDash) containerDash.innerHTML = html;
        };

        // UI Feedback for Actions
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const sucesso = urlParams.get('sucesso');
            const erro = urlParams.get('erro');
            
            if (sucesso) {
                let msg = "Operação realizada com sucesso!";
                if (sucesso === 'permissoes_atualizadas') msg = "Permissões e acessos atualizados com sucesso!";
                if (sucesso === 'senha_forçada') msg = "Senha do colaborador alterada com sucesso!";
                if (sucesso === 'funcionario_excluido') msg = "Colaborador excluído com sucesso!";
                if (sucesso === 'ponto_salvo') msg = "Ponto manual salvo com sucesso!";
                
                alert('✅ ' + msg);
                // Clear URL to prevent showing alert again on refresh
                window.history.replaceState({}, document.title, window.location.pathname);
            }
            
            if (erro) {
                let msg = "Ocorreu um erro na solicitação.";
                if (erro === 'autoexclusao') msg = "Você não pode excluir seu próprio usuário admin.";
                if (erro === 'exclusao_nao_permitida') msg = "Não é permitido excluir este administrador.";
                if (erro === 'senha_curta') msg = "A senha deve ter no mínimo 6 caracteres.";
                if (erro === 'dados_invalidos') msg = "Preencha todos os campos obrigatórios.";
                if (erro === 'usuario_invalido') msg = "Usuário não encontrado ou ID inválido.";
                
                alert('❌ Erro: ' + msg);
                // Clear URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>
