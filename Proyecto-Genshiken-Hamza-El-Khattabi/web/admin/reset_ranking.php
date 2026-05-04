<?php
/*
--------------------------------------------------
Reset del Ranking mensual
--------------------------------------------------

Este archivo reinicia el ranking actual del juego.

Antes de vaciar la tabla de puntuaciones:
- guarda el ranking completo del mes
- registra usuario_id, nombre, puntos, tiempo,
  posición, mes, año y fecha real de la partida

Comportamiento importante:
- solo puede existir UN histórico por mes y año
- si ya existe uno para el mes actual, se reemplaza

Después:
- vacía el ranking actual
- deja preparado el sistema para el siguiente periodo
*/

require_once "config.php";

/* Verificar sesión de administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/*
--------------------------------------------------
Guardar ranking mensual completo antes del reset
--------------------------------------------------
*/
$sqlRanking = "SELECT usuario_id, nombre, puntos, tiempo, fecha
               FROM puntuaciones
               ORDER BY puntos DESC, tiempo ASC, fecha ASC";

$resultado = $conexion->query($sqlRanking);

if (!$resultado) {
    die("Error al obtener el ranking: " . $conexion->error);
}

if ($resultado->num_rows > 0) {
    $mes = (int) date("n");
    $anio = (int) date("Y");
    $posicion = 1;

    $conexion->begin_transaction();

    try {
        /*
        --------------------------------------------------
        Borrar histórico anterior del mismo mes y año
        para que solo exista un ranking por mes
        --------------------------------------------------
        */
        $stmtBorrarHistoricoMes = $conexion->prepare("
            DELETE FROM ranking_mensual_historico
            WHERE mes = ? AND anio = ?
        ");

        if (!$stmtBorrarHistoricoMes) {
            throw new Exception("Error al preparar el borrado del histórico mensual.");
        }

        $stmtBorrarHistoricoMes->bind_param("ii", $mes, $anio);

        if (!$stmtBorrarHistoricoMes->execute()) {
            $stmtBorrarHistoricoMes->close();
            throw new Exception("Error al borrar el histórico anterior del mes.");
        }

        $stmtBorrarHistoricoMes->close();

        /*
        --------------------------------------------------
        Insertar nuevo histórico del mes actual
        --------------------------------------------------
        */
        $stmtInsertar = $conexion->prepare("
            INSERT INTO ranking_mensual_historico
            (usuario_id, nombre, puntos, tiempo, posicion, mes, anio, fecha_partida)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmtInsertar) {
            throw new Exception("Error al preparar inserción del histórico.");
        }

        while ($fila = $resultado->fetch_assoc()) {
            $usuario_id = isset($fila["usuario_id"]) ? (int)$fila["usuario_id"] : 1;
            $nombre = trim($fila["nombre"] ?? "");
            $fechaPartida = $fila["fecha"] ?? null;

            if ($nombre === "") {
                $nombre = "Anónimo";
            }

            if (mb_strlen($nombre, "UTF-8") > 100) {
                $nombre = mb_substr($nombre, 0, 100, "UTF-8");
            }

            $puntos = (int)$fila["puntos"];
            $tiempo = (int)$fila["tiempo"];

            $stmtInsertar->bind_param(
                "isiiiiis",
                $usuario_id,
                $nombre,
                $puntos,
                $tiempo,
                $posicion,
                $mes,
                $anio,
                $fechaPartida
            );

            if (!$stmtInsertar->execute()) {
                $stmtInsertar->close();
                throw new Exception("Error al guardar una fila en el histórico.");
            }

            $posicion++;
        }

        $stmtInsertar->close();

        /*
        --------------------------------------------------
        Vaciar ranking actual
        --------------------------------------------------
        */
        if (!$conexion->query("TRUNCATE TABLE puntuaciones")) {
            throw new Exception("Error al vaciar la tabla de puntuaciones.");
        }

        $conexion->commit();
    } catch (Exception $e) {
        $conexion->rollback();
        $conexion->close();
        die($e->getMessage());
    }
}

/* Cerrar conexión */
$conexion->close();

/* Volver al ranking */
header("Location: ranking.php");
exit;
?>