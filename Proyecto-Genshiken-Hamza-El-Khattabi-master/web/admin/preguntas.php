<?php
/*
--------------------------------------------------
Panel de administración - Listado de preguntas
--------------------------------------------------

Esta página muestra todas las preguntas del juego
almacenadas en la base de datos.

Se visualizan junto con su nivel, imagen asociada
y acciones disponibles, como ver respuestas o
editar la pregunta.

Esta sección forma parte del panel interno de
administración y solo es accesible con sesión.
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

$sql = "SELECT p.id, p.pregunta, p.imagen, p.nivel_id, n.numero AS nivel_numero
        FROM preguntas p
        LEFT JOIN niveles n ON p.nivel_id = n.id
        ORDER BY p.id ASC";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conexion->error);
}
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
        .btn-editar {
            display: inline-block;
            padding: 10px 16px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            margin-right: 6px;
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

        .tabla-preguntas {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
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
    </style>
</head>
<body class="dashboard-body">

<header class="topbar">
    <div>
        <h1>Gestión de Preguntas</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION["admin_usuario"]); ?></p>
    </div>

    <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="tabla-container">
    <div class="acciones-superiores">
        <div>
            <h2 class="titulo-seccion">Listado de preguntas</h2>
            <p class="texto-seccion">Aquí se muestran las preguntas registradas en la base de datos.</p>
        </div>

        <a href="dashboard.php" class="btn-volver">Volver al panel</a>
    </div>

    <table class="tabla-preguntas">
        <thead>
            <tr>
                <th>ID</th>
                <th>Pregunta</th>
                <th>Nivel</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $fila["id"]; ?></td>
                    <td><?php echo htmlspecialchars($fila["pregunta"]); ?></td>
                    <td>
                        <?php
                        echo !empty($fila["nivel_numero"])
                            ? "Nivel " . htmlspecialchars($fila["nivel_numero"])
                            : "Nivel " . $fila["nivel_id"];
                        ?>
                    </td>
                    <td>
                        <?php if (!empty($fila["imagen"])): ?>
                            <img
                                src="../img/<?php echo htmlspecialchars($fila["imagen"]); ?>"
                                alt="Imagen pregunta"
                                class="imagen-mini"
                            >
                        <?php else: ?>
                            <span class="sin-imagen">Sin imagen</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="ver_respuestas.php?pregunta_id=<?php echo $fila["id"]; ?>" class="btn-ver">
                            Ver respuestas
                        </a>
                        <a href="editar_pregunta.php?pregunta_id=<?php echo $fila["id"]; ?>" class="btn-editar">
                            Editar
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php
$conexion->close();
?>