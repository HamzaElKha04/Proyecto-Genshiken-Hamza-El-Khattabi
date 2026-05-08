<?php
/*
--------------------------------------------------
API - Registrar instalación / primer uso de la app
--------------------------------------------------

Este archivo registra una instalación detectada
desde la app Android.

IMPORTANTE:
El archivo se sigue llamando registrarDescarga.php
para no romper la conexión con Android, pero su uso
real es registrar el primer uso de la app en un
dispositivo concreto.

No registra cada inicio de sesión.
Solo crea una fila si no existe ya una instalación
para ese usuario + dispositivo + versión.
*/

header('Content-Type: application/json; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();
$conexion->set_charset("utf8mb4");

/* Leer JSON o POST normal */
$datos = json_decode(file_get_contents("php://input"), true);

if (!is_array($datos)) {
    $datos = $_POST;
}

/* Datos enviados desde Android */
$usuarioId = isset($datos["usuario_id"]) && $datos["usuario_id"] !== "" ? (int)$datos["usuario_id"] : null;
$nombreUsuario = trim($datos["nombre_usuario"] ?? "Anónimo");
$dispositivo = trim($datos["dispositivo"] ?? "");
$versionApp = trim($datos["version_app"] ?? "");

/* Valores por defecto */
if ($nombreUsuario === "") {
    $nombreUsuario = "Anónimo";
}

if ($versionApp === "") {
    $versionApp = "No indicada";
}

/* El dispositivo es obligatorio */
if ($dispositivo === "") {
    echo json_encode([
        "ok" => false,
        "mensaje" => "El dispositivo es obligatorio."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
--------------------------------------------------
Comprobar instalación existente
--------------------------------------------------

Si el mismo usuario entra desde el mismo dispositivo
y la misma versión, no se vuelve a insertar.

Esto evita duplicados al iniciar sesión varias veces.
*/
if ($usuarioId !== null) {
    $stmtExiste = $conexion->prepare("
        SELECT id
        FROM descargas
        WHERE usuario_id = ?
          AND dispositivo = ?
          AND version_app = ?
        LIMIT 1
    ");

    if (!$stmtExiste) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al comprobar la instalación."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmtExiste->bind_param("iss", $usuarioId, $dispositivo, $versionApp);
} else {
    $stmtExiste = $conexion->prepare("
        SELECT id
        FROM descargas
        WHERE nombre_usuario = ?
          AND dispositivo = ?
          AND version_app = ?
        LIMIT 1
    ");

    if (!$stmtExiste) {
        echo json_encode([
            "ok" => false,
            "mensaje" => "Error al comprobar la instalación."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmtExiste->bind_param("sss", $nombreUsuario, $dispositivo, $versionApp);
}

$stmtExiste->execute();
$resultadoExiste = $stmtExiste->get_result();

if ($resultadoExiste && $resultadoExiste->num_rows > 0) {
    $stmtExiste->close();

    echo json_encode([
        "ok" => true,
        "registrada" => false,
        "mensaje" => "La instalación ya estaba registrada."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmtExiste->close();

/*
--------------------------------------------------
Insertar nueva instalación
--------------------------------------------------
*/
$stmt = $conexion->prepare("
    INSERT INTO descargas (usuario_id, nombre_usuario, dispositivo, version_app)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al preparar el registro de instalación."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("isss", $usuarioId, $nombreUsuario, $dispositivo, $versionApp);

if ($stmt->execute()) {
    echo json_encode([
        "ok" => true,
        "registrada" => true,
        "mensaje" => "Instalación registrada correctamente."
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "ok" => false,
        "registrada" => false,
        "mensaje" => "No se pudo registrar la instalación."
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conexion->close();
?>