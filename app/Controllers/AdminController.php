<?php
namespace App\Controllers;

use Config\Database;
use PDO;
use PDOException;

class AdminController {

    public function dashboard() {
        
        
        // Proteção de Rota Admin
        // Proteção de Rota Admin
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
            header("Location: /login");
            exit;
        }

        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        $pdo = Database::getConnection();

        // 1. Buscar funcionários para a tabela
        try {
            $stmt = $pdo->prepare("
                SELECT u.id, u.nome, u.email, u.cargo, u.permissoes, u.escala_id, 
                       u.status_trabalho, u.criado_em, 'Atrasado' as status_temp, 
                       e.nome as escala_nome 
                FROM usuarios u 
                LEFT JOIN escalas e ON u.escala_id = e.id 
                WHERE u.id != ? 
                ORDER BY u.nome ASC
            ");
            $stmt->execute([$_SESSION['usuario_id']]);
            $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $funcionarios = [];
            $erro_db = "Erro ao carregar funcionários: " . $e->getMessage();
        }

        // 2. Buscar escalas disponíveis para os modais
        try {
            $stmtEscalas = $pdo->query("SELECT id, nome FROM escalas ORDER BY nome ASC");
            $escalas_disponiveis = $stmtEscalas->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $escalas_disponiveis = [];
        }

        // Variáveis que a view precisa: $funcionarios, $escalas_disponiveis e $erro_db (se existir)
        require_once __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function gestao_ponto() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        $pdo = Database::getConnection();

        // 1. Busca Histórico Global
        try {
            $stmt = $pdo->query("
                SELECT p.*, u.nome 
                FROM registros_ponto p 
                JOIN usuarios u ON p.usuario_id = u.id 
                ORDER BY p.data_hora DESC
            ");
            $historico_global = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $historico_global = [];
            $erro_db = "Erro ao carregar o histórico (" . $e->getMessage() . ")";
        }

        // 2. O Helper de tipagem agora ficará nativo na View para evitar problemas de escopo/namespace

        require_once __DIR__ . '/../Views/admin/gestao_ponto.php';
    }

    public function escalas() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->query("
                SELECT e.*, COUNT(u.id) as total_funcionarios 
                FROM escalas e 
                LEFT JOIN usuarios u ON e.id = u.escala_id 
                GROUP BY e.id 
                ORDER BY e.nome ASC
            ");
            $escalas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $escalas = [];
            $erro_db = "Erro ao carregar escalas: " . $e->getMessage();
        }

        require_once __DIR__ . '/../Views/admin/escalas.php';
    }

    public function relatorios() {
        if (!isset($_SESSION['logado'])) { header("Location: /login"); exit; }
        if ($_SESSION['nivel_acesso'] !== 'admin' && !in_array('analisar_relatorios', $_SESSION['permissoes']??[])) {
            header("Location: /admin"); exit;
        }
        $pdo = Database::getConnection();

        $mesAtual = date('n');
        $anoAtual = date('Y');
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        try {
            $stmt = $pdo->query("SELECT id, nome FROM usuarios ORDER BY nome ASC");
            $usuarios_cadastrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $usuarios_cadastrados = [];
        }

        require_once __DIR__ . '/../Views/admin/relatorios.php';
    }

    public function perfil() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->prepare("SELECT nome, email, criado_em FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['usuario_id']]);
            $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $perfil = false;
            $erro_db = "Erro ao buscar dados do perfil.";
        }

        require_once __DIR__ . '/../Views/admin/perfil_admin.php';
    }

    public function criarFuncionario() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) { die('CSRF detected'); }
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha_pura = trim($_POST['senha'] ?? '');
            if (empty($nome) || empty($email) || empty($senha_pura)) { header("Location: /admin?erro=preencha_todos_os_campos"); exit; }
            if (strlen($senha_pura) < 6) { header("Location: /admin?erro=senha_curta"); exit; }
            $senha_hash = password_hash($senha_pura, PASSWORD_BCRYPT);
            $cargo = trim($_POST['cargo'] ?? 'Empregado');
            $status_trabalho = in_array($_POST['status_trabalho'] ?? 'Trabalhando', ['Trabalhando', 'Férias', 'Afastado']) ? $_POST['status_trabalho'] : 'Trabalhando';
            $escala_id = !empty($_POST['escala_id']) ? filter_var($_POST['escala_id'], FILTER_VALIDATE_INT) : null;
            $permissoesRaw = isset($_POST['permissoes']) && is_array($_POST['permissoes']) ? $_POST['permissoes'] : [];
            $todasPermissoes = ['analisar_relatorios', 'gerenciar_senhas', 'aprovar_solicitacoes', 'ajustar_pontos'];
            $temTodas = true; foreach($todasPermissoes as $p) { if (!in_array($p, $permissoesRaw)) { $temTodas = false; break; } }
            $nivel_acesso = $temTodas ? 'admin' : 'funcionario';
            $permissoesJson = json_encode($permissoesRaw);
            $pdo = Database::getConnection();
            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, cargo, permissoes, escala_id, status_trabalho) VALUES (:nome, :email, :senha, :nivel, :cargo, :permissoes, :escala_id, :status_trabalho)");
                $stmt->bindParam(':nome', $nome); $stmt->bindParam(':email', $email); $stmt->bindParam(':senha', $senha_hash);
                $stmt->bindParam(':nivel', $nivel_acesso); $stmt->bindParam(':cargo', $cargo); $stmt->bindParam(':permissoes', $permissoesJson);
                $stmt->bindParam(':escala_id', $escala_id, PDO::PARAM_INT); $stmt->bindParam(':status_trabalho', $status_trabalho);
                $stmt->execute();
                header("Location: /admin?sucesso=funcionario_criado"); exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) { header("Location: /admin?erro=email_ja_cadastrado"); exit; }
                die("Erro crítico ao criar usuário: " . $e->getMessage());
            }
        }
        header("Location: /admin"); exit;
    }

    public function editarPermissoes() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id_usuario_alvo = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
            if (!$id_usuario_alvo) { header("Location: /admin?erro=usuario_invalido"); exit; }
            $cargo = trim($_POST['cargo'] ?? 'Empregado');
            $status_trabalho = in_array($_POST['status_trabalho'] ?? 'Trabalhando', ['Trabalhando', 'Férias', 'Afastado']) ? $_POST['status_trabalho'] : 'Trabalhando';
            $escala_id = !empty($_POST['escala_id']) ? filter_var($_POST['escala_id'], FILTER_VALIDATE_INT) : null;
            $permissoesRaw = isset($_POST['permissoes']) && is_array($_POST['permissoes']) ? $_POST['permissoes'] : [];
            $todasPermissoes = ['analisar_relatorios', 'gerenciar_senhas', 'aprovar_solicitacoes', 'ajustar_pontos'];
            $temTodas = true; foreach($todasPermissoes as $p) { if (!in_array($p, $permissoesRaw)) { $temTodas = false; break; } }
            $nivel_acesso = $temTodas ? 'admin' : 'funcionario';
            if ($id_usuario_alvo == 1) { $nivel_acesso = 'admin'; $permissoesRaw = $todasPermissoes; }
            $permissoesJson = json_encode($permissoesRaw);
            $pdo = Database::getConnection();
            try {
                $stmt = $pdo->prepare("UPDATE usuarios SET cargo = :cargo, permissoes = :permissoes, nivel_acesso = :nivel_acesso, escala_id = :escala_id, status_trabalho = :status_trabalho WHERE id = :id");
                $stmt->bindParam(':cargo', $cargo); $stmt->bindParam(':permissoes', $permissoesJson);
                $stmt->bindParam(':nivel_acesso', $nivel_acesso); $stmt->bindParam(':escala_id', $escala_id, PDO::PARAM_INT);
                $stmt->bindParam(':status_trabalho', $status_trabalho); $stmt->bindParam(':id', $id_usuario_alvo);
                $stmt->execute();
                if ($id_usuario_alvo == $_SESSION['usuario_id']) {
                    $_SESSION['cargo'] = $cargo; $_SESSION['permissoes'] = $permissoesRaw; $_SESSION['nivel_acesso'] = $nivel_acesso;
                    if ($nivel_acesso !== 'admin') { header("Location: /funcionario"); exit; }
                }
                header("Location: /admin?sucesso=permissoes_atualizadas"); exit;
            } catch (PDOException $e) { die("Erro crítico ao salvar permissões: " . $e->getMessage()); }
        }
        header("Location: /admin"); exit;
    }

    public function excluirFuncionario() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id_usuario = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
            if (!$id_usuario) { header("Location: /admin?erro=usuario_invalido"); exit; }
            if ($id_usuario == $_SESSION['usuario_id']) { header("Location: /admin?erro=autoexclusao"); exit; }
            $pdo = Database::getConnection();
            try {
                $pdo->beginTransaction();
                $stmt_solic = $pdo->prepare("DELETE FROM solicitacoes WHERE usuario_id = :id"); $stmt_solic->bindParam(':id', $id_usuario); $stmt_solic->execute();
                $stmt_ponto = $pdo->prepare("DELETE FROM registros_ponto WHERE usuario_id = :id"); $stmt_ponto->bindParam(':id', $id_usuario); $stmt_ponto->execute();
                $stmt_user = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND nivel_acesso != 'admin'");
                $stmt_user->bindParam(':id', $id_usuario); $stmt_user->execute();
                if ($stmt_user->rowCount() == 0) { $pdo->rollBack(); header("Location: /admin?erro=exclusao_nao_permitida"); exit; }
                $pdo->commit(); header("Location: /admin?sucesso=funcionario_excluido"); exit;
            } catch (PDOException $e) {
                $pdo->rollBack(); die("Erro crítico ao excluir funcionário: " . $e->getMessage());
            }
        }
        header("Location: /admin"); exit;
    }

    public function resetarSenha() {
        $pode_gerenciar = ($_SESSION['nivel_acesso'] === 'admin') || (is_array($_SESSION['permissoes']??null) && in_array('gerenciar_senhas', $_SESSION['permissoes']));
        if (!isset($_SESSION['logado']) || !$pode_gerenciar) { die("Acesso Negado."); }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $usuario_alvo = (int)($_POST['usuario_id'] ?? 0);
            $nova_senha_admin = trim($_POST['nova_senha'] ?? '');
            if (empty($usuario_alvo) || empty($nova_senha_admin)) { header("Location: /admin?erro=faltam_dados_senha"); exit; }
            if (strlen($nova_senha_admin) < 6) { header("Location: /admin?erro=senha_curta"); exit; }
            $pdo = Database::getConnection();
            try {
                $nova_hash = password_hash($nova_senha_admin, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :uid");
                $stmt->bindParam(':senha', $nova_hash); $stmt->bindParam(':uid', $usuario_alvo); $stmt->execute();
                header("Location: /admin?sucesso=senha_forçada"); exit;
            } catch (PDOException $e) { die("Erro ao forçar senha: " . $e->getMessage()); }
        }
        header("Location: /admin"); exit;
    }

    public function salvarEscala() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = filter_input(INPUT_POST, 'escala_id', FILTER_VALIDATE_INT);
            $nome = trim($_POST['nome'] ?? '');
            $dias_trabalho = trim($_POST['dias_trabalho'] ?? '');
            $hora_entrada = trim($_POST['hora_entrada'] ?? '');
            $hora_saida = trim($_POST['hora_saida'] ?? '');
            $hora_almoco_inicio = !empty($_POST['hora_almoco_inicio']) ? trim($_POST['hora_almoco_inicio']) : null;
            $hora_almoco_fim = !empty($_POST['hora_almoco_fim']) ? trim($_POST['hora_almoco_fim']) : null;
            if (empty($nome) || empty($dias_trabalho) || empty($hora_entrada) || empty($hora_saida)) { header("Location: /admin/escalas?erro=Campos_obrigatorios_ausentes"); exit; }
            $pdo = Database::getConnection();
            try {
                if ($id) {
                    $stmt = $pdo->prepare("UPDATE escalas SET nome = :nome, dias_trabalho = :dias_trabalho, hora_entrada = :hora_entrada, hora_saida = :hora_saida, hora_almoco_inicio = :hora_almoco_inicio, hora_almoco_fim = :hora_almoco_fim WHERE id = :id");
                    $stmt->bindParam(':id', $id);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO escalas (nome, dias_trabalho, hora_entrada, hora_saida, hora_almoco_inicio, hora_almoco_fim) VALUES (:nome, :dias_trabalho, :hora_entrada, :hora_saida, :hora_almoco_inicio, :hora_almoco_fim)");
                }
                $stmt->bindParam(':nome', $nome); $stmt->bindParam(':dias_trabalho', $dias_trabalho); $stmt->bindParam(':hora_entrada', $hora_entrada);
                $stmt->bindParam(':hora_saida', $hora_saida); $stmt->bindParam(':hora_almoco_inicio', $hora_almoco_inicio); $stmt->bindParam(':hora_almoco_fim', $hora_almoco_fim);
                $stmt->execute();
                header("Location: /admin/escalas?sucesso=1"); exit;
            } catch (PDOException $e) { die("Erro ao salvar escala: " . $e->getMessage()); }
        }
        header("Location: /admin/escalas"); exit;
    }

    public function excluirEscala() {
        if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') { header("Location: /login"); exit; }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = filter_input(INPUT_POST, 'escala_id', FILTER_VALIDATE_INT);
            if (!$id) { header("Location: /admin/escalas?erro=ID Inválido"); exit; }
            $pdo = Database::getConnection();
            try {
                $stmt = $pdo->prepare("DELETE FROM escalas WHERE id = :id"); $stmt->bindParam(':id', $id); $stmt->execute();
                header("Location: /admin/escalas?sucesso=1"); exit;
            } catch (PDOException $e) { die("Erro ao excluir escala: " . $e->getMessage()); }
        }
        header("Location: /admin/escalas"); exit;
    }

    public function salvarPonto() {
        $pode_ajustar = ($_SESSION['nivel_acesso'] === 'admin') || (is_array($_SESSION['permissoes']??null) && in_array('ajustar_pontos', $_SESSION['permissoes']));
        if (!isset($_SESSION['logado']) || !$pode_ajustar) { header("Location: /login"); exit; }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
            $data_registro = $_POST['data_registro'] ?? null;
            $hora_registro = $_POST['hora_registro'] ?? null;
            $tipo_registro = $_POST['tipo_registro'] ?? null;
            if (!$usuario_id || !$data_registro || !$hora_registro || !$tipo_registro) { header("Location: /admin?erro=dados_invalidos"); exit; }
            $data_hora = $data_registro . ' ' . $hora_registro . ':00';
            $pdo = Database::getConnection();
            try {
                $stmt = $pdo->prepare("INSERT INTO registros_ponto (usuario_id, tipo, data_hora) VALUES (:usuario_id, :tipo, :data_hora)");
                $stmt->bindParam(':usuario_id', $usuario_id); $stmt->bindParam(':tipo', $tipo_registro); $stmt->bindParam(':data_hora', $data_hora);
                $stmt->execute();
                header("Location: /admin?sucesso=ponto_salvo"); exit;
            } catch (PDOException $e) { die("Erro ao salvar ponto: " . $e->getMessage()); }
        }
        header("Location: /admin"); exit;
    }
}

