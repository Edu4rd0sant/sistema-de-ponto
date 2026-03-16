<?php

namespace App\Models;

use PDO;
use Exception;

class PontoModel {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Busca todos os registros de ponto de um usuário em uma data específica
     */
    public function getRegistrosHoje(int $usuario_id, string $data_hora): array {
        $stmt = $this->pdo->prepare("SELECT tipo FROM registros_ponto WHERE usuario_id = ? AND DATE(data_hora) = DATE(?) ORDER BY data_hora ASC");
        $stmt->execute([$usuario_id, $data_hora]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Determina o próximo tipo de registro com base nos registros já existentes hoje
     */
    public function determinarProximoTipo(int $usuario_id, string $data_hora): string {
        $registrosHoje = $this->getRegistrosHoje($usuario_id, $data_hora);
        $qtdRegistros = count($registrosHoje);

        if ($qtdRegistros == 0) {
            return 'entrada';
        } elseif ($qtdRegistros == 1) {
            return 'saida_almoco';
        } elseif ($qtdRegistros == 2) {
            return 'retorno_almoco';
        } elseif ($qtdRegistros == 3) {
            return 'saida';
        }

        throw new Exception('Você já registrou todos os horários de hoje.');
    }

    /**
     * Insere o registro de ponto no banco de dados
     */
    public function registrarPonto(int $usuario_id, string $tipo, string $data_hora, ?float $latitude, ?float $longitude, ?string $fotoPath): bool {
        $stmt = $this->pdo->prepare("INSERT INTO registros_ponto (usuario_id, tipo, data_hora, latitude, longitude, foto) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$usuario_id, $tipo, $data_hora, $latitude, $longitude, $fotoPath]);
    }
}
