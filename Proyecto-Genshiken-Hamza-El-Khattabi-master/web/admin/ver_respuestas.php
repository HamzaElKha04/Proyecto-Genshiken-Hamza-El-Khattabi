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

Se utiliza como apoyo para revisar el contenido
del juego desde el panel de administración.
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

$sqlPregunta = "SELECT * FROM preguntas WHERE id = $preguntaId";
$resultadoPregunta = $conexion->query($sqlPregunta);

if (!$resultadoPregunta || $resultadoPregunta->num_rows === 0) {
    die("Pregunta no encontrada.");
}

$pregunta = $resultadoPregunta->fetch_assoc();

$sqlRespuestas = "SELECT * FROM respuestas WHERE pregunta_id = $preguntaId ORDER BY id ASC";
$resultadoRespuestas = $conexion->query($sqlRespuestas);

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

        .lista-respuestas {
            margin-top: 20px;
        }

        .respuesta-item {
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: #f4f6f9;
            border: 1px solid #ddd;
        }

        .correcta {
            background: #d4edda;
            border: 1px solid #28a745;
        }

        .etiqueta-correcta {
            font-weight: bold;
            color: #155724;
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
        <h2><?php echo htmlspecialchars($pregunta["pregunta"]); ?></h2>

        <?php if (!empty($pregunta["imagen"])): ?>
            <img
                src="../img/<?php echo htmlspecialchars($pregunta["imagen"]); ?>"
                alt="Imagen de la pregunta"
                class="imagen-pregunta"
            >
        <?php endif; ?>

        <div class="lista-respuestas">
            <?php while ($respuesta = $resultadoRespuestas->fetch_assoc()): ?>
                <div class="respuesta-item <?php echo $respuesta["correcta"] ? 'correcta' : ''; ?>">
                    <?php echo htmlspecialchars($respuesta["texto"]); ?>

                    <?php if ($respuesta["correcta"]): ?>
                        <span class="etiqueta-correcta"> - Correcta</span>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

</body>
</html>
<?php
$conexion->close();
?>