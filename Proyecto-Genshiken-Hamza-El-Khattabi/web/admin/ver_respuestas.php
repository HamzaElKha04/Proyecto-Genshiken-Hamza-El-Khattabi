<?php
/*
--------------------------------------------------
Panel de administración - Ver respuestas
--------------------------------------------------
Esta página permite consultar las respuestas
asociadas a una pregunta concreta del juego.

Muestra el enunciado, la imagen relacionada y
las diferentes respuestas posibles, indicando
cuál es la correcta.
*/
require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/* ID de la pregunta que se quiere consultar */
$preguntaId = isset($_GET["pregunta_id"]) ? (int)$_GET["pregunta_id"] : 0;

if ($preguntaId <= 0) {
    die("ID de pregunta no válido.");
}

/* Obtiene la pregunta y su nivel */
$stmtPregunta = $conexion->prepare("
    SELECT p.*, n.numero AS nivel_numero
    FROM preguntas p
    LEFT JOIN niveles n ON p.nivel_id = n.id
    WHERE p.id = ?
");

if (!$stmtPregunta) {
    die("Error al preparar la consulta de la pregunta: " . $conexion->error);
}

$stmtPregunta->bind_param("i", $preguntaId);
$stmtPregunta->execute();
$resultadoPregunta = $stmtPregunta->get_result();

if (!$resultadoPregunta || $resultadoPregunta->num_rows === 0) {
    die("Pregunta no encontrada.");
}

$pregunta = $resultadoPregunta->fetch_assoc();
$stmtPregunta->close();

/* Obtiene las respuestas asociadas a esa pregunta */
$stmtRespuestas = $conexion->prepare("
    SELECT *
    FROM respuestas
    WHERE pregunta_id = ?
    ORDER BY id ASC
");

if (!$stmtRespuestas) {
    die("Error al preparar la consulta de respuestas: " . $conexion->error);
}

$stmtRespuestas->bind_param("i", $preguntaId);
$stmtRespuestas->execute();
$resultadoRespuestas = $stmtRespuestas->get_result();

if (!$resultadoRespuestas) {
    die("Error al cargar respuestas: " . $conexion->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuestas de la pregunta</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contenedor {
            padding: 30px;
            max-width: 1100px;
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

        .imagen-pregunta {
            width: 260px;
            max-width: 100%;
            height: auto;
            display: block;
            margin: 15px 0 20px 0;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fff;
        }

        .nivel-badge {
            display: inline-block;
            margin-top: 12px;
            margin-bottom: 18px;
            padding: 8px 12px;
            background: #1f3c88;
            color: white;
            border-radius: 8px;
            font-size: 14px;
        }

        .lista-respuestas {
            margin-top: 20px;
            display: grid;
            gap: 14px;
        }

        .respuesta-item {
            padding: 14px;
            border-radius: 10px;
            background: #f4f6f9;
            border: 1px solid #ddd;
        }

        .correcta {
            background: #d4edda;
            border: 1px solid #28a745;
        }

        .etiqueta-correcta {
            display: inline-block;
            margin-top: 10px;
            font-weight: bold;
            color: #155724;
        }

        .imagen-respuesta {
            width: 170px;
            max-width: 100%;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            background: white;
        }

        .respuesta-titulo {
            font-weight: bold;
            color: #1f3c88;
            margin-bottom: 8px;
        }

        .sin-contenido {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body class="dashboard-body">

<header class="topbar">
    <div>
        <h1>Respuestas de la pregunta</h1>
        <p>Panel de administración</p>
    </div>

    <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="contenedor">
    <a href="preguntas.php" class="btn-volver">Volver a preguntas</a>

    <div class="caja">
        <h2><?php echo htmlspecialchars($pregunta["pregunta"], ENT_QUOTES, "UTF-8"); ?></h2>

        <span class="nivel-badge">
            <?php
            echo !empty($pregunta["nivel_numero"])
                ? "Nivel " . htmlspecialchars($pregunta["nivel_numero"], ENT_QUOTES, "UTF-8")
                : "Nivel " . (int)$pregunta["nivel_id"];
            ?>
        </span>

        <?php if (!empty($pregunta["imagen"])): ?>
            <img
                src="../img/<?php echo htmlspecialchars($pregunta["imagen"], ENT_QUOTES, "UTF-8"); ?>"
                alt="Imagen de la pregunta"
                class="imagen-pregunta"
            >
        <?php endif; ?>

        <div class="lista-respuestas">
            <?php $numeroRespuesta = 1; ?>
            <?php while ($respuesta = $resultadoRespuestas->fetch_assoc()): ?>
                <div class="respuesta-item <?php echo ((int)$respuesta["correcta"] === 1) ? 'correcta' : ''; ?>">
                    <div class="respuesta-titulo">Respuesta <?php echo $numeroRespuesta; ?></div>

                    <?php if (!empty($respuesta["texto"])): ?>
                        <div><?php echo htmlspecialchars($respuesta["texto"], ENT_QUOTES, "UTF-8"); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($respuesta["imagen"])): ?>
                        <img
                            src="../img/<?php echo htmlspecialchars($respuesta["imagen"], ENT_QUOTES, "UTF-8"); ?>"
                            alt="Respuesta <?php echo $numeroRespuesta; ?>"
                            class="imagen-respuesta"
                        >
                    <?php endif; ?>

                    <?php if (empty($respuesta["texto"]) && empty($respuesta["imagen"])): ?>
                        <div class="sin-contenido">Esta respuesta no tiene contenido.</div>
                    <?php endif; ?>

                    <?php if ((int)$respuesta["correcta"] === 1): ?>
                        <span class="etiqueta-correcta">Correcta</span>
                    <?php endif; ?>
                </div>
                <?php $numeroRespuesta++; ?>
            <?php endwhile; ?>
        </div>
    </div>
</div>

</body>
</html>
<?php
$stmtRespuestas->close();
$conexion->close();
?>