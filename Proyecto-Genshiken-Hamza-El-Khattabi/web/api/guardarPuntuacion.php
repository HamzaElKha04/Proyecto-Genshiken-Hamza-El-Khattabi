<?php
header('Content-Type: application/json; charset=utf-8');

/*
--------------------------------------------------
API - Guardar puntuación del juego
--------------------------------------------------

Este archivo recibe el nombre del jugador, la
puntuación y el tiempo desde el frontend del juego
y los guarda en la base de datos.

Además:
- calcula la posición real del jugador en el ranking
- comprueba si ha quedado dentro del Top 3
- devuelve esos datos al frontend
*/

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

$datos = json_decode(file_get_contents("php://input"), true);

if (
    !$datos ||
    !isset($datos["nombre"]) ||
    !isset($datos["puntos"]) ||
    !isset($datos["tiempo"])
) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Datos incompletos."
    ]);
    exit;
}

$nombre = trim($datos["nombre"]);
$puntos = (int)$datos["puntos"];
$tiempo = (int)$datos["tiempo"];
$usuario_id = 1;

if ($nombre === "") {
    $nombre = "Anónimo";
}

if (mb_strlen($nombre, "UTF-8") > 100) {
    $nombre = mb_substr($nombre, 0, 100, "UTF-8");
}

$stmt = $conexion->prepare("
    INSERT INTO puntuaciones (usuario_id, nombre, puntos, tiempo, fecha) 
    VALUES (?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando la consulta."
    ]);
    exit;
}

$stmt->bind_param("isii", $usuario_id, $nombre, $puntos, $tiempo);

if (!$stmt->execute()) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar la puntuación."
    ]);
    exit;
}

$idInsertado = $conexion->insert_id;
$stmt->close();

$sqlRanking = "SELECT id
               FROM puntuaciones
               ORDER BY puntos DESC, tiempo ASC, fecha ASC";

$resultadoRanking = $conexion->query($sqlRanking);

if (!$resultadoRanking) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Puntuación guardada, pero no se pudo calcular la posición."
    ]);
    exit;
}

$posicion = 0;
$contador = 1;

while ($fila = $resultadoRanking->fetch_assoc()) {
    if ((int)$fila["id"] === (int)$idInsertado) {
        $posicion = $contador;
        break;
    }
    $contador++;
}

$top3 = ($posicion > 0 && $posicion <= 3);

echo json_encode([
    "ok" => true,
    "mensaje" => "Puntuación y tiempo guardados correctamente.",
    "posicion" => $posicion,
    "top3" => $top3
], JSON_UNESCAPED_UNICODE);

$conexion->close();
?>