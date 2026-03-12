<?php
/*
--------------------------------------------------
Configuración general del panel de administración
--------------------------------------------------

Este archivo inicia la sesión del sistema y guarda
las credenciales básicas de acceso al panel admin.

Se utiliza como archivo común en las páginas del
panel para controlar el acceso del administrador.
*/
session_start();

$USUARIO_ADMIN = "admin";
$PASSWORD_ADMIN = "1234";
?>