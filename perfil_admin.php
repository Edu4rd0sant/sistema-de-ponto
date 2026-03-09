<?php
require_once 'config/session.php';
require_once 'config/database.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
try {
    $stmt = $pdo->prepare("SELECT nome, email, criado_em FROM usuarios WHERE id = :uid LIMIT 1");
    $stmt->bindParam(':uid', $usuario_id);
    $stmt->execute();
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $perfil = null;
    $erro_db = "Erro ao carregar perfil: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Primus Point</title>
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

            <div class="p-8 pb-12 flex-1 relative z-10 flex flex-col items-center">
                
                <div class="w-full max-w-4xl bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 shadow-2xl mt-4 relative overflow-hidden">
                    <!-- Fundo decorativo -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-red-900/10 blur-[80px] rounded-full pointer-events-none"></div>

                    <?php if($perfil): ?>
                        
                        <!-- Header do Perfil -->
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 mb-10 pb-8 border-b border-slate-700/50 relative z-10">
                            <!-- Avatar Admin -->
                            <div class="w-24 h-24 rounded-full bg-red-600 flex items-center justify-center text-4xl font-bold text-white shadow-[0_0_0_4px_rgba(15,23,42,1),0_0_0_6px_rgba(51,65,85,1)] shrink-0">
                                <?php echo strtoupper(substr($perfil['nome'], 0, 2)); ?>
                            </div>
                            
                            <div class="text-center md:text-left mt-2 flex-1">
                                <h2 class="text-2xl font-bold text-white tracking-tight leading-tight"><?php echo htmlspecialchars($perfil['nome']); ?></h2>
                                <div class="inline-flex items-center gap-1.5 bg-red-500/10 text-red-500 border border-red-500/20 px-2.5 py-1 rounded-full text-xs font-semibold mt-2">
                                    <i class="ph ph-shield-check text-sm"></i> Administrador de Sistema
                                </div>
                            </div>
                        </div>

                        <!-- Grid de Infos -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                            <!-- E-mail -->
                            <div class="bg-slate-900/40 border border-slate-700/50 p-5 rounded-xl hover:bg-slate-800 transition-colors">
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2"><i class="ph ph-envelope-simple text-slate-500"></i> E-mail de Acesso</span>
                                <span class="text-base font-medium text-slate-200"><?php echo htmlspecialchars($perfil['email']); ?></span>
                            </div>
                            
                            <!-- Criação Conta -->
                            <div class="bg-slate-900/40 border border-slate-700/50 p-5 rounded-xl hover:bg-slate-800 transition-colors">
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2"><i class="ph ph-calendar-plus text-slate-500"></i> Conta Criada Em</span>
                                <span class="text-base font-medium text-slate-200">
                                    <?php 
                                        $data_criacao = new DateTime($perfil['criado_em']);
                                        echo $data_criacao->format('d/m/Y \à\s H:i');
                                    ?>
                                </span>
                            </div>

                            <!-- ID Mestre -->
                            <div class="bg-slate-900/40 border border-slate-700/50 p-5 rounded-xl hover:bg-slate-800 transition-colors">
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2"><i class="ph ph-identification-card text-slate-500"></i> ID Mestre</span>
                                <span class="text-base font-mono text-slate-300">#<?php echo str_pad($_SESSION['usuario_id'], 6, "0", STR_PAD_LEFT); ?></span>
                            </div>

                            <!-- Nível Privilegio -->
                            <div class="bg-slate-900/40 border border-slate-700/50 p-5 rounded-xl hover:bg-slate-800 transition-colors">
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2"><i class="ph ph-lock-key text-slate-500"></i> Nível de Privilégio</span>
                                <span class="text-base font-medium text-red-400 flex items-center gap-1"><i class="ph ph-star-fill text-sm"></i> Acesso Máximo</span>
                            </div>
                        </div>

                        <!-- Ações -->
                        <div class="mt-10 pt-6 border-t border-slate-700/50 flex justify-end relative z-10">
                            <button onclick="abrirModalSenha()" class="px-5 py-2.5 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors flex items-center gap-2">
                                <i class="ph ph-key"></i> Alterar Minha Senha
                            </button>
                        </div>

                    <?php else: ?>
                        <div class="flex items-center justify-center p-12 text-red-400 bg-red-900/20 rounded-xl border border-red-500/20">
                            <i class="ph ph-warning-circle text-2xl mr-3"></i>
                            <span class="font-medium"><?php echo $erro_db ?? 'Erro desconhecido.'; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- MODAL ALTERAR SENHA (TAILWIND) -->
            <div id="modalSenhaSelf" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-[100] items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
                <div class="bg-slate-800 border border-slate-700 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300 modal-content-anim">
                    <div class="px-6 py-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800">
                        <h2 class="text-lg font-semibold text-white">Alterar Senha do Administrador</h2>
                        <button onclick="fecharModalSenha()" class="text-slate-400 hover:text-red-400 transition-colors"><i class="ph ph-x text-xl"></i></button>
                    </div>
                    
                    <form action="actions/alterar_senha_self.php" method="POST" id="formSenhaSelf" class="p-6">
                        
                        <!-- Mensagens de Feedback PHP dinâmicas -->
                        <?php if(isset($_GET['erro']) && $_GET['erro'] == 'senha_atual_incorreta'): ?>
                            <div class="bg-red-500/10 text-red-400 border border-red-500/20 px-4 py-3 rounded-lg mb-6 text-sm flex items-start gap-3">
                                <i class="ph ph-warning-circle text-lg mt-0.5"></i>
                                <span>A senha atual que você digitou está incorreta.</span>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($_GET['sucesso']) && $_GET['sucesso'] == 'senha_alterada'): ?>
                            <div class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-4 py-3 rounded-lg mb-6 text-sm flex items-start gap-3">
                                <i class="ph ph-check-circle text-lg mt-0.5"></i>
                                <span>Sua senha foi alterada com sucesso!</span>
                            </div>
                        <?php endif; ?>

                        <div class="mb-6">
                            <label for="nova_senha" class="block text-sm font-medium text-slate-300 mb-1.5 flex justify-between">
                                Nova Senha <span class="text-slate-500 text-xs font-normal">Mín. 6 caracteres</span>
                            </label>
                            <input type="password" id="nova_senha" name="nova_senha" required minlength="6" class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 focus:outline-none focus:border-red-500 transition-colors text-sm placeholder:text-slate-600">
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="fecharModalSenha()" class="px-4 py-2 text-sm font-medium text-slate-300 border border-slate-600 rounded-lg hover:bg-slate-700 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-500 transition-colors shadow-lg shadow-red-500/20">Salvar Nova Senha</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                function abrirModalSenha() {
                    const modal = document.getElementById('modalSenhaSelf');
                    const content = modal.querySelector('.modal-content-anim');
                    modal.classList.remove('hidden');
                    // Timeout para permitir que a classe removal (display: block) seja computada antes do transition
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        content.classList.remove('scale-95');
                        content.classList.add('scale-100');
                    }, 10);
                }

                function fecharModalSenha() {
                    const modal = document.getElementById('modalSenhaSelf');
                    const content = modal.querySelector('.modal-content-anim');
                    modal.classList.add('opacity-0');
                    content.classList.remove('scale-100');
                    content.classList.add('scale-95');
                    
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        document.getElementById('formSenhaSelf').reset();
                    }, 300); // 300ms = duration tailwind
                }

                window.onload = function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    if(urlParams.has('erro') || urlParams.has('sucesso')) {
                        // Se houver redirect GET, abra o modal automaticamente para ver a mensagem
                        abrirModalSenha();
                    }
                };
            </script>
        </main>
    </div>
</body>
</html>
