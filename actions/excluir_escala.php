<?php
require_once __DIR__ . '/../config/session.php';
require_once '../config/database.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_input(INPUT_POST, 'escala_id', FILTER_VALIDATE_INT);

    if (!$id) {
        header("Location: ../escalas.php?erro=ID Inválido");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM escalas WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        header("Location: ../escalas.php?sucesso=1");
        exit;
    } catch (PDOException $e) {
        die("Erro ao excluir escala: " . $e->getMessage());
    }
} else {
    header("Location: ../escalas.php");
    exit;
}
?>
