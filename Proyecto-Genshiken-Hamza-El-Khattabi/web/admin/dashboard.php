<?php
/*
--------------------------------------------------
Dashboard - Panel de administración
--------------------------------------------------

Esta página muestra el panel principal del administrador.

Incluye estadísticas generales del sistema como:
- Total de preguntas
- Total de respuestas
- Total de niveles
- Total de instalaciones detectadas de la app
- Total de espadas del gacha

Además permite acceder a las diferentes secciones
del panel de administración.
*/

require_once "config.php";

/* Solo accede el administrador logueado */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/*
--------------------------------------------------
Función auxiliar para comprobar si existe una tabla
--------------------------------------------------
*/
function existeTablaDashboard(mysqli $conexion, string $tabla): bool
{
    $tabla = $conexion->real_escape_string($tabla);
    $resultado = $conexion->query("SHOW TABLES LIKE '$tabla'");

    return $resultado && $resultado->num_rows > 0;
}

/* CONTADORES GENERALES DEL DASHBOARD */
$totalPreguntas = 0;
$totalRespuestas = 0;
$totalNiveles = 0;
$totalInstalaciones = 0;
$totalEspadas = 0;

if (existeTablaDashboard($conexion, "preguntas")) {
    $resultadoPreguntas = $conexion->query("SELECT COUNT(*) as total FROM preguntas");

    if ($resultadoPreguntas) {
        $totalPreguntas = (int)$resultadoPreguntas->fetch_assoc()["total"];
    }

    $resultadoNiveles = $conexion->query("
        SELECT COUNT(DISTINCT nivel_id) as total
        FROM preguntas
    ");

    if ($resultadoNiveles) {
        $totalNiveles = (int)$resultadoNiveles->fetch_assoc()["total"];
    }
}

if (existeTablaDashboard($conexion, "respuestas") && existeTablaDashboard($conexion, "preguntas")) {
    $resultadoRespuestas = $conexion->query("
        SELECT COUNT(*) as total
        FROM respuestas r
        INNER JOIN preguntas p ON r.pregunta_id = p.id
    ");

    if ($resultadoRespuestas) {
        $totalRespuestas = (int)$resultadoRespuestas->fetch_assoc()["total"];
    }
}

/*
--------------------------------------------------
Total de instalaciones únicas
--------------------------------------------------

Aunque la tabla se llama "descargas", se usa para
registrar instalaciones o primeros usos detectados
desde la app Android.

Se agrupa por usuario + dispositivo + versión para
no contar varias veces el mismo login.
*/
if (existeTablaDashboard($conexion, "descargas")) {
    $resultadoInstalaciones = $conexion->query("
        SELECT COUNT(*) AS total
        FROM (
            SELECT nombre_usuario, dispositivo, version_app
            FROM descargas
            GROUP BY nombre_usuario, dispositivo, version_app
        ) AS instalaciones_unicas
    ");

    if ($resultadoInstalaciones) {
        $filaInstalaciones = $resultadoInstalaciones->fetch_assoc();
        $totalInstalaciones = (int)$filaInstalaciones["total"];
    }
}

/*
--------------------------------------------------
Total de espadas del gacha
--------------------------------------------------
*/
if (existeTablaDashboard($conexion, "espadas")) {
    $resultadoEspadas = $conexion->query("SELECT COUNT(*) as total FROM espadas");

    if ($resultadoEspadas) {
        $totalEspadas = (int)$resultadoEspadas->fetch_assoc()["total"];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Genshi</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="dashboard-body">

<header class="topbar">
    <div>
        <h1>Panel de Administración</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["admin_usuario"], ENT_QUOTES, "UTF-8"); ?></p>
    </div>
    <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<main class="dashboard-container">

    <div class="card">
        <h2>Total preguntas</h2>
        <p style="font-size: 28px; font-weight: bold;">
            <?php echo $totalPreguntas; ?>
        </p>
    </div>

    <div class="card">
        <h2>Total respuestas</h2>
        <p style="font-size: 28px; font-weight: bold;">
            <?php echo $totalRespuestas; ?>
        </p>
    </div>

    <div class="card">
        <h2>Total niveles</h2>
        <p style="font-size: 28px; font-weight: bold;">
            <?php echo $totalNiveles; ?>
        </p>
    </div>

    <div class="card">
        <h2>Usuarios registrados</h2>
        <p>Sección para visualizar los usuarios de la aplicación.</p>
        <a href="usuarios.php" style="text-decoration:none;">
            <button>Ver usuarios</button>
        </a>
    </div>

    <div class="card">
        <h2>Preguntas y respuestas</h2>
        <p>Sección para gestionar las preguntas del juego.</p>
        <a href="preguntas.php" style="text-decoration:none;">
            <button>Ir a preguntas</button>
        </a>
    </div>

    <div class="card">
        <h2>Ranking</h2>
        <p>Sección para consultar el ranking de jugadores.</p>
        <a href="ranking.php" style="text-decoration:none;">
            <button>Ver ranking</button>
        </a>
    </div>

    <div class="card">
        <h2>Instalaciones de la app</h2>
        <p>Total registradas: <strong><?php echo $totalInstalaciones; ?></strong></p>
        <a href="descargas.php" style="text-decoration:none;">
            <button>Ver instalaciones</button>
        </a>
    </div>

    <div class="card">
        <h2>Gacha / Espadas</h2>
        <p>Total de espadas registradas: <strong><?php echo $totalEspadas; ?></strong></p>
        <p>Sección para gestionar el catálogo de espadas del gachapon.</p>
        <a href="espadas.php" style="text-decoration:none;">
            <button>Gestionar espadas</button>
        </a>
    </div>

</main>

</body>
</html>

<?php
$conexion->close();
?>