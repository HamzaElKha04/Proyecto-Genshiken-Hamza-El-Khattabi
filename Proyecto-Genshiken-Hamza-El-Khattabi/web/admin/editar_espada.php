<?php
/*
--------------------------------------------------
Panel de administración - Editar espada
--------------------------------------------------

Permite editar una espada existente del catálogo
del gacha.

Se puede modificar:
- Nombre
- Rareza
- Descripción
- Imagen

Si no se selecciona una imagen nueva, se conserva
la imagen actual.
*/

require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

$URL_BASE_GACHA = "http://www.shopkatanas.com/WEB_genshi/img/gacha/";

$espadaId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($espadaId <= 0) {
    die("ID de espada no válido.");
}

$mensaje = "";
$error = "";

function escapar($texto)
{
    return htmlspecialchars((string)$texto, ENT_QUOTES, "UTF-8");
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
        $nombreSinExtension = "espada";
    }

    return $nombreSinExtension . "." . $extension;
}

function subirImagenGacha($campoArchivo, $urlBaseGacha)
{
    if (!isset($_FILES[$campoArchivo]) || $_FILES[$campoArchivo]["error"] === UPLOAD_ERR_NO_FILE) {
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

    $directorioDestino = __DIR__ . "/../img/gacha";

    if (!is_dir($directorioDestino)) {
        if (!mkdir($directorioDestino, 0755, true)) {
            throw new Exception("No se pudo crear la carpeta img/gacha.");
        }
    }

    $nombreArchivo = limpiarNombreArchivo($_FILES[$campoArchivo]["name"]);
    $rutaDestino = $directorioDestino . "/" . $nombreArchivo;

    if (file_exists($rutaDestino)) {
        $nombreSinExtension = pathinfo($nombreArchivo, PATHINFO_FILENAME);
        $nombreArchivo = $nombreSinExtension . "_" . date("Ymd_His") . "." . $extension;
        $rutaDestino = $directorioDestino . "/" . $nombreArchivo;
    }

    if (!move_uploaded_file($_FILES[$campoArchivo]["tmp_name"], $rutaDestino)) {
        throw new Exception("No se pudo guardar la imagen subida.");
    }

    return $urlBaseGacha . $nombreArchivo;
}

/*
--------------------------------------------------
Cargar espada actual
--------------------------------------------------
*/

$stmt = $conexion->prepare("
    SELECT id, nombre, rareza, descripcion, imagen_url
    FROM espadas
    WHERE id = ?
");

if (!$stmt) {
    die("Error al preparar la consulta: " . $conexion->error);
}

$stmt->bind_param("i", $espadaId);
$stmt->execute();

$resultado = $stmt->get_result();

if (!$resultado || $resultado->num_rows === 0) {
    die("Espada no encontrada.");
}

$espada = $resultado->fetch_assoc();
$stmt->close();

$valores = [
    "nombre" => $espada["nombre"],
    "rareza" => strtoupper($espada["rareza"]),
    "descripcion" => $espada["descripcion"],
    "imagen_url" => $espada["imagen_url"]
];

/*
--------------------------------------------------
Guardar cambios
--------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $valores["nombre"] = trim($_POST["nombre"] ?? "");
    $valores["rareza"] = strtoupper(trim($_POST["rareza"] ?? "COMUN"));
    $valores["descripcion"] = trim($_POST["descripcion"] ?? "");
    $valores["imagen_url"] = trim($_POST["imagen_url"] ?? "");

    try {
        $rarezasValidas = ["COMUN", "RARA", "EPICA", "LEGENDARIA"];

        if ($valores["nombre"] === "") {
            throw new Exception("Debes escribir el nombre de la espada.");
        }

        if (!in_array($valores["rareza"], $rarezasValidas, true)) {
            throw new Exception("Debes seleccionar una rareza válida.");
        }

        if ($valores["descripcion"] === "") {
            throw new Exception("Debes escribir una descripción.");
        }

        $nuevaImagen = subirImagenGacha("imagen", $URL_BASE_GACHA);

        if ($nuevaImagen !== "") {
            $valores["imagen_url"] = $nuevaImagen;
        }

        if ($valores["imagen_url"] === "") {
            throw new Exception("La espada debe tener una imagen.");
        }

        $stmtActualizar = $conexion->prepare("
            UPDATE espadas
            SET nombre = ?, rareza = ?, descripcion = ?, imagen_url = ?
            WHERE id = ?
        ");

        if (!$stmtActualizar) {
            throw new Exception("Error al preparar la actualización: " . $conexion->error);
        }

        $stmtActualizar->bind_param(
            "ssssi",
            $valores["nombre"],
            $valores["rareza"],
            $valores["descripcion"],
            $valores["imagen_url"],
            $espadaId
        );

        if (!$stmtActualizar->execute()) {
            throw new Exception("Error al actualizar la espada: " . $stmtActualizar->error);
        }

        $stmtActualizar->close();

        $_SESSION["mensaje_ok"] = "Espada actualizada correctamente.";
        header("Location: espadas.php");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar espada - Panel Admin</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .contenedor {
            padding: 30px;
            max-width: 850px;
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
            min-height: 120px;
            resize: vertical;
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

        .mensaje-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .ayuda-campo {
            color: #666;
            font-size: 13px;
            margin-top: 4px;
            line-height: 1.4;
        }

        .imagen-preview {
            width: 220px;
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fff;
            padding: 4px;
        }
    </style>
</head>

<body class="dashboard-body">

<header class="topbar">
    <div>
        <h1>Editar espada</h1>
        <p>Modificar espada del catálogo del gacha</p>
    </div>

    <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="contenedor">

    <a href="espadas.php" class="btn-volver">Volver a espadas</a>

    <div class="caja">

        <?php if ($error !== ""): ?>
            <div class="mensaje-error"><?php echo escapar($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="campo">
                <label for="nombre">Nombre de la espada</label>
                <input
                    type="text"
                    name="nombre"
                    id="nombre"
                    value="<?php echo escapar($valores["nombre"]); ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="rareza">Rareza</label>
                <select name="rareza" id="rareza">
                    <option value="COMUN" <?php echo $valores["rareza"] === "COMUN" ? "selected" : ""; ?>>COMUN</option>
                    <option value="RARA" <?php echo $valores["rareza"] === "RARA" ? "selected" : ""; ?>>RARA</option>
                    <option value="EPICA" <?php echo $valores["rareza"] === "EPICA" ? "selected" : ""; ?>>EPICA</option>
                    <option value="LEGENDARIA" <?php echo $valores["rareza"] === "LEGENDARIA" ? "selected" : ""; ?>>LEGENDARIA</option>
                </select>
            </div>

            <div class="campo">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" id="descripcion" required><?php echo escapar($valores["descripcion"]); ?></textarea>
            </div>

            <div class="campo">
                <label>Imagen actual</label>

                <input
                    type="hidden"
                    name="imagen_url"
                    value="<?php echo escapar($valores["imagen_url"]); ?>"
                >

                <?php if ($valores["imagen_url"] !== ""): ?>
                    <img
                        src="<?php echo escapar($valores["imagen_url"]); ?>"
                        alt="Imagen actual"
                        class="imagen-preview"
                        id="preview_imagen"
                    >
                <?php else: ?>
                    <img
                        alt="Vista previa"
                        class="imagen-preview"
                        id="preview_imagen"
                        style="display:none;"
                    >
                <?php endif; ?>
            </div>

            <div class="campo">
                <label for="imagen">Cambiar imagen</label>
                <input
                    type="file"
                    name="imagen"
                    id="imagen"
                    accept="image/*"
                    onchange="previsualizarArchivo(this, 'preview_imagen')"
                >

                <div class="ayuda-campo">
                    Si no seleccionas una imagen nueva, se conserva la imagen actual.
                </div>
            </div>

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