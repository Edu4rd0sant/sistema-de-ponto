<?php
require_once __DIR__ . '/config/session.php';
require_once 'config/database.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT e.*, COUNT(u.id) as total_funcionarios FROM escalas e LEFT JOIN usuarios u ON u.escala_id = e.id GROUP BY e.id ORDER BY e.id ASC");
    $escalas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $escalas = [];
    $erro_db = "Erro ao carregar escalas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Escalas - Primus Point</title>
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

        <main class="flex-1 lg:ml-64 flex flex-col min-h-screen relative overflow-hidden">
            <!-- HEADER -->
            <?php include 'includes/header.php'; ?>
            
            <div class="p-8 pb-12 flex-1 relative z-10 flex flex-col">
                <div class="mb-6 flex justify-between items-center text-white">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Carga Horária e Escalas</h1>
                        <p class="text-slate-400 text-sm mt-1">Configuração de jornadas de trabalho e turnos</p>
                    </div>
                </div>

                <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl flex flex-col">
                    
                    <div class="p-6 border-b border-slate-700/50 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-slate-800 rounded-t-2xl">
                        <div class="flex flex-col">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                <i class="ph ph-calendar-plus text-blue-400 text-xl"></i> Escalas Base
                            </h2>
                            <p class="text-slate-400 text-sm mt-1 max-w-2xl">As escalas determinam as jornadas de trabalho. Associe um colaborador a uma escala para computar horas extras ou atrasos automaticamente.</p>
                        </div>
                        <button onclick="abrirModalEscala()" class="bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 px-5 rounded-lg shadow-lg shadow-blue-500/20 transition-all hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 whitespace-nowrap text-sm w-full md:w-auto">
                            <i class="ph ph-plus-bold"></i> Criar Nova Escala
                        </button>
                    </div>

                    <?php if(isset($erro_db)): ?>
                        <div class="bg-red-500/10 border border-red-500/20 text-red-500 text-sm p-4 m-6 mb-0 rounded-lg">
                            <?php echo $erro_db; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_GET['sucesso'])): ?>
                        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-sm p-4 m-6 mb-0 rounded-lg">
                            Ação realizada com sucesso!
                        </div>
                    <?php endif; ?>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <?php if(count($escalas) > 0): ?>
                                <?php foreach($escalas as $escala): ?>
                                <div class="group bg-slate-900/40 border border-slate-700 hover:border-blue-500/50 rounded-xl p-5 hover:bg-slate-800/60 transition-all hover:-translate-y-1 shadow-lg hover:shadow-blue-900/20">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="font-bold text-white text-base max-w-[80%] break-words"><?php echo htmlspecialchars($escala['nome']); ?></h3>
                                        <button onclick="abrirModalExcluir(<?php echo $escala['id']; ?>)" class="text-slate-500 hover:text-red-400 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                            <i class="ph ph-trash text-lg"></i>
                                        </button>
                                    </div>
                                    <div class="text-sm text-slate-400 space-y-2 mb-6">
                                        <div class="flex items-center gap-2 text-slate-300 font-medium">
                                            <i class="ph ph-calendar-blank"></i> <?php echo htmlspecialchars($escala['dias_trabalho']); ?>
                                        </div>
                                        <div class="flex items-start gap-2">
                                            <i class="ph ph-clock mt-0.5 text-slate-500"></i> 
                                            <span>Entrada: <span class="text-slate-200"><?php echo substr($escala['hora_entrada'], 0, 5); ?></span> • Saída: <span class="text-slate-200"><?php echo substr($escala['hora_saida'], 0, 5); ?></span></span>
                                        </div>
                                        <?php if(!empty($escala['hora_almoco_inicio']) && !empty($escala['hora_almoco_fim'])): ?>
                                        <div class="flex items-start gap-2">
                                            <i class="ph ph-coffee mt-0.5 text-slate-500"></i> 
                                            <span>Intervalo: <?php echo substr($escala['hora_almoco_inicio'], 0, 5); ?> às <?php echo substr($escala['hora_almoco_fim'], 0, 5); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="pt-4 border-t border-slate-700/50 flex justify-between items-center">
                                        <div class="flex items-center gap-2 text-xs font-medium text-slate-400">
                                            <i class="ph ph-users text-lg"></i> <?php echo $escala['total_funcionarios']; ?> colaboradores
                                        </div>
                                        <button onclick="abrirModalEscala(<?php echo htmlspecialchars(json_encode($escala)); ?>)" class="text-blue-400 hover:text-blue-300 text-sm font-medium flex items-center gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                            <i class="ph ph-pencil-simple"></i> Editar
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-span-full py-12 text-center text-slate-500 border-2 border-dashed border-slate-700 rounded-2xl">
                                    <i class="ph ph-calendar-x text-4xl mb-3 opacity-50"></i>
                                    <p class="text-sm">Nenhuma escala cadastrada. Clique em "Criar Nova Escala" para começar.</p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL CRIAR/EDITAR ESCALA -->
            <div id="modalEscala" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
                <div class="bg-slate-800 border border-slate-700 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]" id="modalEscalaContent">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800 shrink-0">
                        <h2 class="text-lg font-semibold text-white" id="modalEscalaTitle">Nova Escala</h2>
                        <button onclick="fecharModalEscala()" class="text-slate-400 hover:text-red-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
                    </div>
                    <form action="actions/salvar_escala.php" method="POST" id="formEscala" class="p-6 overflow-y-auto">
                        <input type="hidden" name="escala_id" id="escala_id">
                        
                        <div class="mb-4">
                            <label for="nome" class="block text-sm font-medium text-slate-300 mb-1.5">Nome da Escala</label>
                            <input type="text" id="nome" name="nome" placeholder="Ex: Comercial / Escritório" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm placeholder:text-slate-600">
                        </div>
                        
                        <div class="mb-5">
                            <label for="dias_trabalho" class="block text-sm font-medium text-slate-300 mb-1.5">Dias de Trabalho (Texto)</label>
                            <input type="text" id="dias_trabalho" name="dias_trabalho" placeholder="Ex: Segunda a Sexta" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm placeholder:text-slate-600">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="hora_entrada" class="block text-sm font-medium text-slate-300 mb-1.5">Entrada Padrão</label>
                                <input type="time" id="hora_entrada" name="hora_entrada" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm">
                            </div>
                            <div>
                                <label for="hora_saida" class="block text-sm font-medium text-slate-300 mb-1.5">Saída Padrão</label>
                                <input type="time" id="hora_saida" name="hora_saida" required class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm">
                            </div>
                        </div>

                        <div class="mb-4 p-4 rounded-lg bg-slate-900/30 border border-slate-700/50">
                            <label class="block text-sm font-medium text-slate-400 mb-3 flex items-center gap-2">
                                <i class="ph ph-coffee"></i> Intervalo de Almoço / Pausa
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="hora_almoco_inicio" class="block text-xs text-slate-500 mb-1">Início</label>
                                    <input type="time" id="hora_almoco_inicio" name="hora_almoco_inicio" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm">
                                </div>
                                <div>
                                    <label for="hora_almoco_fim" class="block text-xs text-slate-500 mb-1">Fim</label>
                                    <input type="time" id="hora_almoco_fim" name="hora_almoco_fim" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-blue-500 transition-colors text-sm">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" onclick="fecharModalEscala()" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-500 transition-colors shadow-lg shadow-blue-500/20">Salvar Escala</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL EXCLUIR ESCALA -->
            <div id="modalExcluirEscala" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
                <div class="bg-slate-800 border border-slate-700 w-full max-w-sm rounded-2xl shadow-2xl overflow-y-auto max-h-[90vh] p-6 text-center transform scale-95 transition-transform duration-300" id="modalExcluirContent">
                    <div class="w-16 h-16 rounded-full bg-red-500/20 text-red-500 flex items-center justify-center mx-auto mb-4">
                        <i class="ph ph-warning-circle text-4xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white mb-2">Excluir Escala</h2>
                    <p class="text-slate-400 text-sm mb-6">Tem certeza que deseja excluir esta escala? Os usuários vinculados a ela ficarão sem escala padrão (livre).</p>
                    
                    <form action="actions/excluir_escala.php" method="POST" id="formExcluirEscala">
                        <input type="hidden" id="excluir_escala_id" name="escala_id">
                        <div class="flex justify-center gap-3">
                            <button type="button" onclick="fecharModalExcluir()" class="px-5 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-500 transition-colors shadow-lg shadow-red-500/20">Sim, Excluir</button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>

    <script>
        function abrirModalEscala(escalaObj = null) {
            const modal = document.getElementById('modalEscala');
            const content = document.getElementById('modalEscalaContent');
            const title = document.getElementById('modalEscalaTitle');
            const form = document.getElementById('formEscala');
            
            form.reset();
            
            if (escalaObj) {
                title.innerText = 'Editar Escala';
                document.getElementById('escala_id').value = escalaObj.id;
                document.getElementById('nome').value = escalaObj.nome;
                document.getElementById('dias_trabalho').value = escalaObj.dias_trabalho;
                document.getElementById('hora_entrada').value = escalaObj.hora_entrada.substring(0, 5);
                document.getElementById('hora_saida').value = escalaObj.hora_saida.substring(0, 5);
                
                if (escalaObj.hora_almoco_inicio) {
                    document.getElementById('hora_almoco_inicio').value = escalaObj.hora_almoco_inicio.substring(0, 5);
                    document.getElementById('hora_almoco_fim').value = escalaObj.hora_almoco_fim.substring(0, 5);
                }
            } else {
                title.innerText = 'Nova Escala';
                document.getElementById('escala_id').value = '';
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function fecharModalEscala() {
            const modal = document.getElementById('modalEscala');
            const content = document.getElementById('modalEscalaContent');
            
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function abrirModalExcluir(id) {
            const modal = document.getElementById('modalExcluirEscala');
            const content = document.getElementById('modalExcluirContent');
            
            document.getElementById('excluir_escala_id').value = id;
            
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.classList.add('opacity-100');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function fecharModalExcluir() {
            const modal = document.getElementById('modalExcluirEscala');
            const content = document.getElementById('modalExcluirContent');
            
            modal.classList.remove('opacity-100');
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
</html>
