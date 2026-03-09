<?php
require_once __DIR__ . '/../config/session.php';
require_once '../config/database.php';

// Apenas admins podem acessar
if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        die('CSRF detected');
    }
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_pura = trim($_POST['senha'] ?? '');

    // Validação básica
    if (empty($nome) || empty($email) || empty($senha_pura)) {
        header("Location: ../admin.php?erro=preencha_todos_os_campos");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../admin.php?erro=email_invalido");
        exit;
    }

    if (strlen($senha_pura) < 6) {
        header("Location: ../admin.php?erro=senha_curta");
        exit;
    }

    // Criar a hash BCRYPT
    $senha_hash = password_hash($senha_pura, PASSWORD_BCRYPT);
    $cargo = trim($_POST['cargo'] ?? 'Empregado');
    $status_trabalho = in_array($_POST['status_trabalho'] ?? 'Trabalhando', ['Trabalhando', 'Férias', 'Afastado']) ? $_POST['status_trabalho'] : 'Trabalhando';
    $escala_id = !empty($_POST['escala_id']) ? filter_var($_POST['escala_id'], FILTER_VALIDATE_INT) : null;
    $permissoesRaw = isset($_POST['permissoes']) && is_array($_POST['permissoes']) ? $_POST['permissoes'] : [];
    
    // Se selecionou todas as permissões, promova a admin
    $todasPermissoes = ['analisar_relatorios', 'gerenciar_senhas', 'aprovar_solicitacoes', 'ajustar_pontos'];
    $temTodas = true;
    foreach($todasPermissoes as $p) {
        if (!in_array($p, $permissoesRaw)) {
            $temTodas = false;
            break;
        }
    }
    
    $nivel_acesso = $temTodas ? 'admin' : 'funcionario';
    $permissoesJson = json_encode($permissoesRaw);

    try {
        // Tenta inserir no banco
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, cargo, permissoes, escala_id, status_trabalho) VALUES (:nome, :email, :senha, :nivel, :cargo, :permissoes, :escala_id, :status_trabalho)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_hash);
        $stmt->bindParam(':nivel', $nivel_acesso);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':permissoes', $permissoesJson);
        $stmt->bindParam(':escala_id', $escala_id, PDO::PARAM_INT);
        $stmt->bindParam(':status_trabalho', $status_trabalho);
        
        $stmt->execute();

        // Sucesso
        header("Location: ../admin.php?sucesso=funcionario_criado");
        exit;

    } catch (PDOException $e) {
        // Trata erro de e-mail duplicado (código 1062 no MySQL / 23000 no SQLSTATE)
        if ($e->getCode() == 23000 || (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062)) {
            header("Location: ../admin.php?erro=email_ja_cadastrado");
            exit;
        } else {
            // Outro erro de banco
            die("Erro crítico ao criar usuário: " . $e->getMessage());
        }
    }
} else {
    header("Location: ../admin.php");
    exit;
}
?>
