<?php
/*
--------------------------------------------------
API - Registro de usuario desde Android
--------------------------------------------------

Este archivo permite registrar usuarios desde la app
Android.

La app envía:
- nombre
- correo
- password

La API guarda esos datos en la tabla usuarios.

Respuesta esperada por Android:
- OK      -> registro correcto
- EXISTE  -> el correo ya está registrado
- ERROR   -> error general

IMPORTANTE:
Esta API está pensada para integrarse con la app.
No afecta al panel admin ni al login del panel web.
*/

header('Content-Type: text/plain; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();

/* Recogemos los datos enviados por Android */
$nombre = trim($_POST["nombre"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$password = trim($_POST["password"] ?? "");

/* Validaciones básicas */
if ($nombre === "" || $correo === "" || $password === "") {
    echo "ERROR";
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo "ERROR";
    exit;
}

if (mb_strlen($nombre, "UTF-8") > 100) {
    $nombre = mb_substr($nombre, 0, 100, "UTF-8");
}

if (mb_strlen($correo, "UTF-8") > 150) {
    echo "ERROR";
    exit;
}

/*
--------------------------------------------------
Comprobar si el correo ya existe
--------------------------------------------------
*/
$stmtExiste = $conexion->prepare("
    SELECT id 
    FROM usuarios 
    WHERE email = ?
    LIMIT 1
");

if (!$stmtExiste) {
    echo "ERROR";
    exit;
}

$stmtExiste->bind_param("s", $correo);
$stmtExiste->execute();
$resultadoExiste = $stmtExiste->get_result();

if ($resultadoExiste->num_rows > 0) {
    $stmtExiste->close();
    $conexion->close();
    echo "EXISTE";
    exit;
}

$stmtExiste->close();

/*
--------------------------------------------------
Guardar usuario
--------------------------------------------------

Se guarda la contraseña con password_hash para no
almacenarla en texto plano.

El panel admin seguirá funcionando igual, porque su
login usa admin/config.php y no depende de esta tabla.
*/
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmtInsertar = $conexion->prepare("
    INSERT INTO usuarios (username, email, password, fecha_registro, email_verificado)
    VALUES (?, ?, ?, NOW(), 0)
");

if (!$stmtInsertar) {
    echo "ERROR";
    exit;
}

$stmtInsertar->bind_param("sss", $nombre, $correo, $passwordHash);

if ($stmtInsertar->execute()) {
    echo "OK";
} else {
    echo "ERROR";
}

$stmtInsertar->close();
$conexion->close();
?>