<?php
/*
--------------------------------------------------
Panel de administración - Crear nueva pregunta
--------------------------------------------------

Esta página permite al administrador añadir nuevas
preguntas al juego desde el panel de administración.

El formulario recoge:
- el texto de la pregunta
- la imagen asociada
- el nivel al que pertenece
- las cuatro posibles respuestas
- cuál de ellas es la correcta

Cada respuesta puede tener texto, imagen o ambas.
*/
require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/* Carga los niveles disponibles desde la base de datos */
$niveles = [];
$resultadoNiveles = $conexion->query("SELECT id, numero FROM niveles ORDER BY numero ASC");

if ($resultadoNiveles) {
    while ($filaNivel = $resultadoNiveles->fetch_assoc()) {
        $niveles[] = $filaNivel;
    }
}

/* Si no hay niveles cargados, usa un respaldo básico */
if (empty($niveles)) {
    $niveles = [
        ["id" => 1, "numero" => 1],
        ["id" => 2, "numero" => 2],
        ["id" => 3, "numero" => 3]
    ];
}

$mensaje = "";
$error = "";

/* Valores del formulario.
   Sirven para mantener lo escrito si hay error */
$valores = [
    "pregunta" => "",
    "imagen" => "",
    "nivel" => (string)$niveles[0]["id"],
    "respuesta1" => "",
    "respuesta2" => "",
    "respuesta3" => "",
    "respuesta4" => "",
    "imagen_respuesta1" => "",
    "imagen_respuesta2" => "",
    "imagen_respuesta3" => "",
    "imagen_respuesta4" => "",
    "correcta" => "1"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($valores as $clave => $valor) {
        $valores[$clave] = trim($_POST[$clave] ?? "");
    }

    $pregunta = $valores["pregunta"];
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

    /* Validaciones básicas del formulario */
    if ($pregunta === "") {
        $error = "Debes escribir el texto de la pregunta.";
    } elseif (!in_array($nivel, $nivelesValidos, true)) {
        $error = "Debes seleccionar un nivel válido.";
    } elseif ($correcta < 1 || $correcta > 4) {
        $error = "Debes indicar qué respuesta es la correcta.";
    } else {
        /* Cada respuesta debe tener al menos texto o imagen */
        for ($i = 0; $i < 4; $i++) {
            if ($respuestasTexto[$i] === "" && $respuestasImagen[$i] === "") {
                $error = "Cada respuesta debe tener texto, imagen o ambas.";
                break;
            }
        }

        /* La respuesta marcada como correcta no puede estar vacía */
        if ($error === "" && $respuestasTexto[$correcta - 1] === "" && $respuestasImagen[$correcta - 1] === "") {
            $error = "La respuesta marcada como correcta no puede estar vacía.";
        }
    }

    if ($error === "") {
        /* Se guarda todo dentro de una transacción para evitar medias inserciones */
        $conexion->begin_transaction();

        try {
            $stmt = $conexion->prepare("INSERT INTO preguntas (pregunta, imagen, nivel_id) VALUES (?, ?, ?)");

            if (!$stmt) {
                throw new Exception("Error al preparar la inserción de la pregunta.");
            }

            $stmt->bind_param("ssi", $pregunta, $imagen, $nivel);

            if (!$stmt->execute()) {
                throw new Exception("Error al guardar la pregunta.");
            }

            $preguntaId = (int)$conexion->insert_id;
            $stmt->close();

            $stmtRespuesta = $conexion->prepare("
                INSERT INTO respuestas (pregunta_id, texto, imagen, correcta)
                VALUES (?, ?, ?, ?)
            ");

            if (!$stmtRespuesta) {
                throw new Exception("Error al preparar la inserción de respuestas.");
            }

            /* Inserta las 4 respuestas asociadas a la nueva pregunta */
            for ($i = 0; $i < 4; $i++) {
                $texto = $respuestasTexto[$i];
                $imagenRespuesta = $respuestasImagen[$i];
                $esCorrecta = ($correcta === ($i + 1)) ? 1 : 0;

                $stmtRespuesta->bind_param("issi", $preguntaId, $texto, $imagenRespuesta, $esCorrecta);

                if (!$stmtRespuesta->execute()) {
                    throw new Exception("Error al guardar una de las respuestas.");
                }
            }

            $stmtRespuesta->close();
            $conexion->commit();

            $mensaje = "Pregunta creada correctamente.";

            /* Reinicia el formulario si todo se ha guardado bien */
            $valores = [
                "pregunta" => "",
                "imagen" => "",
                "nivel" => (string)$niveles[0]["id"],
                "respuesta1" => "",
                "respuesta2" => "",
                "respuesta3" => "",
                "respuesta4" => "",
                "imagen_respuesta1" => "",
                "imagen_respuesta2" => "",
                "imagen_respuesta3" => "",
                "imagen_respuesta4" => "",
                "correcta" => "1"
            ];
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
<title>Nueva pregunta</title>
<link rel="stylesheet" href="style.css">
<style>
    .contenedor {
        padding: 30px;
        max-width: 950px;
        margin: auto;
    }

    .campo {
        margin-bottom: 15px;
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
        min-height: 90px;
        resize: vertical;
    }

    .bloque-respuesta {
        background: #f8f9fb;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .bloque-respuesta h4 {
        margin-bottom: 12px;
        color: #1f3c88;
    }

    .ayuda-campo {
        font-size: 13px;
        color: #666;
        margin-top: 4px;
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

    .btn-volver {
        display: inline-block;
        margin-bottom: 20px;
        padding: 10px 16px;
        background: #1f3c88;
        color: white;
        text-decoration: none;
        border-radius: 8px;
    }
</style>
</head>

<body class="dashboard-body">

<header class="topbar">
<div>
<h1>Nueva pregunta</h1>
<p>Panel de administración</p>
</div>

<a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="contenedor">

<a href="preguntas.php" class="btn-volver">Volver</a>

<?php if ($mensaje !== ""): ?>
<div class="mensaje-ok"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8"); ?></div>
<?php endif; ?>

<?php if ($error !== ""): ?>
<div class="mensaje-error"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
<?php endif; ?>

<form method="POST">

    <div class="campo">
        <label for="pregunta">Pregunta</label>
        <textarea name="pregunta" id="pregunta" required><?php echo htmlspecialchars($valores["pregunta"], ENT_QUOTES, "UTF-8"); ?></textarea>
    </div>

    <div class="campo">
        <label for="imagen">Imagen de la pregunta</label>
        <input
            type="text"
            name="imagen"
            id="imagen"
            placeholder="ejemplo: nivel1/q11.png"
            value="<?php echo htmlspecialchars($valores["imagen"], ENT_QUOTES, "UTF-8"); ?>"
        >
        <div class="ayuda-campo">Opcional. Puedes dejarlo vacío si la pregunta no lleva imagen.</div>
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
        <div class="bloque-respuesta">
            <h4>Respuesta <?php echo $i; ?></h4>

            <div class="campo">
                <label for="respuesta<?php echo $i; ?>">Texto</label>
                <input
                    type="text"
                    name="respuesta<?php echo $i; ?>"
                    id="respuesta<?php echo $i; ?>"
                    placeholder="Texto opcional"
                    value="<?php echo htmlspecialchars($valores["respuesta" . $i], ENT_QUOTES, "UTF-8"); ?>"
                >
            </div>

            <div class="campo">
                <label for="imagen_respuesta<?php echo $i; ?>">Imagen</label>
                <input
                    type="text"
                    name="imagen_respuesta<?php echo $i; ?>"
                    id="imagen_respuesta<?php echo $i; ?>"
                    placeholder="ejemplo: respuestas/r<?php echo $i; ?>.png"
                    value="<?php echo htmlspecialchars($valores["imagen_respuesta" . $i], ENT_QUOTES, "UTF-8"); ?>"
                >
            </div>

            <div class="ayuda-campo">La respuesta debe tener texto, imagen o ambas.</div>
        </div>
    <?php endfor; ?>

    <div class="campo">
        <label for="correcta">Respuesta correcta</label>
        <select name="correcta" id="correcta">
            <option value="1" <?php echo ($valores["correcta"] === "1") ? "selected" : ""; ?>>Respuesta 1</option>
            <option value="2" <?php echo ($valores["correcta"] === "2") ? "selected" : ""; ?>>Respuesta 2</option>
            <option value="3" <?php echo ($valores["correcta"] === "3") ? "selected" : ""; ?>>Respuesta 3</option>
            <option value="4" <?php echo ($valores["correcta"] === "4") ? "selected" : ""; ?>>Respuesta 4</option>
        </select>
    </div>

    <button type="submit" class="btn-guardar">Crear pregunta</button>

</form>

</div>

</body>
</html>
<?php
$conexion->close();
?>