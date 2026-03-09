<?php
require_once 'config/database.php';

// Gerando a hash correta para 123456
$nova_senha_hash = password_hash('123456', PASSWORD_BCRYPT);

// Atualizando o banco
$stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE email = 'admin@primus.com'");
$stmt->execute(['senha' => $nova_senha_hash]);

echo "Senha atualizada com sucesso!\nNova hash gerada: " . $nova_senha_hash . "\n";
echo "Teste de verificação: " . (password_verify('123456', $nova_senha_hash) ? 'SUCESSO' : 'FALHA');
?>
