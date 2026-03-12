<?php
/*
--------------------------------------------------
Panel de administración - Editar pregunta
--------------------------------------------------

Esta página permite modificar una pregunta ya
existente en la base de datos.

Desde aquí se puede editar:
- El texto de la pregunta
- La ruta de la imagen
- Las cuatro respuestas
- La respuesta correcta

Los cambios se guardan directamente en la base
de datos para actualizar el contenido del juego.
*/
require_once "config.php";

if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = new mysqli("127.0.0.1", "root", "", "u842177649_genshiapp");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$preguntaId = isset($_GET["pregunta_id"]) ? (int)$_GET["pregunta_id"] : 0;

if ($preguntaId <= 0) {
    die("ID de pregunta no válido.");
}

$mensaje = "";
$error = "";

/* GUARDAR CAMBIOS */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $preguntaTexto = trim($_POST["pregunta"] ?? "");
    $imagen = trim($_POST["imagen"] ?? "");
    $respuesta1 = trim($_POST["respuesta1"] ?? "");
    $respuesta2 = trim($_POST["respuesta2"] ?? "");
    $respuesta3 = trim($_POST["respuesta3"] ?? "");
    $respuesta4 = trim($_POST["respuesta4"] ?? "");
    $correcta = isset($_POST["correcta"]) ? (int)$_POST["correcta"] : 0;

    if (
        $preguntaTexto === "" ||
        $respuesta1 === "" ||
        $respuesta2 === "" ||
        $respuesta3 === "" ||
        $respuesta4 === "" ||
        $correcta < 1 || $correcta > 4
    ) {
        $error = "Debes rellenar todos los campos y seleccionar una respuesta correcta.";
    } else {
        $stmtPregunta = $conexion->prepare("UPDATE preguntas SET pregunta = ?, imagen = ? WHERE id = ?");
        $stmtPregunta->bind_param("ssi", $preguntaTexto, $imagen, $preguntaId);

        if ($stmtPregunta->execute()) {
            $sqlRespuestas = "SELECT id FROM respuestas WHERE pregunta_id = ? ORDER BY id ASC";
            $stmtIds = $conexion->prepare($sqlRespuestas);
            $stmtIds->bind_param("i", $preguntaId);
            $stmtIds->execute();
            $resultadoIds = $stmtIds->get_result();

            $idsRespuestas = [];
            while ($filaId = $resultadoIds->fetch_assoc()) {
                $idsRespuestas[] = $filaId["id"];
            }

            if (count($idsRespuestas) === 4) {
                $respuestas = [$respuesta1, $respuesta2, $respuesta3, $respuesta4];

                for ($i = 0; $i < 4; $i++) {
                    $textoRespuesta = $respuestas[$i];
                    $esCorrecta = ($correcta === ($i + 1)) ? 1 : 0;
                    $idRespuesta = $idsRespuestas[$i];

                    $stmtUpdateRespuesta = $conexion->prepare(
                        "UPDATE respuestas SET texto = ?, correcta = ? WHERE id = ?"
                    );
                    $stmtUpdateRespuesta->bind_param("sii", $textoRespuesta, $esCorrecta, $idRespuesta);
                    $stmtUpdateRespuesta->execute();
                }

                $mensaje = "Pregunta actualizada correctamente.";
            } else {
                $error = "No se encontraron exactamente 4 respuestas asociadas a esta pregunta.";
            }
        } else {
            $error = "Error al actualizar la pregunta.";
        }
    }
}

/* CARGAR DATOS ACTUALES */
$stmtPregunta = $conexion->prepare("SELECT * FROM preguntas WHERE id = ?");
$stmtPregunta->bind_param("i", $preguntaId);
$stmtPregunta->execute();
$resultadoPregunta = $stmtPregunta->get_result();

if ($resultadoPregunta->num_rows === 0) {
    die("Pregunta no encontrada.");
}

$pregunta = $resultadoPregunta->fetch_assoc();

$stmtRespuestas = $conexion->prepare("SELECT * FROM respuestas WHERE pregunta_id = ? ORDER BY id ASC");
$stmtRespuestas->bind_param("i", $preguntaId);
$stmtRespuestas->execute();
$resultadoRespuestas = $stmtRespuestas->get_result();

$respuestas = [];
while ($filaRespuesta = $resultadoRespuestas->fetch_assoc()) {
    $respuestas[] = $filaRespuesta;
}

if (count($respuestas) < 4) {
    die("Esta pregunta no tiene 4 respuestas cargadas.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar pregunta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contenedor {
            padding: 30px;
            max-width: 900px;
            margin: 0 auto;
        }

        .caja {
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            padding: 25px;
        }

        .btn-volver {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 16px;
            background: #1f3c88;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .campo {
            margin-bottom: 18px;
        }

        .campo label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .campo input[type="text"],
        .campo textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .campo textarea {
            resize: vertical;
            min-height: 90px;
        }

        .imagen-preview {
            width: 260px;
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fff;
        }

        .respuesta-bloque {
            background: #f8f9fb;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .radio-correcta {
            margin-top: 10px;
        }

        .btn-guardar {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
        }

        .mensaje-ok {
            background: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .mensaje-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
        }
    </style>
</head>
<body class="dashboard-body">

<header class="topbar">
    <div>
        <h1>Editar pregunta</h1>
        <p>Panel de administración</p>
    </div>

    <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="contenedor">
    <a href="preguntas.php" class="btn-volver">Volver a preguntas</a>

    <div class="caja">
        <?php if ($mensaje !== ""): ?>
            <div class="mensaje-ok"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="mensaje-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="campo">
                <label for="pregunta">Texto de la pregunta</label>
                <textarea name="pregunta" id="pregunta" required><?php echo htmlspecialchars($pregunta["pregunta"]); ?></textarea>
            </div>

            <div class="campo">
                <label for="imagen">Ruta de la imagen</label>
                <input
                    type="text"
                    name="imagen"
                    id="imagen"
                    value="<?php echo htmlspecialchars($pregunta["imagen"]); ?>"
                    required
                >

                <?php if (!empty($pregunta["imagen"])): ?>
                    <img
                        src="../img/<?php echo htmlspecialchars($pregunta["imagen"]); ?>"
                        alt="Imagen actual"
                        class="imagen-preview"
                    >
                <?php endif; ?>
            </div>

            <h3>Respuestas</h3>

            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="respuesta-bloque">
                    <div class="campo">
                        <label for="respuesta<?php echo $i + 1; ?>">Respuesta <?php echo $i + 1; ?></label>
                        <input
                            type="text"
                            name="respuesta<?php echo $i + 1; ?>"
                            id="respuesta<?php echo $i + 1; ?>"
                            value="<?php echo htmlspecialchars($respuestas[$i]["texto"]); ?>"
                            required
                        >
                    </div>

                    <div class="radio-correcta">
                        <label>
                            <input
                                type="radio"
                                name="correcta"
                                value="<?php echo $i + 1; ?>"
                                <?php echo ((int)$respuestas[$i]["correcta"] === 1) ? "checked" : ""; ?>
                                required
                            >
                            Marcar como correcta
                        </label>
                    </div>
                </div>
            <?php endfor; ?>

            <button type="submit" class="btn-guardar">Guardar cambios</button>
        </form>
    </div>
</div>

</body>
</html>
<?php
$conexion->close();
?>