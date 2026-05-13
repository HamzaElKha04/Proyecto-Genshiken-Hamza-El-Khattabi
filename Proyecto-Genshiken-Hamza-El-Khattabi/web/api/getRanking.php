<?php
/*
--------------------------------------------------
API - Obtener ranking global
--------------------------------------------------

Devuelve el TOP 10 de jugadores ordenado por:
- puntos descendente
- tiempo ascendente
- fecha ascendente

Este archivo usa admin/config.php para funcionar
correctamente tanto en local como en hosting.
*/

header('Content-Type: application/json; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();

$sql = "
    SELECT 
        p.id,
        p.nombre,
        p.puntos,
        p.tiempo,
        p.fecha
    FROM puntuaciones p
    ORDER BY p.puntos DESC, p.tiempo ASC, p.fecha ASC
    LIMIT 10
";

$resultado = $conexion->query($sql);

if (!$resultado) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error en la consulta del ranking: " . $conexion->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$ranking = [];
$posicion = 1;

while ($fila = $resultado->fetch_assoc()) {
    $ranking[] = [
        "posicion" => $posicion,
        "usuario" => !empty($fila["nombre"]) ? $fila["nombre"] : "Anónimo",
        "puntos" => (int)$fila["puntos"],
        "tiempo" => (int)$fila["tiempo"],
        "fecha" => $fila["fecha"]
    ];

    $posicion++;
}

echo json_encode($ranking, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conexion->close();
?>