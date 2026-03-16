<?php
namespace App\Controllers;

use Config\Database;
use PDO;

class AuthController {
    
    // Mostra a tela de login
    public function index() {
        
        if (isset($_SESSION['logado'])) {
            if ($_SESSION['nivel_acesso'] === 'admin') {
                header("Location: /admin");
                exit;
            } else {
                header("Location: /funcionario");
                exit;
            }
        }
        
        // Inclui a View do formulário HTML
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    // Processa o POST do fomulário
    public function login() {
        
        
        // Proteção contra chamadas GET
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /login");
            exit;
        }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            header("Location: /login?erro=vazio");
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT id, nome, senha, cargo, nivel_acesso, permissoes FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica a senha e prossegue igual ao login legado
            if ($user && password_verify($senha, $user['senha'])) {
                $_SESSION['logado'] = true;
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['cargo'] = $user['cargo'];
                $_SESSION['nivel_acesso'] = $user['nivel_acesso'];
                $_SESSION['permissoes'] = !empty($user['permissoes']) ? json_decode($user['permissoes'], true) : [];

                // Redireciona para as Novas Rotas MVC
                if ($_SESSION['nivel_acesso'] === 'admin') {
                    header("Location: /admin");
                } else {
                    header("Location: /funcionario");
                }
                exit;
            } else {
                header("Location: /login?erro=invalido");
                exit;
            }
        } catch (\PDOException $e) {
            header("Location: /login?erro=banco");
            error_log($e->getMessage()); // log silently
            exit;
        }
    }

    public function logout() {
        session_destroy();
        header("Location: /login");
        exit;
    }

    public function alterarSenha() {
        if (!isset($_SESSION['logado'])) { header("Location: /login"); exit; }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $usuario_id = $_SESSION['usuario_id'];
            $senha_atual = trim($_POST['senha_atual'] ?? '');
            $nova_senha = trim($_POST['nova_senha'] ?? '');
            $redirect_url = ($_SESSION['nivel_acesso'] === 'admin') ? '/admin/perfil' : '/funcionario/perfil';
            if (empty($nova_senha)) { header("Location: " . $redirect_url . "?erro=preencha_todos"); exit; }
            if (strlen($nova_senha) < 6) { header("Location: " . $redirect_url . "?erro=senha_curta"); exit; }
            
            $pdo = Database::getConnection();
            try {
                $nova_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
                $stmt_update = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :uid");
                $stmt_update->bindParam(':senha', $nova_hash);
                $stmt_update->bindParam(':uid', $usuario_id);
                $stmt_update->execute();
                header("Location: " . $redirect_url . "?sucesso=senha_alterada"); exit;
            } catch (\PDOException $e) { die("Erro banco de dados: " . $e->getMessage()); }
        }
        $redirect_url = ($_SESSION['nivel_acesso'] === 'admin') ? '/admin/perfil' : '/funcionario/perfil';
        header("Location: " . $redirect_url); exit;
    }
}

