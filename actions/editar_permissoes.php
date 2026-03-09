<?php
require_once __DIR__ . '/../config/session.php';
require_once '../config/database.php';

// Apenas admins podem acessar
if (!isset($_SESSION['logado']) || $_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario_alvo = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    
    if (!$id_usuario_alvo) {
        header("Location: ../admin.php?erro=usuario_invalido");
        exit;
    }

    $cargo = trim($_POST['cargo'] ?? 'Empregado');
    $status_trabalho = in_array($_POST['status_trabalho'] ?? 'Trabalhando', ['Trabalhando', 'Férias', 'Afastado']) ? $_POST['status_trabalho'] : 'Trabalhando';
    $escala_id = !empty($_POST['escala_id']) ? filter_var($_POST['escala_id'], FILTER_VALIDATE_INT) : null;
    $permissoesRaw = isset($_POST['permissoes']) && is_array($_POST['permissoes']) ? $_POST['permissoes'] : [];
    
    // Se selecionou todas as quatro permissões especiais, promovemos a admin
    $todasPermissoes = ['analisar_relatorios', 'gerenciar_senhas', 'aprovar_solicitacoes', 'ajustar_pontos'];
    $temTodas = true;
    foreach($todasPermissoes as $p) {
        if (!in_array($p, $permissoesRaw)) {
            $temTodas = false;
            break;
        }
    }
    
    $nivel_acesso = $temTodas ? 'admin' : 'funcionario';
    
    // Proteção: O usuário 1 é o Super Admin padrão e nunca deve perder esse cargo
    if ($id_usuario_alvo == 1) {
        $nivel_acesso = 'admin';
        // Opcional: garantir que tenha todas as permissões se for forçado a manter o admin
        $permissoesRaw = $todasPermissoes;
    }
    
    $permissoesJson = json_encode($permissoesRaw);

    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET cargo = :cargo, permissoes = :permissoes, nivel_acesso = :nivel_acesso, escala_id = :escala_id, status_trabalho = :status_trabalho WHERE id = :id");
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':permissoes', $permissoesJson);
        $stmt->bindParam(':nivel_acesso', $nivel_acesso);
        $stmt->bindParam(':escala_id', $escala_id, PDO::PARAM_INT);
        $stmt->bindParam(':status_trabalho', $status_trabalho);
        $stmt->bindParam(':id', $id_usuario_alvo);
        
        $stmt->execute();
        
        // Se caso o admin tirar suas próprias permissões (não recomendado, mas possível), 
        // a sessão atual dele precisa perder acesso, logo vamos deslogar ou checar:
        if ($id_usuario_alvo == $_SESSION['usuario_id']) {
            $_SESSION['cargo'] = $cargo;
            $_SESSION['permissoes'] = $permissoesRaw;
            $_SESSION['nivel_acesso'] = $nivel_acesso;
            
            if ($nivel_acesso !== 'admin') {
                header("Location: ../index.php");
                exit;
            }
        }

        header("Location: ../admin.php?sucesso=permissoes_atualizadas");
        exit;

    } catch (PDOException $e) {
        die("Erro crítico ao salvar permissões: " . $e->getMessage());
    }
} else {
    header("Location: ../admin.php");
    exit;
}
?>
