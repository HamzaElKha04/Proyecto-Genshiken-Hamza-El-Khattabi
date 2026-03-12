<?php
/*
--------------------------------------------------
Panel de administración - Página principal
--------------------------------------------------

Esta página es la pantalla inicial del panel de
administración.

Desde aquí el administrador puede acceder a las
secciones principales del sistema, como preguntas,
usuarios, ranking y descargas.

Antes de mostrar el contenido, se comprueba que
el usuario haya iniciado sesión correctamente.
*/
require_once "config.php";

if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
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
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["admin_usuario"]); ?></p>
        </div>
        <a class="logout-btn" href="logout.php">Cerrar sesión</a>
    </header>

    <main class="dashboard-container">
        <div class="card">
            <h2>Usuarios registrados</h2>
            <p>Sección preparada para visualizar los usuarios de la aplicación.</p>
            <button disabled>Próximamente</button>
        </div>

        <div class="card">
            <h2>Preguntas y respuestas</h2>
            <p>Sección preparada para listar y modificar preguntas del juego.</p>
            <button disabled>Próximamente</button>
        </div>

        <div class="card">
            <h2>Ranking</h2>
            <p>Sección preparada para consultar el ranking y reiniciarlo.</p>
            <button disabled>Próximamente</button>
        </div>

        <div class="card">
            <h2>Descargas</h2>
            <p>Sección preparada para mostrar el listado de descargas.</p>
            <button disabled>Próximamente</button>
        </div>
    </main>
</body>
</html>