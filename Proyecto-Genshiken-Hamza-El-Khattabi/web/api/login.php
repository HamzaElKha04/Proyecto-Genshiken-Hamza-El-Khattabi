<?php
/*
--------------------------------------------------
API - Login de usuario desde Android
--------------------------------------------------

Este archivo permite iniciar sesión desde la app
Android usando correo y contraseña.

La app envía:
- correo
- password

La API devuelve JSON con:
- status
- id
- nombre

Formato esperado por UserRepository.kt:
{
  "status": "OK",
  "id": 1,
  "nombre": "Hamza"
}
*/

header('Content-Type: application/json; charset=utf-8');

require_once "../admin/config.php";

$conexion = conectarDB();

/* Recogemos los datos enviados por Android */
$correo = trim($_POST["correo"] ?? "");
$password = trim($_POST["password"] ?? "");

/* Validación básica */
if ($correo === "" || $password === "") {
    echo json_encode([
        "status" => "ERROR",
        "mensaje" => "Datos incompletos."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
--------------------------------------------------
Buscar usuario por correo
--------------------------------------------------
*/
$stmt = $conexion->prepare("
    SELECT id, username, password
    FROM usuarios
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    echo json_encode([
        "status" => "ERROR",
        "mensaje" => "Error preparando la consulta."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode([
        "status" => "ERROR",
        "mensaje" => "Usuario no encontrado."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario = $resultado->fetch_assoc();

/*
--------------------------------------------------
Comprobar contraseña
--------------------------------------------------

Acepta:
- contraseñas nuevas guardadas con password_hash
- contraseñas antiguas en texto plano, por si ya había
  datos de prueba en la base de datos.
*/
$passwordBD = $usuario["password"];

$passwordCorrecta = password_verify($password, $passwordBD) || $password === $passwordBD;

if (!$passwordCorrecta) {
    echo json_encode([
        "status" => "ERROR",
        "mensaje" => "Contraseña incorrecta."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/*
--------------------------------------------------
Respuesta correcta para Android
--------------------------------------------------
*/
echo json_encode([
    "status" => "OK",
    "id" => (int)$usuario["id"],
    "nombre" => $usuario["username"]
], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conexion->close();
?>