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
- Total de accesos a la app

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

/* CONTADORES GENERALES DEL DASHBOARD */
$totalPreguntas = (int)$conexion->query("SELECT COUNT(*) as total FROM preguntas")->fetch_assoc()["total"];

$totalRespuestas = (int)$conexion->query("
    SELECT COUNT(*) as total
    FROM respuestas r
    INNER JOIN preguntas p ON r.pregunta_id = p.id
")->fetch_assoc()["total"];

$totalNiveles = (int)$conexion->query("
    SELECT COUNT(DISTINCT nivel_id) as total
    FROM preguntas
")->fetch_assoc()["total"];

/*
--------------------------------------------------
Total de accesos a la app
--------------------------------------------------

Aunque la tabla se llama "descargas", realmente se
usa para registrar accesos/uso de la app Android.
*/
$totalAccesos = 0;
$resultadoAccesos = $conexion->query("SELECT COUNT(*) as total FROM descargas");
if ($resultadoAccesos) {
    $filaAccesos = $resultadoAccesos->fetch_assoc();
    $totalAccesos = (int)$filaAccesos["total"];
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

    <!-- Tarjetas resumen principales del panel -->
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

    <!-- Acceso a usuarios registrados -->
    <div class="card">
        <h2>Usuarios registrados</h2>
        <p>Sección para visualizar los usuarios de la aplicación.</p>
        <a href="usuarios.php" style="text-decoration:none;">
            <button>Ver usuarios</button>
        </a>
    </div>

    <!-- Acceso a gestión de preguntas -->
    <div class="card">
        <h2>Preguntas y respuestas</h2>
        <p>Sección para gestionar las preguntas del juego.</p>
        <a href="preguntas.php" style="text-decoration:none;">
            <button>Ir a preguntas</button>
        </a>
    </div>

    <!-- Acceso al ranking actual -->
    <div class="card">
        <h2>Ranking</h2>
        <p>Sección para consultar el ranking.</p>
        <a href="ranking.php" style="text-decoration:none;">
            <button>Ver ranking</button>
        </a>
    </div>

    <!-- Acceso al registro de accesos de la app -->
    <div class="card">
        <h2>Accesos a la app</h2>
        <p>Total registrados: <strong><?php echo $totalAccesos; ?></strong></p>
        <a href="descargas.php" style="text-decoration:none;">
            <button>Ver accesos</button>
        </a>
    </div>

</main>

</body>
</html>

<?php
$conexion->close();
?>