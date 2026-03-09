<?php
require_once __DIR__ . '/../config/session.php';
// Chama o arquivo de conexão que o Antigravity gerou
require_once '../config/database.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    // Verifica se os campos não estão vazios
    if (empty($email) || empty($senha)) {
        header("Location: ../login.php?erro=vazio");
        exit;
    }

    try {
        // Busca o usuário no banco sistemaponto
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, nivel_acesso, cargo, permissoes FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se o usuário existe e se a senha bate (suporta hash BCRYPT)
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            
            // Cria a sessão com os dados reais do banco
            $_SESSION['logado'] = true;
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
            $_SESSION['cargo'] = $usuario['cargo'] ?? 'Empregado';
            
            $permissoesRaw = [];
            if (!empty($usuario['permissoes'])) {
                $decoded = json_decode($usuario['permissoes'], true);
                if (is_array($decoded)) {
                    $permissoesRaw = $decoded;
                }
            }
            $_SESSION['permissoes'] = $permissoesRaw;

            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            // Roteamento inteligente baseado no nível de acesso do banco
            if ($usuario['nivel_acesso'] === 'admin') {
                header("Location: ../admin.php");
                exit;
            } else {
                header("Location: ../index.php");
                exit;
            }
            
        } else {
            // E-mail ou senha incorretos
            header("Location: ../login.php?erro=invalido");
            exit;
        }
    } catch (PDOException $e) {
        die("Erro no sistema: " . $e->getMessage());
    }
} else {
    header("Location: ../login.php");
    exit;
}
?>
