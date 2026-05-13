<?php
/*
--------------------------------------------------
API - Obtener preguntas por nivel
--------------------------------------------------

Devuelve las preguntas de un nivel concreto en JSON,
junto con sus respuestas.

IMPORTANTE:
Este archivo usa admin/config.php para conectarse a
la base de datos, por lo que funciona tanto en local
como en hosting cambiando solo config.php.

Además, detecta si la tabla preguntas usa:
- pregunta / imagen
o
- pregunta_texto / pregunta_imagen

Así evita errores entre la base local y la del hosting.
*/

header('Content-Type: application/json; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();

/*
--------------------------------------------------
Función auxiliar para comprobar columnas
--------------------------------------------------
*/
function existeColumna(mysqli $conexion, string $tabla, string $columna): bool
{
    $stmt = $conexion->prepare("SHOW COLUMNS FROM $tabla LIKE ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $columna);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $existe = $resultado && $resultado->num_rows > 0;

    $stmt->close();

    return $existe;
}

/*
--------------------------------------------------
Detectar nombres reales de columnas
--------------------------------------------------

En algunas versiones la tabla usa:
pregunta / imagen

En la base online actual usa:
pregunta_texto / pregunta_imagen
*/
$columnaPregunta = existeColumna($conexion, "preguntas", "pregunta")
    ? "pregunta"
    : "pregunta_texto";

$columnaImagen = existeColumna($conexion, "preguntas", "imagen")
    ? "imagen"
    : "pregunta_imagen";

/* Nivel solicitado desde Android o web */
$nivel = isset($_GET["nivel"]) ? (int)$_GET["nivel"] : 1;

/*
--------------------------------------------------
Obtener preguntas
--------------------------------------------------
*/
$sqlPreguntas = "
    SELECT 
        id,
        $columnaPregunta AS pregunta,
        $columnaImagen AS imagen,
        nivel_id
    FROM preguntas
    WHERE nivel_id = ?
    ORDER BY id ASC
";

$stmtPreguntas = $conexion->prepare($sqlPreguntas);

if (!$stmtPreguntas) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando preguntas: " . $conexion->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmtPreguntas->bind_param("i", $nivel);
$stmtPreguntas->execute();
$resultadoPreguntas = $stmtPreguntas->get_result();

$preguntas = [];

while ($fila = $resultadoPreguntas->fetch_assoc()) {
    $idPregunta = (int)$fila["id"];

    /*
    --------------------------------------------------
    Obtener respuestas de cada pregunta
    --------------------------------------------------
    */
    $stmtRespuestas = $conexion->prepare("
        SELECT texto, imagen, correcta
        FROM respuestas
        WHERE pregunta_id = ?
        ORDER BY id ASC
    ");

    if (!$stmtRespuestas) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error preparando respuestas: " . $conexion->error
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmtRespuestas->bind_param("i", $idPregunta);
    $stmtRespuestas->execute();
    $resultadoRespuestas = $stmtRespuestas->get_result();

    $respuestas = [];

    while ($r = $resultadoRespuestas->fetch_assoc()) {
        $respuestas[] = [
            "texto" => $r["texto"],
            "imagen" => $r["imagen"],
            "correcta" => (int)$r["correcta"]
        ];
    }

    $stmtRespuestas->close();

    $preguntas[] = [
        "id" => (int)$fila["id"],
        "nivel_id" => (int)$fila["nivel_id"],
        "pregunta" => $fila["pregunta"],
        "imagen" => $fila["imagen"],
        "respuestas" => $respuestas
    ];
}

$stmtPreguntas->close();
$conexion->close();

echo json_encode($preguntas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>