<?php
/*
--------------------------------------------------
API - Obtener preguntas por nivel
--------------------------------------------------

Este archivo devuelve las preguntas de un nivel
concreto en formato JSON, junto con sus respuestas.

Se utiliza para cargar dinámicamente el contenido
del juego desde la base de datos.
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

$nivel = isset($_GET["nivel"]) ? (int)$_GET["nivel"] : 1;

$stmtPreguntas = $conexion->prepare("
    SELECT id, pregunta, imagen, nivel_id
    FROM preguntas
    WHERE nivel_id = ?
    ORDER BY id ASC
");

if (!$stmtPreguntas) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error preparando preguntas."
    ]);
    exit;
}

$stmtPreguntas->bind_param("i", $nivel);
$stmtPreguntas->execute();
$resultadoPreguntas = $stmtPreguntas->get_result();

$preguntas = [];

while ($fila = $resultadoPreguntas->fetch_assoc()) {
    $idPregunta = (int)$fila["id"];

    $stmtRespuestas = $conexion->prepare("
        SELECT texto, imagen, correcta
        FROM respuestas
        WHERE pregunta_id = ?
        ORDER BY id ASC
    ");

    if (!$stmtRespuestas) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error preparando respuestas."
        ]);
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