<?php
/*
--------------------------------------------------
Panel de administración - Eliminar espada
--------------------------------------------------

Elimina una espada del catálogo del gacha.

Importante:
- Se elimina de la tabla espadas.
- No se borra físicamente la imagen del servidor.
- No se toca coleccion_usuario directamente.
*/

require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

$espadaId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($espadaId <= 0) {
    $_SESSION["mensaje_error"] = "ID de espada no válido.";
    header("Location: espadas.php");
    exit;
}

try {
    $stmtExiste = $conexion->prepare("SELECT id FROM espadas WHERE id = ?");

    if (!$stmtExiste) {
        throw new Exception("Error al comprobar la espada.");
    }

    $stmtExiste->bind_param("i", $espadaId);
    $stmtExiste->execute();

    $resultadoExiste = $stmtExiste->get_result();

    if (!$resultadoExiste || $resultadoExiste->num_rows === 0) {
        $stmtExiste->close();
        throw new Exception("La espada no existe.");
    }

    $stmtExiste->close();

    $stmtEliminar = $conexion->prepare("DELETE FROM espadas WHERE id = ?");

    if (!$stmtEliminar) {
        throw new Exception("Error al preparar la eliminación.");
    }

    $stmtEliminar->bind_param("i", $espadaId);

    if (!$stmtEliminar->execute()) {
        throw new Exception("No se pudo eliminar la espada: " . $stmtEliminar->error);
    }

    $stmtEliminar->close();

    $_SESSION["mensaje_ok"] = "Espada eliminada correctamente.";
} catch (Exception $e) {
    $_SESSION["mensaje_error"] = $e->getMessage();
}

$conexion->close();

header("Location: espadas.php");
exit;
?>