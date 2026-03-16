<?php
require 'config/database.php';
session_start();
$_SESSION['logado'] = true;
$_SESSION['nivel_acesso'] = 'admin';
$_SESSION['permissoes'] = ['aprovar_solicitacoes'];
$_SESSION['usuario_id'] = 1; // Admin Teste

require 'app/Controllers/Api/ApiSolicitacoesController.php';

// Mock atualizarSolicitacao to simulate admin.js sending a rejection reason
class TestApiSolicitacoesController extends App\Controllers\Api\ApiSolicitacoesController {
    public function atualizarSolicitacao() {
        $input = ['id' => 9, 'status' => 'recusada', 'motivo_recusa' => 'Falta o atestado médico anexado.'];
        
        $id = $input['id'] ?? null;
        $novoStatus = $input['status'] ?? null;
        $motivoRecusa = $input['motivo_recusa'] ?? null;
        $adminId = $_SESSION['usuario_id'];

        if ($novoStatus === 'aprovada') $motivoRecusa = null;

        try {
            $pdo = Config\Database::getConnection();
            $stmt = $pdo->prepare("UPDATE solicitacoes SET status = ?, motivo_recusa = ?, atualizada_por = ?, atualizada_em = NOW() WHERE id = ?");
            $success = $stmt->execute([$novoStatus, $motivoRecusa, $adminId, $id]);
            echo json_encode(['sucesso' => $success]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
}

ob_start();
$controller = new TestApiSolicitacoesController();
$controller->atualizarSolicitacao();
$output = ob_get_clean();

echo "POST RESPONSE:\n";
var_dump($output);

// Also let's check the row to see if it changed
$pdo = Config\Database::getConnection();
$stmt = $pdo->query("SELECT id, status, motivo_recusa FROM solicitacoes WHERE id = 9");
var_dump($stmt->fetch(PDO::FETCH_ASSOC));
