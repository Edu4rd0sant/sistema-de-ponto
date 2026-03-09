<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

// Apenas Admin ou funcionário com permissão pode atualizar status de solicitações
$is_admin = ($_SESSION['nivel_acesso'] === 'admin');
$pode_aprovar = $is_admin || (is_array($_SESSION['permissoes']??null) && in_array('aprovar_solicitacoes', $_SESSION['permissoes']));

if (!isset($_SESSION['logado']) || !$pode_aprovar) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'erro' => 'Método inválido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Dados incompletos']);
    exit;
}

$id = intval($input['id']);
$status = strtolower(trim($input['status']));

// Valida status
$status_validos = ['aprovada', 'recusada'];
if (!in_array($status, $status_validos)) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'erro' => 'Status inválido']);
    exit;
}

try {
    // Buscar informações da solicitação primeiro
    $stmtSelect = $pdo->prepare("SELECT usuario_id, tipo FROM solicitacoes WHERE id = :id");
    $stmtSelect->execute([':id' => $id]);
    $solicitacao = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$solicitacao) {
        http_response_code(404);
        echo json_encode(['sucesso' => false, 'erro' => 'Solicitação não encontrada']);
        exit;
    }

    $pdo->beginTransaction();

    $query = "UPDATE solicitacoes SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':status' => $status, ':id' => $id]);
    
    // Mudança automática de status caso seja de Férias e tenha sido aprovada
    if ($status === 'aprovada' && $solicitacao['tipo'] === 'ferias') {
        $stmtUpdateUser = $pdo->prepare("UPDATE usuarios SET status_trabalho = 'Férias' WHERE id = :usuario_id");
        $stmtUpdateUser->execute([':usuario_id' => $solicitacao['usuario_id']]);
    }
    
    $pdo->commit();

    echo json_encode(['sucesso' => true, 'mensagem' => 'Status atualizado com sucesso']);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao atualizar solicitação: ' . $e->getMessage()]);
}
