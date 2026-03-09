<?php
require_once __DIR__ . '/../config/session.php';
require_once '../config/database.php';

if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = filter_input(INPUT_POST, 'escala_id', FILTER_VALIDATE_INT);
    $nome = trim($_POST['nome'] ?? '');
    $dias_trabalho = trim($_POST['dias_trabalho'] ?? '');
    $hora_entrada = trim($_POST['hora_entrada'] ?? '');
    $hora_saida = trim($_POST['hora_saida'] ?? '');
    $hora_almoco_inicio = !empty($_POST['hora_almoco_inicio']) ? trim($_POST['hora_almoco_inicio']) : null;
    $hora_almoco_fim = !empty($_POST['hora_almoco_fim']) ? trim($_POST['hora_almoco_fim']) : null;

    if (empty($nome) || empty($dias_trabalho) || empty($hora_entrada) || empty($hora_saida)) {
        header("Location: ../escalas.php?erro=Campos obrigatórios ausentes");
        exit;
    }

    try {
        if ($id) {
            // Edição
            $stmt = $pdo->prepare("UPDATE escalas SET nome = :nome, dias_trabalho = :dias_trabalho, hora_entrada = :hora_entrada, hora_saida = :hora_saida, hora_almoco_inicio = :hora_almoco_inicio, hora_almoco_fim = :hora_almoco_fim WHERE id = :id");
            $stmt->bindParam(':id', $id);
        } else {
            // Criação
            $stmt = $pdo->prepare("INSERT INTO escalas (nome, dias_trabalho, hora_entrada, hora_saida, hora_almoco_inicio, hora_almoco_fim) VALUES (:nome, :dias_trabalho, :hora_entrada, :hora_saida, :hora_almoco_inicio, :hora_almoco_fim)");
        }
        
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':dias_trabalho', $dias_trabalho);
        $stmt->bindParam(':hora_entrada', $hora_entrada);
        $stmt->bindParam(':hora_saida', $hora_saida);
        $stmt->bindParam(':hora_almoco_inicio', $hora_almoco_inicio);
        $stmt->bindParam(':hora_almoco_fim', $hora_almoco_fim);
        
        $stmt->execute();
        
        header("Location: ../escalas.php?sucesso=1");
        exit;
    } catch (PDOException $e) {
        die("Erro ao salvar escala: " . $e->getMessage());
    }
} else {
    header("Location: ../escalas.php");
    exit;
}
?>
