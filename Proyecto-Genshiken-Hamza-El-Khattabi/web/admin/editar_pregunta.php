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
- El nivel
- Las cuatro respuestas
- La imagen de cada respuesta
- La respuesta correcta
*/
require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/* ID de la pregunta que se va a editar */
$preguntaId = isset($_GET["pregunta_id"]) ? (int)$_GET["pregunta_id"] : 0;

if ($preguntaId <= 0) {
    die("ID de pregunta no válido.");
}

/* Carga los niveles disponibles */
$niveles = [];
$resultadoNiveles = $conexion->query("SELECT id, numero FROM niveles ORDER BY numero ASC");

if ($resultadoNiveles) {
    while ($filaNivel = $resultadoNiveles->fetch_assoc()) {
        $niveles[] = $filaNivel;
    }
}

/* Respaldo por si no hubiera niveles en la tabla */
if (empty($niveles)) {
    $niveles = [
        ["id" => 1, "numero" => 1],
        ["id" => 2, "numero" => 2],
        ["id" => 3, "numero" => 3]
    ];
}

$mensaje = "";
$error = "";

/* CARGAR DATOS ACTUALES DE LA PREGUNTA */
$stmtPregunta = $conexion->prepare("SELECT * FROM preguntas WHERE id = ?");
$stmtPregunta->bind_param("i", $preguntaId);
$stmtPregunta->execute();
$resultadoPregunta = $stmtPregunta->get_result();

if ($resultadoPregunta->num_rows === 0) {
    die("Pregunta no encontrada.");
}

$preguntaBD = $resultadoPregunta->fetch_assoc();
$stmtPregunta->close();

/* CARGAR RESPUESTAS ACTUALES DE ESA PREGUNTA */
$stmtRespuestas = $conexion->prepare("SELECT * FROM respuestas WHERE pregunta_id = ? ORDER BY id ASC");
$stmtRespuestas->bind_param("i", $preguntaId);
$stmtRespuestas->execute();
$resultadoRespuestas = $stmtRespuestas->get_result();

$respuestasBD = [];
while ($filaRespuesta = $resultadoRespuestas->fetch_assoc()) {
    $respuestasBD[] = $filaRespuesta;
}
$stmtRespuestas->close();

if (count($respuestasBD) !== 4) {
    die("Esta pregunta no tiene exactamente 4 respuestas cargadas.");
}

/* Valores que se muestran en el formulario */
$valores = [
    "pregunta" => $preguntaBD["pregunta"] ?? "",
    "imagen" => $preguntaBD["imagen"] ?? "",
    "nivel" => (string)($preguntaBD["nivel_id"] ?? $niveles[0]["id"]),
    "respuesta1" => $respuestasBD[0]["texto"] ?? "",
    "respuesta2" => $respuestasBD[1]["texto"] ?? "",
    "respuesta3" => $respuestasBD[2]["texto"] ?? "",
    "respuesta4" => $respuestasBD[3]["texto"] ?? "",
    "imagen_respuesta1" => $respuestasBD[0]["imagen"] ?? "",
    "imagen_respuesta2" => $respuestasBD[1]["imagen"] ?? "",
    "imagen_respuesta3" => $respuestasBD[2]["imagen"] ?? "",
    "imagen_respuesta4" => $respuestasBD[3]["imagen"] ?? "",
    "correcta" => "1"
];

/* Detecta cuál de las 4 respuestas es la correcta */
for ($i = 0; $i < 4; $i++) {
    if ((int)$respuestasBD[$i]["correcta"] === 1) {
        $valores["correcta"] = (string)($i + 1);
        break;
    }
}

/* GUARDAR CAMBIOS */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($valores as $clave => $valor) {
        $valores[$clave] = trim($_POST[$clave] ?? "");
    }

    $preguntaTexto = $valores["pregunta"];
    $imagen = $valores["imagen"];
    $nivel = (int)$valores["nivel"];
    $correcta = (int)$valores["correcta"];

    $nivelesValidos = array_map(fn($n) => (int)$n["id"], $niveles);

    $respuestasTexto = [
        $valores["respuesta1"],
        $valores["respuesta2"],
        $valores["respuesta3"],
        $valores["respuesta4"]
    ];

    $respuestasImagen = [
        $valores["imagen_respuesta1"],
        $valores["imagen_respuesta2"],
        $valores["imagen_respuesta3"],
        $valores["imagen_respuesta4"]
    ];

    /* Validaciones antes de guardar */
    if ($preguntaTexto === "") {
        $error = "Debes escribir el texto de la pregunta.";
    } elseif (!in_array($nivel, $nivelesValidos, true)) {
        $error = "Debes seleccionar un nivel válido.";
    } elseif ($correcta < 1 || $correcta > 4) {
        $error = "Debes seleccionar una respuesta correcta válida.";
    } else {
        for ($i = 0; $i < 4; $i++) {
            if ($respuestasTexto[$i] === "" && $respuestasImagen[$i] === "") {
                $error = "Cada respuesta debe tener texto, imagen o ambas.";
                break;
            }
        }

        if ($error === "" && $respuestasTexto[$correcta - 1] === "" && $respuestasImagen[$correcta - 1] === "") {
            $error = "La respuesta marcada como correcta no puede estar vacía.";
        }
    }

    if ($error === "") {
        /* Se actualiza todo con transacción para evitar cambios a medias */
        $conexion->begin_transaction();

        try {
            $stmtActualizarPregunta = $conexion->prepare("
                UPDATE preguntas
                SET pregunta = ?, imagen = ?, nivel_id = ?
                WHERE id = ?
            ");

            if (!$stmtActualizarPregunta) {
                throw new Exception("Error al preparar la actualización de la pregunta.");
            }

            $stmtActualizarPregunta->bind_param("ssii", $preguntaTexto, $imagen, $nivel, $preguntaId);

            if (!$stmtActualizarPregunta->execute()) {
                throw new Exception("Error al actualizar la pregunta.");
            }

            $stmtActualizarPregunta->close();

            /* Actualiza una a una las 4 respuestas */
            for ($i = 0; $i < 4; $i++) {
                $idRespuesta = (int)$respuestasBD[$i]["id"];
                $textoRespuesta = $respuestasTexto[$i];
                $imagenRespuesta = $respuestasImagen[$i];
                $esCorrecta = ($correcta === ($i + 1)) ? 1 : 0;

                $stmtUpdateRespuesta = $conexion->prepare("
                    UPDATE respuestas
                    SET texto = ?, imagen = ?, correcta = ?
                    WHERE id = ?
                ");

                if (!$stmtUpdateRespuesta) {
                    throw new Exception("Error al preparar la actualización de una respuesta.");
                }

                $stmtUpdateRespuesta->bind_param("ssii", $textoRespuesta, $imagenRespuesta, $esCorrecta, $idRespuesta);

                if (!$stmtUpdateRespuesta->execute()) {
                    throw new Exception("Error al actualizar una respuesta.");
                }

                $stmtUpdateRespuesta->close();
            }

            $conexion->commit();
            $mensaje = "Pregunta actualizada correctamente.";
        } catch (Exception $e) {
            $conexion->rollback();
            $error = $e->getMessage();
        }
    }
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
            max-width: 950px;
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
        .campo textarea,
        .campo select {
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

        .respuesta-bloque h4 {
            margin-bottom: 12px;
            color: #1f3c88;
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

        .ayuda-campo {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
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
            <div class="mensaje-ok"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="mensaje-error"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="campo">
                <label for="pregunta">Texto de la pregunta</label>
                <textarea name="pregunta" id="pregunta" required><?php echo htmlspecialchars($valores["pregunta"], ENT_QUOTES, "UTF-8"); ?></textarea>
            </div>

            <div class="campo">
                <label for="imagen">Ruta de la imagen</label>
                <input
                    type="text"
                    name="imagen"
                    id="imagen"
                    value="<?php echo htmlspecialchars($valores["imagen"], ENT_QUOTES, "UTF-8"); ?>"
                    placeholder="ejemplo: nivel1/q11.png"
                >

                <?php if (!empty($valores["imagen"])): ?>
                    <img
                        src="../img/<?php echo htmlspecialchars($valores["imagen"], ENT_QUOTES, "UTF-8"); ?>"
                        alt="Imagen actual"
                        class="imagen-preview"
                    >
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="nivel">Nivel</label>
                <select name="nivel" id="nivel">
                    <?php foreach ($niveles as $nivelItem): ?>
                        <option value="<?php echo (int)$nivelItem["id"]; ?>" <?php echo ((string)$nivelItem["id"] === $valores["nivel"]) ? "selected" : ""; ?>>
                            Nivel <?php echo htmlspecialchars($nivelItem["numero"], ENT_QUOTES, "UTF-8"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h3>Respuestas</h3>

            <?php for ($i = 1; $i <= 4; $i++): ?>
                <div class="respuesta-bloque">
                    <h4>Respuesta <?php echo $i; ?></h4>

                    <div class="campo">
                        <label for="respuesta<?php echo $i; ?>">Texto</label>
                        <input
                            type="text"
                            name="respuesta<?php echo $i; ?>"
                            id="respuesta<?php echo $i; ?>"
                            value="<?php echo htmlspecialchars($valores["respuesta" . $i], ENT_QUOTES, "UTF-8"); ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="imagen_respuesta<?php echo $i; ?>">Imagen</label>
                        <input
                            type="text"
                            name="imagen_respuesta<?php echo $i; ?>"
                            id="imagen_respuesta<?php echo $i; ?>"
                            value="<?php echo htmlspecialchars($valores["imagen_respuesta" . $i], ENT_QUOTES, "UTF-8"); ?>"
                            placeholder="ejemplo: respuestas/r<?php echo $i; ?>.png"
                        >

                        <?php if (!empty($valores["imagen_respuesta" . $i])): ?>
                            <img
                                src="../img/<?php echo htmlspecialchars($valores["imagen_respuesta" . $i], ENT_QUOTES, "UTF-8"); ?>"
                                alt="Imagen respuesta <?php echo $i; ?>"
                                class="imagen-preview"
                            >
                        <?php endif; ?>
                    </div>

                    <div class="radio-correcta">
                        <label>
                            <input
                                type="radio"
                                name="correcta"
                                value="<?php echo $i; ?>"
                                <?php echo ($valores["correcta"] === (string)$i) ? "checked" : ""; ?>
                                required
                            >
                            Marcar como correcta
                        </label>
                    </div>

                    <div class="ayuda-campo">La respuesta debe tener texto, imagen o ambas.</div>
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