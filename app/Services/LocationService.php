<?php

namespace App\Services;

class LocationService {
    
    /**
     * Calcula a distância em metros entre duas coordenadas geográficas
     * utilizando a fórmula de Haversine.
     * 
     * @param float $lat1 Latitude do Ponto 1
     * @param float $lon1 Longitude do Ponto 1
     * @param float $lat2 Latitude do Ponto 2 (Empresa)
     * @param float $lon2 Longitude do Ponto 2 (Empresa)
     * @return float Distância em metros
     */
    public static function calcularDistanciaMetros(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthRadius = 6371000; // Raio da Terra em metros

        // Converte coordenadas de graus para radianos
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $dLat = $lat2Rad - $lat1Rad;
        $dLon = $lon2Rad - $lon1Rad;

        // Fórmula de Haversine
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($dLon / 2) * sin($dLon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }
}
