<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['logado'])) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $senha_atual = trim($_POST['senha_atual'] ?? '');
    $nova_senha = trim($_POST['nova_senha'] ?? '');

    // Verifica de onde veio pra redirecionar certo
    $redirect_url = ($_SESSION['nivel_acesso'] === 'admin') ? '../perfil_admin.php' : '../perfil.php';

    if (empty($nova_senha)) {
        header("Location: " . $redirect_url . "?erro=preencha_todos");
        exit;
    }

    if (strlen($nova_senha) < 6) {
        header("Location: " . $redirect_url . "?erro=senha_curta");
        exit;
    }

    try {
        // Gera nova hash BCRYPT
        $nova_hash = password_hash($nova_senha, PASSWORD_BCRYPT);

        $stmt_update = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :uid");
        $stmt_update->bindParam(':senha', $nova_hash);
        $stmt_update->bindParam(':uid', $usuario_id);
        $stmt_update->execute();

        header("Location: " . $redirect_url . "?sucesso=senha_alterada");
        exit;

    } catch (PDOException $e) {
        die("Erro banco de dados: " . $e->getMessage());
    }
}
?>
