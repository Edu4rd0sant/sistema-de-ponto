<?php
require_once __DIR__ . '/../config/session.php';
require_once '../config/database.php';

$is_admin = ($_SESSION['nivel_acesso'] === 'admin');
$pode_ajustar = $is_admin || (is_array($_SESSION['permissoes']??null) && in_array('ajustar_pontos', $_SESSION['permissoes']));

if (!isset($_SESSION['logado']) || !$pode_ajustar) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_POST['usuario_id'] ?? null;
    $data_registro = $_POST['data_registro'] ?? null;
    $hora_registro = $_POST['hora_registro'] ?? null;
    $tipo_registro = $_POST['tipo_registro'] ?? null;

    if (!$usuario_id || !$data_registro || !$hora_registro || !$tipo_registro) {
        // Redireciona com erro
        header("Location: ../admin.php?erro=dados_invalidos");
        exit;
    }

    // Combina data e hora
    $data_hora = $data_registro . ' ' . $hora_registro . ':00';

    try {
        $stmt = $pdo->prepare("INSERT INTO registros_ponto (usuario_id, tipo, data_hora) VALUES (:usuario_id, :tipo, :data_hora)");
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':tipo', $tipo_registro);
        $stmt->bindParam(':data_hora', $data_hora);
        
        $stmt->execute();

        header("Location: ../admin.php?sucesso=ponto_salvo");
        exit;
    } catch (PDOException $e) {
        die("Erro ao salvar ponto: " . $e->getMessage());
    }
} else {
    header("Location: ../admin.php");
    exit;
}
?>
