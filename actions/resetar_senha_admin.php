<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

// Bloqueio duplo: só admins ou func. com permissão têm acesso a esse script
$is_admin = ($_SESSION['nivel_acesso'] === 'admin');
$pode_gerenciar = $is_admin || (is_array($_SESSION['permissoes']??null) && in_array('gerenciar_senhas', $_SESSION['permissoes']));

if (!isset($_SESSION['logado']) || !$pode_gerenciar) {
    die("Acesso Negado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_alvo = (int)($_POST['usuario_id'] ?? 0);
    $nova_senha_admin = trim($_POST['nova_senha'] ?? '');

    if (empty($usuario_alvo) || empty($nova_senha_admin)) {
        header("Location: ../admin.php?erro=faltam_dados_senha");
        exit;
    }

    if (strlen($nova_senha_admin) < 6) {
        header("Location: ../admin.php?erro=senha_curta");
        exit;
    }

    try {
        // Garantir que não estamos alterando um Super Admin sem querer a não ser que seja um sistema maior
        // Para simplificar, altera qualquer usuario enviado pelo admin
        $nova_hash = password_hash($nova_senha_admin, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :uid");
        $stmt->bindParam(':senha', $nova_hash);
        $stmt->bindParam(':uid', $usuario_alvo);
        $stmt->execute();

        header("Location: ../admin.php?sucesso=senha_forçada");
        exit;

    } catch (PDOException $e) {
        die("Erro ao forçar senha: " . $e->getMessage());
    }
} else {
    header("Location: ../admin.php");
    exit;
}
?>
