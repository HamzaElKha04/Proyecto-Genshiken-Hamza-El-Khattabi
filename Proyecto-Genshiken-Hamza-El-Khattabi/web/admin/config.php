<?php
/*
--------------------------------------------------
Configuración general del panel de administración
--------------------------------------------------

IMPORTANTE:
Este archivo está preparado para el repositorio público.
Las credenciales son privadas.

Para trabajar en local o hosting, he un archivo
config.local.php con las credenciales reales.
*/

session_start();

/*
--------------------------------------------------
Carga de configuración local privada
--------------------------------------------------

El archivo config.local.php NO se sube a GitHub.
Ahí irían las credenciales reales de cada entorno.
*/
$configLocal = __DIR__ . "/config.local.php";

if (file_exists($configLocal)) {
    require_once $configLocal;
} else {
    /*
    --------------------------------------------------
    Valores de ejemplo
    --------------------------------------------------

    Estos datos son solo orientativos.
    Cada desarrollador debe cambiarlos en config.local.php.
    */

    $USUARIO_ADMIN = "admin";
    $PASSWORD_ADMIN = "CAMBIAR_PASSWORD";

    $DB_HOST = "localhost";
    $DB_USER = "usuario_base_datos";
    $DB_PASS = "password_base_datos";
    $DB_NAME = "nombre_base_datos";
}

/*
--------------------------------------------------
Función de conexión
--------------------------------------------------

Devuelve una conexión mysqli lista para usar.
*/
function conectarDB(): mysqli
{
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

    $conexion = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    $conexion->set_charset("utf8mb4");

    return $conexion;
}
?>