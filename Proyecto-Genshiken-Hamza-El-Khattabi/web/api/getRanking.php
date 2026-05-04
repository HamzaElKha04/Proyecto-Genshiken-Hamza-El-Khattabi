<?php
/*
--------------------------------------------------
API - Obtener ranking global
--------------------------------------------------

Este archivo devuelve el TOP 10 de jugadores
ordenado por:
- puntos (descendente)
- tiempo (ascendente)
- fecha (ascendente)

Se utiliza para mostrar el ranking en el frontend.
*/

header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$usuario = "root";
$contrasena = "";
$basedatos = "u842177649_genshiapp";

$conexion = new mysqli($host, $usuario, $contrasena, $basedatos);

if ($conexion->connect_error) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error de conexión: " . $conexion->connect_error
    ]);
    exit;
}

$conexion->set_charset("utf8mb4");

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
    ]);
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