<?php
/*
--------------------------------------------------
Panel de administración - Editar pregunta
--------------------------------------------------

Esta página permite modificar una pregunta ya
existente en la base de datos.

Desde aquí se puede editar:
- El texto de la pregunta
- La imagen de la pregunta
- El nivel
- Las cuatro respuestas
- La imagen de cada respuesta
- La respuesta correcta

Mejora añadida:
- El administrador ya no tiene que escribir rutas
  de imagen manualmente.
- Se usa un selector de archivos para elegir una
  nueva imagen desde el ordenador.
- Si no se selecciona ninguna imagen nueva, se
  conserva la imagen que ya tenía la pregunta.
- Las imágenes nuevas se suben automáticamente a:
  /img/nivel1, /img/nivel2, etc.
- No se toca /img/gacha porque esa carpeta es solo
  para las espadas del gacha.
*/

require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/*
--------------------------------------------------
Funciones auxiliares
--------------------------------------------------
*/

function escapar($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8");
}

function existeTabla($conexion, $tabla)
{
    $tabla = $conexion->real_escape_string($tabla);
    $resultado = $conexion->query("SHOW TABLES LIKE '$tabla'");

    return $resultado && $resultado->num_rows > 0;
}

function existeColumna($conexion, $tabla, $columna)
{
    $tabla = $conexion->real_escape_string($tabla);
    $columna = $conexion->real_escape_string($columna);

    $resultado = $conexion->query("SHOW COLUMNS FROM `$tabla` LIKE '$columna'");

    return $resultado && $resultado->num_rows > 0;
}

function obtenerNumeroNivel($niveles, $nivelId)
{
    foreach ($niveles as $nivel) {
        if ((int)$nivel["id"] === (int)$nivelId) {
            return (int)$nivel["numero"];
        }
    }

    return 1;
}

function nivelEsValido($niveles, $nivelId)
{
    foreach ($niveles as $nivel) {
        if ((int)$nivel["id"] === (int)$nivelId) {
            return true;
        }
    }

    return false;
}

function limpiarNombreArchivo($nombreOriginal)
{
    $nombreOriginal = basename($nombreOriginal);

    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $nombreSinExtension = pathinfo($nombreOriginal, PATHINFO_FILENAME);

    $nombreSinExtension = strtolower($nombreSinExtension);
    $nombreSinExtension = preg_replace('/[^a-z0-9_-]/', '_', $nombreSinExtension);
    $nombreSinExtension = preg_replace('/_+/', '_', $nombreSinExtension);
    $nombreSinExtension = trim($nombreSinExtension, '_');

    if ($nombreSinExtension === "") {
        $nombreSinExtension = "imagen";
    }

    return $nombreSinExtension . "." . $extension;
}

function subirImagen($campoArchivo, $numeroNivel)
{
    if (!isset($_FILES[$campoArchivo])) {
        return "";
    }

    if ($_FILES[$campoArchivo]["error"] === UPLOAD_ERR_NO_FILE) {
        return "";
    }

    if ($_FILES[$campoArchivo]["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Error al subir la imagen. Código: " . $_FILES[$campoArchivo]["error"]);
    }

    $extensionesPermitidas = ["jpg", "jpeg", "png", "gif", "webp"];
    $extension = strtolower(pathinfo($_FILES[$campoArchivo]["name"], PATHINFO_EXTENSION));

    if (!in_array($extension, $extensionesPermitidas, true)) {
        throw new Exception("Formato de imagen no permitido. Usa JPG, PNG, GIF o WEBP.");
    }

    $carpetaNivel = "nivel" . (int)$numeroNivel;
    $directorioDestino = __DIR__ . "/../img/" . $carpetaNivel;

    if (!is_dir($directorioDestino)) {
        if (!mkdir($directorioDestino, 0755, true)) {
            throw new Exception("No se pudo crear la carpeta de imágenes del nivel.");
        }
    }

    $nombreArchivo = limpiarNombreArchivo($_FILES[$campoArchivo]["name"]);
    $rutaDestino = $directorioDestino . "/" . $nombreArchivo;

    /*
    Si ya existe una imagen con ese nombre,
    se añade fecha y hora para evitar sobrescribirla.
    */
    if (file_exists($rutaDestino)) {
        $nombreSinExtension = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $nombreArchivo = $nombreSinExtension . "_" . date("Ymd_His") . "." . $extension;
        $rutaDestino = $directorioDestino . "/" . $nombreArchivo;
    }

    if (!move_uploaded_file($_FILES[$campoArchivo]["tmp_name"], $rutaDestino)) {
        throw new Exception("No se pudo guardar la imagen subida.");
    }

    return $carpetaNivel . "/" . $nombreArchivo;
}

function obtenerImagenFinal($campoTexto, $campoArchivo, $numeroNivel)
{
    /*
    Prioridad:
    1. Si se selecciona una imagen nueva desde el ordenador, se sube y se usa esa.
    2. Si no se selecciona nada, se conserva el valor oculto que ya tenía el formulario.
    */
    $imagenSubida = subirImagen($campoArchivo, $numeroNivel);

    if ($imagenSubida !== "") {
        return $imagenSubida;
    }

    return trim($_POST[$campoTexto] ?? "");
}

/*
--------------------------------------------------
ID de la pregunta
--------------------------------------------------
*/

$preguntaId = isset($_GET["pregunta_id"]) ? (int)$_GET["pregunta_id"] : 0;

if ($preguntaId <= 0) {
    die("ID de pregunta no válido.");
}

/*
--------------------------------------------------
Cargar niveles disponibles
--------------------------------------------------
*/

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
$transaccionIniciada = false;

/*
--------------------------------------------------
Detectar estructura real de la tabla preguntas
--------------------------------------------------
*/

$usaEstructuraNueva = existeColumna($conexion, "preguntas", "pregunta_texto");

/*
--------------------------------------------------
Cargar datos actuales de la pregunta
--------------------------------------------------
*/

$stmtPregunta = $conexion->prepare("SELECT * FROM preguntas WHERE id = ?");

if (!$stmtPregunta) {
    die("Error al preparar la consulta de la pregunta: " . $conexion->error);
}

$stmtPregunta->bind_param("i", $preguntaId);
$stmtPregunta->execute();
$resultadoPregunta = $stmtPregunta->get_result();

if (!$resultadoPregunta || $resultadoPregunta->num_rows === 0) {
    die("Pregunta no encontrada.");
}

$preguntaBD = $resultadoPregunta->fetch_assoc();
$stmtPregunta->close();

/*
--------------------------------------------------
Cargar respuestas actuales de la tabla respuestas
--------------------------------------------------
*/

$respuestasBD = [];

if (existeTabla($conexion, "respuestas")) {
    $stmtRespuestas = $conexion->prepare("SELECT * FROM respuestas WHERE pregunta_id = ? ORDER BY id ASC");

    if ($stmtRespuestas) {
        $stmtRespuestas->bind_param("i", $preguntaId);
        $stmtRespuestas->execute();

        $resultadoRespuestas = $stmtRespuestas->get_result();

        while ($filaRespuesta = $resultadoRespuestas->fetch_assoc()) {
            $respuestasBD[] = $filaRespuesta;
        }

        $stmtRespuestas->close();
    }
}

/*
--------------------------------------------------
Preparar valores del formulario
--------------------------------------------------
*/

if ($usaEstructuraNueva) {
    $valores = [
        "pregunta" => $preguntaBD["pregunta_texto"] ?? "",
        "imagen" => $preguntaBD["pregunta_imagen"] ?? "",
        "nivel" => (string)($preguntaBD["nivel_id"] ?? $niveles[0]["id"]),
        "respuesta1" => $preguntaBD["opcion1_texto"] ?? "",
        "respuesta2" => $preguntaBD["opcion2_texto"] ?? "",
        "respuesta3" => $preguntaBD["opcion3_texto"] ?? "",
        "respuesta4" => $preguntaBD["opcion4_texto"] ?? "",
        "imagen_respuesta1" => $preguntaBD["opcion1_imagen"] ?? "",
        "imagen_respuesta2" => $preguntaBD["opcion2_imagen"] ?? "",
        "imagen_respuesta3" => $preguntaBD["opcion3_imagen"] ?? "",
        "imagen_respuesta4" => $preguntaBD["opcion4_imagen"] ?? "",
        "correcta" => (string)(((int)($preguntaBD["respuesta_correcta"] ?? 0)) + 1)
    ];
} else {
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

    for ($i = 0; $i < 4; $i++) {
        if (isset($respuestasBD[$i]) && (int)$respuestasBD[$i]["correcta"] === 1) {
            $valores["correcta"] = (string)($i + 1);
            break;
        }
    }
}

if ((int)$valores["correcta"] < 1 || (int)$valores["correcta"] > 4) {
    $valores["correcta"] = "1";
}

/*
--------------------------------------------------
Guardar cambios
--------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($valores as $clave => $valor) {
        $valores[$clave] = trim($_POST[$clave] ?? "");
    }

    $preguntaTexto = $valores["pregunta"];
    $nivel = (int)$valores["nivel"];
    $correcta = (int)$valores["correcta"];

    try {
        if (!nivelEsValido($niveles, $nivel)) {
            throw new Exception("Debes seleccionar un nivel válido.");
        }

        $numeroNivel = obtenerNumeroNivel($niveles, $nivel);

        /*
        Se resuelven las imágenes.
        Si el administrador selecciona un archivo nuevo, se sube.
        Si no selecciona nada, se conserva la imagen anterior.
        */
        $imagen = obtenerImagenFinal("imagen", "archivo_imagen", $numeroNivel);

        $respuestasTexto = [
            $valores["respuesta1"],
            $valores["respuesta2"],
            $valores["respuesta3"],
            $valores["respuesta4"]
        ];

        $respuestasImagen = [
            obtenerImagenFinal("imagen_respuesta1", "archivo_imagen_respuesta1", $numeroNivel),
            obtenerImagenFinal("imagen_respuesta2", "archivo_imagen_respuesta2", $numeroNivel),
            obtenerImagenFinal("imagen_respuesta3", "archivo_imagen_respuesta3", $numeroNivel),
            obtenerImagenFinal("imagen_respuesta4", "archivo_imagen_respuesta4", $numeroNivel)
        ];

        $valores["imagen"] = $imagen;
        $valores["imagen_respuesta1"] = $respuestasImagen[0];
        $valores["imagen_respuesta2"] = $respuestasImagen[1];
        $valores["imagen_respuesta3"] = $respuestasImagen[2];
        $valores["imagen_respuesta4"] = $respuestasImagen[3];

        /*
        Validaciones
        */
        if ($preguntaTexto === "") {
            throw new Exception("Debes escribir el texto de la pregunta.");
        }

        if ($correcta < 1 || $correcta > 4) {
            throw new Exception("Debes seleccionar una respuesta correcta válida.");
        }

        for ($i = 0; $i < 4; $i++) {
            if ($respuestasTexto[$i] === "" && $respuestasImagen[$i] === "") {
                throw new Exception("Cada respuesta debe tener texto, imagen o ambas.");
            }
        }

        if ($respuestasTexto[$correcta - 1] === "" && $respuestasImagen[$correcta - 1] === "") {
            throw new Exception("La respuesta marcada como correcta no puede estar vacía.");
        }

        /*
        --------------------------------------------------
        Actualizar base de datos
        --------------------------------------------------
        */

        $conexion->begin_transaction();
        $transaccionIniciada = true;

        if ($usaEstructuraNueva) {
            /*
            Estructura usada por la app Android.
            */
            $respuestaCorrectaBD = $correcta - 1;

            $opcion1Texto = $respuestasTexto[0];
            $opcion1Imagen = $respuestasImagen[0];

            $opcion2Texto = $respuestasTexto[1];
            $opcion2Imagen = $respuestasImagen[1];

            $opcion3Texto = $respuestasTexto[2];
            $opcion3Imagen = $respuestasImagen[2];

            $opcion4Texto = $respuestasTexto[3];
            $opcion4Imagen = $respuestasImagen[3];

            $stmtActualizarPregunta = $conexion->prepare("
                UPDATE preguntas
                SET
                    nivel_id = ?,
                    pregunta_texto = ?,
                    pregunta_imagen = ?,
                    opcion1_texto = ?,
                    opcion1_imagen = ?,
                    opcion2_texto = ?,
                    opcion2_imagen = ?,
                    opcion3_texto = ?,
                    opcion3_imagen = ?,
                    opcion4_texto = ?,
                    opcion4_imagen = ?,
                    respuesta_correcta = ?
                WHERE id = ?
            ");

            if (!$stmtActualizarPregunta) {
                throw new Exception("Error al preparar la actualización de la pregunta: " . $conexion->error);
            }

            $stmtActualizarPregunta->bind_param(
                "issssssssssii",
                $nivel,
                $preguntaTexto,
                $imagen,
                $opcion1Texto,
                $opcion1Imagen,
                $opcion2Texto,
                $opcion2Imagen,
                $opcion3Texto,
                $opcion3Imagen,
                $opcion4Texto,
                $opcion4Imagen,
                $respuestaCorrectaBD,
                $preguntaId
            );
        } else {
            /*
            Estructura antigua.
            */
            $stmtActualizarPregunta = $conexion->prepare("
                UPDATE preguntas
                SET pregunta = ?, imagen = ?, nivel_id = ?
                WHERE id = ?
            ");

            if (!$stmtActualizarPregunta) {
                throw new Exception("Error al preparar la actualización de la pregunta: " . $conexion->error);
            }

            $stmtActualizarPregunta->bind_param(
                "ssii",
                $preguntaTexto,
                $imagen,
                $nivel,
                $preguntaId
            );
        }

        if (!$stmtActualizarPregunta->execute()) {
            throw new Exception("Error al actualizar la pregunta: " . $stmtActualizarPregunta->error);
        }

        $stmtActualizarPregunta->close();

        /*
        --------------------------------------------------
        Sincronizar tabla respuestas
        --------------------------------------------------

        Esto mantiene funcionando "Ver respuestas".
        Si ya existen 4 respuestas, se actualizan.
        Si faltan respuestas, se eliminan las antiguas
        y se crean 4 nuevas.
        */

        if (existeTabla($conexion, "respuestas")) {
            if (count($respuestasBD) === 4) {
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
                        throw new Exception("Error al preparar la actualización de una respuesta: " . $conexion->error);
                    }

                    $stmtUpdateRespuesta->bind_param(
                        "ssii",
                        $textoRespuesta,
                        $imagenRespuesta,
                        $esCorrecta,
                        $idRespuesta
                    );

                    if (!$stmtUpdateRespuesta->execute()) {
                        throw new Exception("Error al actualizar una respuesta: " . $stmtUpdateRespuesta->error);
                    }

                    $stmtUpdateRespuesta->close();
                }
            } else {
                $stmtBorrar = $conexion->prepare("DELETE FROM respuestas WHERE pregunta_id = ?");

                if (!$stmtBorrar) {
                    throw new Exception("Error al preparar el borrado de respuestas: " . $conexion->error);
                }

                $stmtBorrar->bind_param("i", $preguntaId);

                if (!$stmtBorrar->execute()) {
                    throw new Exception("Error al borrar respuestas antiguas: " . $stmtBorrar->error);
                }

                $stmtBorrar->close();

                $stmtInsertRespuesta = $conexion->prepare("
                    INSERT INTO respuestas (pregunta_id, texto, imagen, correcta)
                    VALUES (?, ?, ?, ?)
                ");

                if (!$stmtInsertRespuesta) {
                    throw new Exception("Error al preparar la creación de respuestas: " . $conexion->error);
                }

                for ($i = 0; $i < 4; $i++) {
                    $textoRespuesta = $respuestasTexto[$i];
                    $imagenRespuesta = $respuestasImagen[$i];
                    $esCorrecta = ($correcta === ($i + 1)) ? 1 : 0;

                    $stmtInsertRespuesta->bind_param(
                        "issi",
                        $preguntaId,
                        $textoRespuesta,
                        $imagenRespuesta,
                        $esCorrecta
                    );

                    if (!$stmtInsertRespuesta->execute()) {
                        throw new Exception("Error al crear una respuesta: " . $stmtInsertRespuesta->error);
                    }
                }

                $stmtInsertRespuesta->close();
            }
        }

        $conexion->commit();
        $transaccionIniciada = false;

        $mensaje = "Pregunta actualizada correctamente.";
    } catch (Exception $e) {
        if ($transaccionIniciada) {
            $conexion->rollback();
        }

        $error = $e->getMessage();
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

    .btn-volver:hover {
        opacity: 0.92;
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
    .campo input[type="file"],
    .campo textarea,
    .campo select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .campo textarea {
        resize: vertical;
        min-height: 90px;
    }

    .selector-imagen {
        display: grid;
        gap: 8px;
    }

    .imagen-preview {
        width: 230px;
        max-width: 100%;
        height: auto;
        display: none;
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

    .btn-guardar:hover {
        opacity: 0.92;
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
        line-height: 1.4;
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
    <div class="mensaje-ok"><?php echo escapar($mensaje); ?></div>
<?php endif; ?>

<?php if ($error !== ""): ?>
    <div class="mensaje-error"><?php echo escapar($error); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

    <div class="campo">
        <label for="pregunta">Texto de la pregunta</label>
        <textarea name="pregunta" id="pregunta" required><?php echo escapar($valores["pregunta"]); ?></textarea>
    </div>

    <div class="campo">
        <label>Imagen de la pregunta</label>

        <div class="selector-imagen">
            <input
                type="hidden"
                name="imagen"
                id="imagen"
                value="<?php echo escapar($valores["imagen"]); ?>"
            >

            <input
                type="file"
                name="archivo_imagen"
                accept="image/*"
                onchange="previsualizarArchivo(this, 'preview_imagen')"
            >
        </div>

        <img
            id="preview_imagen"
            class="imagen-preview"
            alt="Vista previa"
            src="<?php echo $valores["imagen"] !== "" ? "../img/" . escapar($valores["imagen"]) : ""; ?>"
            style="<?php echo $valores["imagen"] !== "" ? "display:block;" : "display:none;"; ?>"
        >

        <div class="ayuda-campo">
            Si no seleccionas una imagen nueva, se conserva la imagen actual.
        </div>
    </div>

    <div class="campo">
        <label for="nivel">Nivel</label>
        <select name="nivel" id="nivel">
            <?php foreach ($niveles as $nivelItem): ?>
                <option value="<?php echo (int)$nivelItem["id"]; ?>" <?php echo ((string)$nivelItem["id"] === $valores["nivel"]) ? "selected" : ""; ?>>
                    Nivel <?php echo escapar($nivelItem["numero"]); ?>
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
                    value="<?php echo escapar($valores["respuesta" . $i]); ?>"
                >
            </div>

            <div class="campo">
                <label>Imagen</label>

                <div class="selector-imagen">
                    <input
                        type="hidden"
                        name="imagen_respuesta<?php echo $i; ?>"
                        id="imagen_respuesta<?php echo $i; ?>"
                        value="<?php echo escapar($valores["imagen_respuesta" . $i]); ?>"
                    >

                    <input
                        type="file"
                        name="archivo_imagen_respuesta<?php echo $i; ?>"
                        accept="image/*"
                        onchange="previsualizarArchivo(this, 'preview_respuesta<?php echo $i; ?>')"
                    >
                </div>

                <img
                    id="preview_respuesta<?php echo $i; ?>"
                    class="imagen-preview"
                    alt="Vista previa respuesta <?php echo $i; ?>"
                    src="<?php echo $valores["imagen_respuesta" . $i] !== "" ? "../img/" . escapar($valores["imagen_respuesta" . $i]) : ""; ?>"
                    style="<?php echo $valores["imagen_respuesta" . $i] !== "" ? "display:block;" : "display:none;"; ?>"
                >
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

<script>
function previsualizarArchivo(input, previewId) {
    const preview = document.getElementById(previewId);

    if (!preview || !input.files || !input.files[0]) {
        return;
    }

    const archivo = input.files[0];
    const lector = new FileReader();

    lector.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = "block";
    };

    lector.readAsDataURL(archivo);
}
</script>

</body>
</html>

<?php
$conexion->close();
?>