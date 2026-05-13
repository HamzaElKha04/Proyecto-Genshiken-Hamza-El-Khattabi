<?php
/*
--------------------------------------------------
Panel de administración - Listado de preguntas
--------------------------------------------------

Muestra todas las preguntas del juego almacenadas
en la base de datos.

Este archivo está preparado para funcionar con dos
posibles estructuras de tabla:

1) preguntas.pregunta / preguntas.imagen
2) preguntas.pregunta_texto / preguntas.pregunta_imagen

Además, corrige la visualización de imágenes para
que funcione tanto en local como en hosting.
*/

require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/* Texto introducido en el buscador */
$busqueda = trim($_GET["busqueda"] ?? "");

/* Mensajes temporales */
$mensajeOk = $_SESSION["mensaje_ok"] ?? "";
$mensajeError = $_SESSION["mensaje_error"] ?? "";

unset($_SESSION["mensaje_ok"], $_SESSION["mensaje_error"]);

/*
--------------------------------------------------
Comprobar columnas de una tabla
--------------------------------------------------
*/
function existeColumna(mysqli $conexion, string $tabla, string $columna): bool
{
    $stmt = $conexion->prepare("SHOW COLUMNS FROM $tabla LIKE ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $columna);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $existe = $resultado && $resultado->num_rows > 0;

    $stmt->close();

    return $existe;
}

/*
--------------------------------------------------
Detectar columnas reales de preguntas
--------------------------------------------------
*/
$columnaPregunta = existeColumna($conexion, "preguntas", "pregunta")
    ? "pregunta"
    : "pregunta_texto";

$columnaImagen = existeColumna($conexion, "preguntas", "imagen")
    ? "imagen"
    : "pregunta_imagen";

/*
--------------------------------------------------
Resaltar coincidencia de búsqueda
--------------------------------------------------
*/
function resaltarCoincidencia(string $texto, string $busqueda): string
{
    if ($busqueda === "") {
        return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
    }

    $partes = preg_split(
        '/(' . preg_quote($busqueda, '/') . ')/iu',
        $texto,
        -1,
        PREG_SPLIT_DELIM_CAPTURE
    );

    if ($partes === false) {
        return htmlspecialchars($texto, ENT_QUOTES, "UTF-8");
    }

    $salida = "";

    foreach ($partes as $parte) {
        if ($parte === "") {
            continue;
        }

        if (mb_strtolower($parte, "UTF-8") === mb_strtolower($busqueda, "UTF-8")) {
            $salida .= "<mark>" . htmlspecialchars($parte, ENT_QUOTES, "UTF-8") . "</mark>";
        } else {
            $salida .= htmlspecialchars($parte, ENT_QUOTES, "UTF-8");
        }
    }

    return $salida;
}

/*
--------------------------------------------------
Preparar ruta de imagen para el panel admin
--------------------------------------------------

La imagen puede venir de varias formas:

- nivel1/q1.png
- img/nivel1/q1.png
- /WEB_genshi/img/nivel1/q1.png
- http://...
- https://...

Esta función evita que el panel construya rutas rotas.
*/
function prepararRutaImagenAdmin(string $rutaImagen): string
{
    $rutaImagen = trim($rutaImagen);

    if ($rutaImagen === "") {
        return "";
    }

    /* Si ya viene como URL completa */
    if (
        strpos($rutaImagen, "http://") === 0 ||
        strpos($rutaImagen, "https://") === 0
    ) {
        return $rutaImagen;
    }

    /* Si ya viene como ruta absoluta del proyecto */
    if (strpos($rutaImagen, "/") === 0) {
        return $rutaImagen;
    }

    /* Si viene empezando por img/ */
    if (strpos($rutaImagen, "img/") === 0) {
        return "../" . $rutaImagen;
    }

    /* Caso normal: nivel1/q1.png */
    return "../img/" . $rutaImagen;
}

/*
--------------------------------------------------
Consulta base
--------------------------------------------------

No dependemos de niveles.numero para evitar errores
si la tabla niveles tiene otra estructura.
*/
$sqlBase = "
    SELECT 
        p.id,
        p.$columnaPregunta AS pregunta,
        p.$columnaImagen AS imagen,
        p.nivel_id,
        COUNT(r.id) AS total_respuestas,
        SUM(CASE WHEN r.correcta = 1 THEN 1 ELSE 0 END) AS respuestas_correctas
    FROM preguntas p
    LEFT JOIN respuestas r ON r.pregunta_id = p.id
";

/* Si hay búsqueda, se filtra por texto de pregunta */
if ($busqueda !== "") {
    $sql = $sqlBase . "
        WHERE p.$columnaPregunta LIKE ?
        GROUP BY p.id, p.$columnaPregunta, p.$columnaImagen, p.nivel_id
        ORDER BY p.id ASC
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al preparar la búsqueda: " . $conexion->error);
    }

    $likeBusqueda = "%" . $busqueda . "%";
    $stmt->bind_param("s", $likeBusqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = $sqlBase . "
        GROUP BY p.id, p.$columnaPregunta, p.$columnaImagen, p.nivel_id
        ORDER BY p.id ASC
    ";

    $resultado = $conexion->query($sql);

    if (!$resultado) {
        die("Error en la consulta: " . $conexion->error);
    }
}

/* Número total de filas que se van a mostrar */
$totalResultados = $resultado ? $resultado->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas - Panel Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabla-container {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .acciones-superiores {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-volver,
        .btn-ver,
        .btn-editar,
        .btn-eliminar,
        .btn-buscar {
            display: inline-block;
            padding: 10px 16px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            margin-right: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-volver {
            background: #1f3c88;
        }

        .btn-ver {
            background: #39a2db;
        }

        .btn-editar {
            background: #f39c12;
        }

        .btn-eliminar {
            background: #e74c3c;
        }

        .btn-buscar {
            background: #28a745;
        }

        .btn-volver:hover,
        .btn-ver:hover,
        .btn-editar:hover,
        .btn-eliminar:hover,
        .btn-buscar:hover {
            opacity: 0.92;
        }

        .tabla-preguntas {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
        }

        .tabla-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        .tabla-preguntas th,
        .tabla-preguntas td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        .tabla-preguntas th {
            background: #1f3c88;
            color: white;
        }

        .tabla-preguntas tr:hover {
            background: #f7f9fc;
        }

        .imagen-mini {
            width: 90px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ccc;
            background: #fff;
        }

        .sin-imagen {
            color: #999;
            font-style: italic;
        }

        .titulo-seccion {
            margin-bottom: 10px;
            color: #1f3c88;
        }

        .texto-seccion {
            color: #555;
            margin-bottom: 20px;
        }

        .form-busqueda {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .input-busqueda {
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            min-width: 280px;
        }

        .texto-resultado {
            margin-bottom: 15px;
            color: #555;
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

        .estado-ok {
            color: #155724;
            font-weight: bold;
        }

        .estado-revisar {
            color: #b02a37;
            font-weight: bold;
        }

        .acciones-celda {
            white-space: nowrap;
        }

        mark {
            background: #fff3a3;
            padding: 1px 3px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="dashboard-body">

<header class="topbar">
    <div>
        <h1>Gestión de Preguntas</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["admin_usuario"], ENT_QUOTES, "UTF-8"); ?></p>
    </div>

    <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="tabla-container">
    <div class="acciones-superiores">
        <div>
            <h2 class="titulo-seccion">Listado de preguntas</h2>
            <p class="texto-seccion">Aquí se muestran las preguntas registradas en la base de datos.</p>
        </div>

        <div>
            <a href="crear_pregunta.php" class="btn-editar">+ Nueva pregunta</a>
            <a href="dashboard.php" class="btn-volver">Volver al panel</a>
        </div>
    </div>

    <?php if ($mensajeOk !== ""): ?>
        <div class="mensaje-ok"><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, "UTF-8"); ?></div>
    <?php endif; ?>

    <?php if ($mensajeError !== ""): ?>
        <div class="mensaje-error"><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, "UTF-8"); ?></div>
    <?php endif; ?>

    <form method="GET" class="form-busqueda">
        <input
            type="text"
            name="busqueda"
            class="input-busqueda"
            placeholder="Buscar por texto de pregunta..."
            value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8"); ?>"
        >
        <button type="submit" class="btn-buscar">Buscar</button>
        <a href="preguntas.php" class="btn-volver">Limpiar</a>
    </form>

    <p class="texto-resultado">
        <?php if ($busqueda !== ""): ?>
            Resultados para: <strong><?php echo htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8"); ?></strong>
            | Total encontrados: <strong><?php echo $totalResultados; ?></strong>
        <?php else: ?>
            Total de preguntas registradas: <strong><?php echo $totalResultados; ?></strong>
        <?php endif; ?>
    </p>

    <div class="tabla-wrapper">
        <table class="tabla-preguntas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pregunta</th>
                    <th>Nivel</th>
                    <th>Imagen</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <?php
                            $totalRespuestas = (int)($fila["total_respuestas"] ?? 0);
                            $respuestasCorrectas = (int)($fila["respuestas_correctas"] ?? 0);
                            $estadoCorrecto = ($totalRespuestas === 4 && $respuestasCorrectas === 1);

                            $rutaImagen = prepararRutaImagenAdmin($fila["imagen"] ?? "");
                        ?>
                        <tr>
                            <td><?php echo (int)$fila["id"]; ?></td>

                            <td><?php echo resaltarCoincidencia($fila["pregunta"] ?? "", $busqueda); ?></td>

                            <td>Nivel <?php echo (int)$fila["nivel_id"]; ?></td>

                            <td>
                                <?php if ($rutaImagen !== ""): ?>
                                    <img
                                        src="<?php echo htmlspecialchars($rutaImagen, ENT_QUOTES, "UTF-8"); ?>"
                                        alt="Imagen pregunta"
                                        class="imagen-mini"
                                    >
                                <?php else: ?>
                                    <span class="sin-imagen">Sin imagen</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($estadoCorrecto): ?>
                                    <span class="estado-ok">Correcta</span><br>
                                    <small><?php echo $totalRespuestas; ?> respuestas / <?php echo $respuestasCorrectas; ?> correcta</small>
                                <?php else: ?>
                                    <span class="estado-revisar">Revisar</span><br>
                                    <small><?php echo $totalRespuestas; ?> respuestas / <?php echo $respuestasCorrectas; ?> correctas</small>
                                <?php endif; ?>
                            </td>

                            <td class="acciones-celda">
                                <a href="ver_respuestas.php?pregunta_id=<?php echo (int)$fila["id"]; ?>" class="btn-ver">
                                    Ver respuestas
                                </a>

                                <a href="editar_pregunta.php?pregunta_id=<?php echo (int)$fila["id"]; ?>" class="btn-editar">
                                    Editar
                                </a>

                                <a href="eliminar_pregunta.php?pregunta_id=<?php echo (int)$fila["id"]; ?>"
                                   onclick="return confirm('¿Seguro que quieres eliminar esta pregunta?');"
                                   class="btn-eliminar">
                                   Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No se encontraron preguntas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
$conexion->close();
?>