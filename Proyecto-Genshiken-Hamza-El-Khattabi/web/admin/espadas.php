<?php
/*
--------------------------------------------------
Panel de administración - Gestión de espadas
--------------------------------------------------

Esta página muestra el catálogo de espadas del gacha.

Desde aquí el administrador puede:
- Ver todas las espadas registradas
- Crear una espada nueva
- Editar una espada existente
- Eliminar una espada

La tabla usada es:
- espadas

Las imágenes se guardan en:
- /img/gacha/
*/

require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

$mensajeOk = $_SESSION["mensaje_ok"] ?? "";
$mensajeError = $_SESSION["mensaje_error"] ?? "";

unset($_SESSION["mensaje_ok"], $_SESSION["mensaje_error"]);

$resultadoEspadas = $conexion->query("
    SELECT id, nombre, rareza, descripcion, imagen_url
    FROM espadas
    ORDER BY id ASC
");

if (!$resultadoEspadas) {
    die("Error al cargar las espadas: " . $conexion->error);
}

$totalEspadas = $resultadoEspadas->num_rows;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gacha / Espadas - Panel Admin</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .contenedor {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .acciones-superiores {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .titulo-seccion {
            color: #1f3c88;
            margin-bottom: 8px;
        }

        .texto-seccion {
            color: #555;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            margin-right: 6px;
        }

        .btn-volver {
            background: #1f3c88;
        }

        .btn-crear {
            background: #28a745;
        }

        .btn-editar {
            background: #f39c12;
        }

        .btn-eliminar {
            background: #e74c3c;
        }

        .btn:hover {
            opacity: 0.92;
        }

        .tabla-wrapper {
            overflow-x: auto;
            border-radius: 12px;
        }

        .tabla-espadas {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
        }

        .tabla-espadas th,
        .tabla-espadas td {
            padding: 14px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        .tabla-espadas th {
            background: #1f3c88;
            color: white;
        }

        .tabla-espadas tr:hover {
            background: #f7f9fc;
        }

        .imagen-mini {
            width: 95px;
            height: 70px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #fff;
            padding: 4px;
        }

        .descripcion-corta {
            max-width: 420px;
            line-height: 1.4;
            color: #444;
        }

        .rareza {
            font-weight: bold;
            padding: 6px 10px;
            border-radius: 999px;
            display: inline-block;
            font-size: 13px;
        }

        .rareza-comun {
            background: #e5e7eb;
            color: #111827;
        }

        .rareza-rara {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .rareza-epica {
            background: #ede9fe;
            color: #6d28d9;
        }

        .rareza-legendaria {
            background: #fef3c7;
            color: #92400e;
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

        .sin-imagen {
            color: #999;
            font-style: italic;
        }

        .acciones {
            white-space: nowrap;
        }
    </style>
</head>

<body class="dashboard-body">

<header class="topbar">
    <div>
        <h1>Gacha / Espadas</h1>
        <p>Gestión del catálogo de espadas del gachapon</p>
    </div>

    <a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="contenedor">

    <div class="acciones-superiores">
        <div>
            <h2 class="titulo-seccion">Listado de espadas</h2>
            <p class="texto-seccion">
                Aquí se gestionan las espadas que aparecerán en el sistema de gacha de la aplicación.
                Total registradas: <strong><?php echo $totalEspadas; ?></strong>
            </p>
        </div>

        <div>
            <a href="crear_espada.php" class="btn btn-crear">+ Nueva espada</a>
            <a href="dashboard.php" class="btn btn-volver">Volver al panel</a>
        </div>
    </div>

    <?php if ($mensajeOk !== ""): ?>
        <div class="mensaje-ok"><?php echo htmlspecialchars($mensajeOk, ENT_QUOTES, "UTF-8"); ?></div>
    <?php endif; ?>

    <?php if ($mensajeError !== ""): ?>
        <div class="mensaje-error"><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, "UTF-8"); ?></div>
    <?php endif; ?>

    <div class="tabla-wrapper">
        <table class="tabla-espadas">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Rareza</th>
                    <th>Descripción</th>
                    <th>URL imagen</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($resultadoEspadas->num_rows > 0): ?>
                    <?php while ($espada = $resultadoEspadas->fetch_assoc()): ?>
                        <?php
                            $rareza = strtoupper($espada["rareza"] ?? "");
                            $claseRareza = "rareza-comun";

                            if ($rareza === "RARA") {
                                $claseRareza = "rareza-rara";
                            } elseif ($rareza === "EPICA") {
                                $claseRareza = "rareza-epica";
                            } elseif ($rareza === "LEGENDARIA") {
                                $claseRareza = "rareza-legendaria";
                            }
                        ?>
                        <tr>
                            <td><?php echo (int)$espada["id"]; ?></td>

                            <td>
                                <?php if (!empty($espada["imagen_url"])): ?>
                                    <img
                                        src="<?php echo htmlspecialchars($espada["imagen_url"], ENT_QUOTES, "UTF-8"); ?>"
                                        alt="Imagen espada"
                                        class="imagen-mini"
                                    >
                                <?php else: ?>
                                    <span class="sin-imagen">Sin imagen</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?php echo htmlspecialchars($espada["nombre"], ENT_QUOTES, "UTF-8"); ?></strong>
                            </td>

                            <td>
                                <span class="rareza <?php echo $claseRareza; ?>">
                                    <?php echo htmlspecialchars($rareza, ENT_QUOTES, "UTF-8"); ?>
                                </span>
                            </td>

                            <td class="descripcion-corta">
                                <?php echo htmlspecialchars($espada["descripcion"], ENT_QUOTES, "UTF-8"); ?>
                            </td>

                            <td>
                                <small><?php echo htmlspecialchars($espada["imagen_url"], ENT_QUOTES, "UTF-8"); ?></small>
                            </td>

                            <td class="acciones">
                                <a href="editar_espada.php?id=<?php echo (int)$espada["id"]; ?>" class="btn btn-editar">
                                    Editar
                                </a>

                                <a
                                    href="eliminar_espada.php?id=<?php echo (int)$espada["id"]; ?>"
                                    class="btn btn-eliminar"
                                    onclick="return confirm('¿Seguro que quieres eliminar esta espada del catálogo?');"
                                >
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No hay espadas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>

<?php
$conexion->close();
?>