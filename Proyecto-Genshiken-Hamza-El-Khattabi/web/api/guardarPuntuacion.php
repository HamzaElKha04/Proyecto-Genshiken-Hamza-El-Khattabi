<?php
/*
--------------------------------------------------
API - Guardar puntuación del juego
--------------------------------------------------

Este archivo recibe desde Android o desde el juego web:
- nombre del jugador
- puntuación
- tiempo realizado

Después guarda la partida en la tabla puntuaciones.

IMPORTANTE:
Este archivo ya no usa conexión local con root.
Usa ../admin/config.php para funcionar tanto en local
como en hosting, dependiendo del config.php correspondiente.
*/

header('Content-Type: application/json; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();

/*
--------------------------------------------------
Leer datos recibidos
--------------------------------------------------

Primero intenta leer JSON.
Si no llega JSON, usa POST normal.

Esto permite que funcione con:
- Android / Retrofit
- JavaScript fetch
- formularios o pruebas POST
*/
$datos = json_decode(file_get_contents("php://input"), true);

if (!is_array($datos)) {
    $datos = $_POST;
}

/*
--------------------------------------------------
Validación básica
--------------------------------------------------
*/
if (
    !isset($datos["nombre"]) ||
    !isset($datos["puntos"]) ||
    !isset($datos["tiempo"])
) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Datos incompletos."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$nombre = trim($datos["nombre"]);
$puntos = (int)$datos["puntos"];
$tiempo = (int)$datos["tiempo"];

/*
--------------------------------------------------
Usuario asociado
--------------------------------------------------

Si Android envía usuario_id, se usa.
Si no lo envía, se deja como 1 para mantener
compatibilidad con el funcionamiento anterior.
*/
$usuarioId = isset($datos["usuario_id"]) && $datos["usuario_id"] !== ""
    ? (int)$datos["usuario_id"]
    : 1;

if ($nombre === "") {
    $nombre = "Anónimo";
}

/* Evita nombres excesivamente largos */
if (mb_strlen($nombre, "UTF-8") > 100) {
    $nombre = mb_substr($nombre, 0, 100, "UTF-8");
}

/*
--------------------------------------------------
Guardar puntuación
--------------------------------------------------
*/
$stmt = $conexion->prepare("
    INSERT INTO puntuaciones (usuario_id, nombre, puntos, tiempo, fecha)
    VALUES (?, ?, ?, ?, NOW())
");

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando la consulta: " . $conexion->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("isii", $usuarioId, $nombre, $puntos, $tiempo);

if (!$stmt->execute()) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar la puntuación: " . $stmt->error
    ], JSON_UNESCAPED_UNICODE);

    $stmt->close();
    $conexion->close();
    exit;
}

$idInsertado = $conexion->insert_id;
$stmt->close();

/*
--------------------------------------------------
Calcular posición real en el ranking
--------------------------------------------------

El ranking se ordena igual que en el panel:
- más puntos primero
- menos tiempo primero
- fecha más antigua primero en caso de empate
*/
$sqlRanking = "
    SELECT id
    FROM puntuaciones
    ORDER BY puntos DESC, tiempo ASC, fecha ASC
";

$resultadoRanking = $conexion->query($sqlRanking);

if (!$resultadoRanking) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Puntuación guardada, pero no se pudo calcular la posición."
    ], JSON_UNESCAPED_UNICODE);

    $conexion->close();
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

/*
--------------------------------------------------
Respuesta para Android / juego web
--------------------------------------------------
*/
echo json_encode([
    "ok" => true,
    "mensaje" => "Puntuación y tiempo guardados correctamente.",
    "posicion" => $posicion,
    "top3" => $top3
], JSON_UNESCAPED_UNICODE);

$conexion->close();
?>