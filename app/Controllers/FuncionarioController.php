<?php
namespace App\Controllers;

class FuncionarioController {

    public function dashboard() {
        
        
        // Proteção de Rota Funcionário
        if (!isset($_SESSION['logado'])) {
            header("Location: /login");
            exit;
        }

        // Variáveis que a view pode precisar (ex: se precisássemos pegar saldo de banco de horas aqui)
        
        require_once __DIR__ . '/../Views/funcionario/dashboard.php';
    }

    public function historico() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] === 'admin') { header("Location: /login"); exit; }
        $pdo = \Config\Database::getConnection();

        try {
            $stmt = $pdo->prepare("SELECT * FROM registros_ponto WHERE usuario_id = ? ORDER BY data_hora DESC");
            $stmt->execute([$_SESSION['usuario_id']]);
            $historico = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $historico = [];
            $erro_db = "Erro ao carregar o histórico: " . $e->getMessage();
        }

        require_once __DIR__ . '/../Views/funcionario/historico.php';
    }

    public function solicitacoes() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] === 'admin') { header("Location: /login"); exit; }
        $pdo = \Config\Database::getConnection();

        try {
            $stmt = $pdo->prepare("SELECT * FROM solicitacoes WHERE usuario_id = ? ORDER BY data_solicitacao DESC");
            $stmt->execute([$_SESSION['usuario_id']]);
            $minhas_solicitacoes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            $minhas_solicitacoes = [];
        }

        require_once __DIR__ . '/../Views/funcionario/solicitacoes.php';
    }

    public function criarSolicitacao() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] === 'admin') { header("Location: /login"); exit; }
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $usuario_id = $_SESSION['usuario_id'];
            $tipo = trim($_POST['tipo_solicitacao'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if (empty($tipo) || empty($descricao)) {
                header("Location: /funcionario/solicitacoes?erro=preencha_todos");
                exit;
            }

            $pdo = \Config\Database::getConnection();
            try {
                $stmt = $pdo->prepare("INSERT INTO solicitacoes (usuario_id, tipo, descricao) VALUES (:uid, :tipo, :desc)");
                $stmt->bindParam(':uid', $usuario_id);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':desc', $descricao);
                $stmt->execute();
                
                header("Location: /funcionario/solicitacoes?sucesso=solicitacao_enviada");
                exit;
            } catch (\PDOException $e) {
                die("Erro banco de dados: " . $e->getMessage());
            }
        }
        
        header("Location: /funcionario/solicitacoes");
        exit;
    }

    public function perfil() {
        if (!isset($_SESSION['logado'])) { header("Location: /login"); exit; }
        $pdo = \Config\Database::getConnection();

        try {
            $stmt = $pdo->prepare("SELECT nome, email, criado_em FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $perfil = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            $perfil = false;
            $erro_db = "Erro ao buscar dados do perfil.";
        }

        require_once __DIR__ . '/../Views/funcionario/perfil.php';
    }
}

