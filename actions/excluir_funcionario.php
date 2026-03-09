<?php
require_once __DIR__ . '/../config/session.php';
require_once '../config/database.php';

// Apenas admins podem acessar
if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    
    if (!$id_usuario) {
        header("Location: ../admin.php?erro=usuario_invalido");
        exit;
    }

    // Impede o admin de excluir a si próprio acidentalmente
    if ($id_usuario == $_SESSION['usuario_id']) {
        header("Location: ../admin.php?erro=autoexclusao");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Excluir solicitações do funcionário
        $stmt_solic = $pdo->prepare("DELETE FROM solicitacoes WHERE usuario_id = :id");
        $stmt_solic->bindParam(':id', $id_usuario);
        $stmt_solic->execute();

        // 2. Excluir registros de ponto do funcionário
        $stmt_ponto = $pdo->prepare("DELETE FROM registros_ponto WHERE usuario_id = :id");
        $stmt_ponto->bindParam(':id', $id_usuario);
        $stmt_ponto->execute();

        // 3. Excluir o funcionário (usuário) em si
        $stmt_user = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND nivel_acesso != 'admin'");
        // Nota: A cláusula AND nivel_acesso != 'admin' impede a exclusão de outros admins por segurança.
        // Se quiser que admins excluam admins, remova essa segunda condição.
        $stmt_user->bindParam(':id', $id_usuario);
        $stmt_user->execute();
        
        // Se nenhuma linha foi afetada no usuário, talvez ele fosse admin
        if ($stmt_user->rowCount() == 0) {
            $pdo->rollBack();
            header("Location: ../admin.php?erro=exclusao_nao_permitida");
            exit;
        }

        $pdo->commit();
        header("Location: ../admin.php?sucesso=funcionario_excluido");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Erro crítico ao excluir funcionário: " . $e->getMessage());
    }
} else {
    header("Location: ../admin.php");
    exit;
}
?>
