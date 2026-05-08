<?php
/*
--------------------------------------------------
API - Registrar acceso a la app
--------------------------------------------------

Este archivo registra un acceso o uso inicial de la
app Android.

IMPORTANTE:
El archivo se sigue llamando registrarDescarga.php
para no romper la conexión con Android, pero su uso
real es registrar accesos/inicios de sesión.

Datos que recibe:
- usuario_id
- nombre_usuario
- dispositivo
- version_app

Luego estos datos se muestran en el panel admin,
en la sección "Accesos a la app".
*/

header('Content-Type: application/json; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();
$conexion->set_charset("utf8mb4");

/*
--------------------------------------------------
Lectura de datos recibidos
--------------------------------------------------

Primero intenta leer JSON.
Si no llega JSON, usa POST normal.
*/
$datos = json_decode(file_get_contents("php://input"), true);

if (!is_array($datos)) {
    $datos = $_POST;
}

/* Datos enviados desde Android */
$usuarioId = isset($datos["usuario_id"]) && $datos["usuario_id"] !== "" ? (int)$datos["usuario_id"] : null;
$nombreUsuario = trim($datos["nombre_usuario"] ?? "Anónimo");
$dispositivo = trim($datos["dispositivo"] ?? "");
$versionApp = trim($datos["version_app"] ?? "");

/* Si no llega nombre, se guarda como Anónimo */
if ($nombreUsuario === "") {
    $nombreUsuario = "Anónimo";
}

/* El dispositivo es obligatorio para que el registro tenga sentido */
if ($dispositivo === "") {
    echo json_encode([
        "ok" => false,
        "mensaje" => "El dispositivo es obligatorio."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
--------------------------------------------------
Inserción en base de datos
--------------------------------------------------

La tabla mantiene el nombre "descargas" por compatibilidad,
pero funcionalmente representa accesos o usos de la app.
*/
$stmt = $conexion->prepare("
    INSERT INTO descargas (usuario_id, nombre_usuario, dispositivo, version_app)
    VALUES (?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al preparar el registro de acceso."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("isss", $usuarioId, $nombreUsuario, $dispositivo, $versionApp);

/* Respuesta JSON para Android */
if ($stmt->execute()) {
    echo json_encode([
        "ok" => true,
        "mensaje" => "Acceso registrado correctamente."
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo registrar el acceso."
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conexion->close();
?>