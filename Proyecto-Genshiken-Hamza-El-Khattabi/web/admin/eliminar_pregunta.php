<?php
/*
--------------------------------------------------
Eliminar pregunta - Panel de administración
--------------------------------------------------

Este archivo permite eliminar una pregunta del juego
desde el panel de administración.

Cuando se recibe el ID de la pregunta por la URL,
primero se eliminan las respuestas asociadas a esa
pregunta y después se elimina la propia pregunta
de la base de datos.

Finalmente se redirige de nuevo al listado de
preguntas del panel.
*/
require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/* ID recibido de la pregunta que se quiere borrar */
$preguntaId = isset($_GET["pregunta_id"]) ? (int)$_GET["pregunta_id"] : 0;

if ($preguntaId <= 0) {
    $_SESSION["mensaje_error"] = "ID de pregunta no válido.";
    header("Location: preguntas.php");
    exit;
}

/* Se usa transacción para borrar respuestas y pregunta de forma segura */
$conexion->begin_transaction();

try {
    /* Comprueba antes que la pregunta existe */
    $stmtExiste = $conexion->prepare("SELECT id FROM preguntas WHERE id = ?");
    if (!$stmtExiste) {
        throw new Exception("Error al comprobar la pregunta.");
    }

    $stmtExiste->bind_param("i", $preguntaId);
    $stmtExiste->execute();
    $resultadoExiste = $stmtExiste->get_result();

    if ($resultadoExiste->num_rows === 0) {
        $stmtExiste->close();
        throw new Exception("La pregunta no existe.");
    }

    $stmtExiste->close();

    /* Borra primero las respuestas asociadas */
    $stmtRespuestas = $conexion->prepare("DELETE FROM respuestas WHERE pregunta_id = ?");
    if (!$stmtRespuestas) {
        throw new Exception("Error al preparar el borrado de respuestas.");
    }

    $stmtRespuestas->bind_param("i", $preguntaId);
    if (!$stmtRespuestas->execute()) {
        $stmtRespuestas->close();
        throw new Exception("Error al borrar las respuestas.");
    }
    $stmtRespuestas->close();

    /* Después borra la pregunta principal */
    $stmtPregunta = $conexion->prepare("DELETE FROM preguntas WHERE id = ?");
    if (!$stmtPregunta) {
        throw new Exception("Error al preparar el borrado de la pregunta.");
    }

    $stmtPregunta->bind_param("i", $preguntaId);
    if (!$stmtPregunta->execute()) {
        $stmtPregunta->close();
        throw new Exception("Error al borrar la pregunta.");
    }
    $stmtPregunta->close();

    $conexion->commit();
    $_SESSION["mensaje_ok"] = "Pregunta eliminada correctamente.";
} catch (Exception $e) {
    $conexion->rollback();
    $_SESSION["mensaje_error"] = $e->getMessage();
}

$conexion->close();

header("Location: preguntas.php");
exit;
?>