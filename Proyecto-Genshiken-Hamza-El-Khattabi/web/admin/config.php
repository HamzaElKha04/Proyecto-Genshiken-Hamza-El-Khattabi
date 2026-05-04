<?php
/*
--------------------------------------------------
Configuración general del panel de administración
--------------------------------------------------

Este archivo:
- inicia la sesión
- guarda las credenciales del admin
- centraliza la conexión a la base de datos
*/

session_start();

/* Credenciales del panel admin */
$USUARIO_ADMIN = "admin";
$PASSWORD_ADMIN = "1234";

/* Configuración de base de datos */
$DB_HOST = "127.0.0.1";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "u842177649_genshiapp";

/*
--------------------------------------------------
Función de conexión
--------------------------------------------------

Devuelve una conexión mysqli lista para usar.
Así, cuando cambiemos a hosting más adelante, solo
tendreé que modificar este archivo.
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