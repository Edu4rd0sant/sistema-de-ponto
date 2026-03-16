<?php
namespace App\Controllers\Api;

use Config\Database;
use PDO;
use PDOException;

class ApiSolicitacoesController {

    public function getSolicitacoes() {
        
        header('Content-Type: application/json');

        if (!isset($_SESSION['logado']) || ($_SESSION['nivel_acesso'] !== 'admin' && !in_array('aprovar_solicitacoes', $_SESSION['permissoes']??[]))) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SELECT s.id, s.tipo, s.descricao, s.solicitada_em, s.status, u.nome as nome_funcionario FROM solicitacoes s JOIN usuarios u ON s.usuario_id = u.id WHERE s.status = 'pendente' ORDER BY s.solicitada_em ASC");
            $solicitacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'sucesso' => true,
                'count' => count($solicitacoes),
                'data' => $solicitacoes
            ]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }

    public function atualizarSolicitacao() {
        
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido']);
            exit;
        }

        if (!isset($_SESSION['logado']) || ($_SESSION['nivel_acesso'] !== 'admin' && !in_array('aprovar_solicitacoes', $_SESSION['permissoes']??[]))) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $novoStatus = $input['status'] ?? null;
        $motivoRecusa = $input['motivo_recusa'] ?? null;
        $adminId = $_SESSION['usuario_id'];

        if (!$id || !in_array($novoStatus, ['aprovada', 'recusada'])) {
            http_response_code(400);
            echo json_encode(['sucesso' => false, 'erro' => 'Dados inválidos']);
            exit;
        }

        if ($novoStatus === 'aprovada') {
            $motivoRecusa = null; // Clear reason if approved
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE solicitacoes SET status = ?, motivo_recusa = ?, atualizada_por = ?, atualizada_em = NOW(), lida_pelo_funcionario = 0 WHERE id = ?");
            $success = $stmt->execute([$novoStatus, $motivoRecusa, $adminId, $id]);
            echo json_encode(['sucesso' => $success]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    public function checarNotificacoesFuncionario() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['logado'])) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitacoes WHERE usuario_id = ? AND status != 'pendente' AND lida_pelo_funcionario = 0");
            $stmt->execute([$_SESSION['usuario_id']]);
            $count = $stmt->fetchColumn();

            $stmtData = $pdo->prepare("SELECT id, tipo, solicitada_em, descricao, status, motivo_recusa, atualizada_em FROM solicitacoes WHERE usuario_id = ? AND status != 'pendente' AND lida_pelo_funcionario = 0 ORDER BY atualizada_em DESC");
            $stmtData->execute([$_SESSION['usuario_id']]);
            $data = $stmtData->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'sucesso' => true,
                'count' => (int)$count,
                'data' => $data
            ]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }

    public function marcarLidasFuncionario() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['sucesso' => false, 'erro' => 'Método não permitido']);
            exit;
        }

        if (!isset($_SESSION['logado'])) {
            http_response_code(403);
            echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
            exit;
        }

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("UPDATE solicitacoes SET lida_pelo_funcionario = 1 WHERE usuario_id = ? AND status != 'pendente' AND lida_pelo_funcionario = 0");
            $success = $stmt->execute([$_SESSION['usuario_id']]);
            
            echo json_encode(['sucesso' => $success]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }
}

