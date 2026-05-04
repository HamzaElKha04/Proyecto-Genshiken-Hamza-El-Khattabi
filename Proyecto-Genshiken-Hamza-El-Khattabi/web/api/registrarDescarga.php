<?php
/*
--------------------------------------------------
API - Registrar descarga de la app
--------------------------------------------------

Este archivo permite registrar una descarga,
instalación o apertura de la aplicación para
que luego pueda visualizarse desde el panel admin.
*/

header('Content-Type: application/json; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();
$conexion->set_charset("utf8mb4");

/* Lee los datos recibidos:
   primero intenta JSON
   y si no, usa POST normal */
$datos = json_decode(file_get_contents("php://input"), true);

if (!is_array($datos)) {
    $datos = $_POST;
}

/* Datos que enviará la app Android */
$usuarioId = isset($datos["usuario_id"]) && $datos["usuario_id"] !== "" ? (int)$datos["usuario_id"] : null;
$nombreUsuario = trim($datos["nombre_usuario"] ?? "Anónimo");
$dispositivo = trim($datos["dispositivo"] ?? "");
$versionApp = trim($datos["version_app"] ?? "");

/* Si no llega nombre, se guarda como Anónimo */
if ($nombreUsuario === "") {
    $nombreUsuario = "Anónimo";
}

/* El dispositivo sí es obligatorio para registrar la descarga */
if ($dispositivo === "") {
    echo json_encode([
        "ok" => false,
        "mensaje" => "El dispositivo es obligatorio."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Inserta el registro en la tabla descargas */
$stmt = $conexion->prepare("
    INSERT INTO descargas (usuario_id, nombre_usuario, dispositivo, version_app)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al preparar la inserción."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("isss", $usuarioId, $nombreUsuario, $dispositivo, $versionApp);

/* Devuelve respuesta JSON para que la app sepa si salió bien o mal */
if ($stmt->execute()) {
    echo json_encode([
        "ok" => true,
        "mensaje" => "Descarga registrada correctamente."
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo registrar la descarga."
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conexion->close();
?>