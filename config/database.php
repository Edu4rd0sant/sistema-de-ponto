<?php
/* =======================================================
   database.php
   Conexão segura com o Banco de Dados (PDO)
   ======================================================= */

// Configura o fuso horário do PHP para o horário do Brasil
date_default_timezone_set('America/Sao_Paulo');

$host = 'localhost';
$dbname = 'sistemaponto';
$username = getenv('DB_USER') ?: 'root'; 
$password = getenv('DB_PASS') ?: '';     

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
        PDO::ATTR_EMULATE_PREPARES   => false,                  
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Força o banco de dados MySQL a usar o nosso fuso horário (UTC-3)
    $pdo->exec("SET time_zone = '-03:00';");
    
} catch (PDOException $e) {
    die("Erro crítico de Conexão: Não foi possível conectar ao banco de dados '{$dbname}'. " . $e->getMessage());
}
?>
