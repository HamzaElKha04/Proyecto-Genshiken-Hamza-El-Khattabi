<?php
/*
--------------------------------------------------
API - Obtener preguntas por nivel
--------------------------------------------------

Este archivo actúa como endpoint de la API del
proyecto.

Su función es consultar la base de datos y devolver
las preguntas de un nivel concreto en formato JSON,
junto con sus respuestas asociadas.

Esta API está pensada para que la aplicación móvil
o la web puedan cargar las preguntas de forma
dinámica desde el servidor.
*/
header('Content-Type: application/json; charset=utf-8');

$host = "127.0.0.1";
$usuario = "root";
$contrasena = "";
$basedatos = "u842177649_genshiapp";

$conexion = @new mysqli($host, $usuario, $contrasena);

if ($conexion->connect_error) {
    die("Error conectando a MySQL: " . $conexion->connect_error);
}

if (!$conexion->select_db($basedatos)) {
    die("No se puede seleccionar la base de datos: " . $basedatos);
}

$nivel = isset($_GET['nivel']) ? (int)$_GET['nivel'] : 1;

$sql = "SELECT * FROM preguntas WHERE nivel_id = $nivel";
$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error en consulta preguntas: " . $conexion->error);
}

$preguntas = [];

while ($fila = $resultado->fetch_assoc()) {
    $idPregunta = (int)$fila['id'];

    $sqlRespuestas = "SELECT * FROM respuestas WHERE pregunta_id = $idPregunta";
    $res = $conexion->query($sqlRespuestas);

    if (!$res) {
        die("Error en consulta respuestas: " . $conexion->error);
    }

    $respuestas = [];

    while ($r = $res->fetch_assoc()) {
        $respuestas[] = [
            "texto" => $r['texto'],
            "correcta" => (int)$r['correcta']
        ];
    }

    $preguntas[] = [
        "id" => (int)$fila['id'],
        "pregunta" => $fila['pregunta'],
        "imagen" => $fila['imagen'],
        "respuestas" => $respuestas
    ];
}

echo json_encode($preguntas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conexion->close();
?>