<?php
session_start();
if (isset($_SESSION['logado'])) {
    if ($_SESSION['nivel_acesso'] === 'admin') {
        header("Location: admin.php");
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Primus Point</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
<body class="bg-slate-900 text-slate-200 font-sans min-h-screen flex items-center justify-center p-4">

    <!-- Fundo de Blur/Gradiente (Opcional para dar cara de app moderno) -->
    <div class="fixed inset-0 w-full h-full pointer-events-none z-0 overflow-hidden">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-blue-900/20 blur-[120px] rounded-full"></div>
        <div class="absolute bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-indigo-900/20 blur-[100px] rounded-full"></div>
    </div>

    <div class="w-full max-w-md bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-2xl p-8 z-10 relative">
        <div class="text-center mb-8">
            <img src="primuslogocompleta.png" alt="Primus Bank" class="h-16 mx-auto mb-6 brightness-0 invert opacity-90">
            <h1 class="text-2xl font-bold text-white tracking-tight">Portal Corporativo</h1>
            <p class="text-slate-400 text-sm mt-2">Acesse com suas credenciais Primus</p>
        </div>

        <?php if (isset($_GET['erro'])): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm font-medium px-4 py-3 rounded-lg mb-6">
                <?php 
                    if ($_GET['erro'] == 'invalido') echo 'E-mail ou senha incorretos.';
                    elseif ($_GET['erro'] == 'vazio') echo 'Por favor, preencha todos os campos.';
                    else echo 'Erro ao realizar login.';
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'senha_alterada'): ?>
            <div class="bg-green-500/10 border border-green-500/20 text-green-400 text-sm font-medium px-4 py-3 rounded-lg mb-6">
                Senha alterada com sucesso. Faça login.
            </div>
        <?php endif; ?>

        <form action="actions/login_action.php" method="POST" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">E-mail Corporativo</label>
                <div class="relative">
                    <input type="email" id="email" name="email" required 
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-slate-600"
                        placeholder="nome@primus.com">
                </div>
            </div>

            <div>
                <label for="senha" class="block text-sm font-medium text-slate-300 mb-1.5">Senha</label>
                <div class="relative">
                    <input type="password" id="senha" name="senha" required 
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-slate-600"
                        placeholder="••••••••">
                </div>
                <div class="flex justify-end mt-2">
                    <a href="#" class="text-sm text-blue-400 hover:text-blue-300 transition-colors">Esqueceu a senha?</a>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 px-4 rounded-lg shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 active:translate-y-0 mt-4">
                Entrar no Sistema
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-700/50 text-center">
            <p class="text-xs text-slate-500">&copy; <?php echo date('Y'); ?> Primus Bank. Protegido por criptografia de ponta a ponta.</p>
        </div>
    </div>
</body>
</html>
