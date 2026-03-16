<?php
namespace Config;

use PDO;
use PDOException;

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            date_default_timezone_set('America/Maceio');

            $host = '127.0.0.1';
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

                self::$connection = new PDO($dsn, $username, $password, $options);
                
                // Força o banco de dados MySQL a usar o nosso fuso horário (UTC-3)
                self::$connection->exec("SET time_zone = '-03:00';");
                
            } catch (PDOException $e) {
                die("Erro crítico de Conexão: Não foi possível conectar ao banco de dados '{$dbname}'. " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>
