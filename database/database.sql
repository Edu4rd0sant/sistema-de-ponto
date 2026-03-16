CREATE DATABASE IF NOT EXISTS `sistemaponto` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sistemaponto`;

CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `nivel_acesso` ENUM('admin', 'funcionario') NOT NULL DEFAULT 'funcionario',
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `registros_ponto` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `tipo` ENUM('entrada', 'saida_almoco', 'retorno_almoco', 'saida') NOT NULL,
    `data_hora` DATETIME NOT NULL,
    `latitude` DECIMAL(10, 8) DEFAULT NULL,
    `longitude` DECIMAL(11, 8) DEFAULT NULL,
    `foto` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserindo um administrador padrão.
-- A senha inserida abaixo é '123456', convertida pelo password_hash.
INSERT INTO `usuarios` (`nome`, `email`, `senha`, `nivel_acesso`) 
VALUES ('Administrador', 'admin@primus.com', '$2y$10$vI8aWBnW3fID.ZQ4/zo1G.q1lRps.9cGLcZEiGDMVr5yUP1KUOYTa', 'admin');
