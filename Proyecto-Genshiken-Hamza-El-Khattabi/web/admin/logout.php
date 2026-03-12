<?php
/*
--------------------------------------------------
Cierre de sesión del panel de administración
--------------------------------------------------

Este archivo destruye la sesión activa del
administrador y redirige de nuevo a la pantalla
de login.

Se utiliza para cerrar el acceso al panel de
forma segura.
*/
require_once "config.php";

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>