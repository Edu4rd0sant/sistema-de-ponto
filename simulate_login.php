<?php
require_once 'config/database.php';

$email = 'admin@primus.com';
$senha = '123456';

echo "Tentando login com Email: $email | Senha: $senha\n";

try {
    $stmt = $pdo->prepare("SELECT id, nome, email, senha, nivel_acesso FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo "Usuário não encontrado no banco de dados.\n";
    } else {
        echo "Usuário encontrado: " . $usuario['nome'] . "\n";
        echo "Hash salva: " . $usuario['senha'] . "\n";
        
        $senha_bate = password_verify($senha, $usuario['senha']);
        $texto_puro_bate = ($senha === $usuario['senha']);
        
        echo "password_verify: " . ($senha_bate ? 'SIM' : 'NÃO') . "\n";
        echo "texto puro: " . ($texto_puro_bate ? 'SIM' : 'NÃO') . "\n";

        if ($senha_bate || $texto_puro_bate) {
            echo "LOGIN VÁLIDO!\n";
        } else {
            echo "LOGIN INVÁLIDO! A senha não confere.\n";
        }
    }
} catch (PDOException $e) {
    echo "Erro no banco: " . $e->getMessage() . "\n";
}
?>
