<?php
require_once '../config/session.php';
require_once '../config/database.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'funcionario') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $tipo = trim($_POST['tipo_solicitacao'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (empty($tipo) || empty($descricao)) {
        header("Location: ../solicitacoes.php?erro=preencha_todos");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO solicitacoes (usuario_id, tipo, descricao, solicitada_em, status) VALUES (:uid, :tipo, :desc, NOW(), 'pendente')");
        // Update table query to match what is actually written
        $stmt = $pdo->prepare("INSERT INTO solicitacoes (usuario_id, tipo, descricao) VALUES (:uid, :tipo, :desc)");
        $stmt->bindParam(':uid', $usuario_id);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':desc', $descricao);
        
        $stmt->execute();
        
        header("Location: ../solicitacoes.php?sucesso=solicitacao_enviada");
        exit;
    } catch (PDOException $e) {
        die("Erro banco de dados: " . $e->getMessage());
    }
} else {
    header("Location: ../solicitacoes.php");
    exit;
}
?>
