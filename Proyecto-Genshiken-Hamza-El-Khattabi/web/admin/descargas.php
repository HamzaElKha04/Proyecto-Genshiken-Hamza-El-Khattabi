<?php
/*
--------------------------------------------------
Panel de administración - Accesos a la app
--------------------------------------------------

Esta página muestra los accesos registrados desde
la aplicación Android.

IMPORTANTE:
Aunque internamente la tabla se llama "descargas",
esta sección realmente registra usos/inicios de sesión
de la app, no descargas reales de la APK.

Permite:
- ver todos los accesos registrados
- buscar por nombre de usuario o dispositivo
- consultar fecha, dispositivo y versión de la app
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
$likeBusqueda = "%" . $busqueda . "%";

/* Resúmenes superiores de la sección */
$totalAccesos = 0;
$totalUsuarios = 0;
$accesosEncontrados = 0;

/* Total de accesos registrados */
$resultadoTotal = $conexion->query("SELECT COUNT(*) AS total FROM descargas");
if ($resultadoTotal && $filaTotal = $resultadoTotal->fetch_assoc()) {
    $totalAccesos = (int)$filaTotal["total"];
}

/* Total de nombres de usuario distintos */
$resultadoUsuarios = $conexion->query("
    SELECT COUNT(DISTINCT nombre_usuario) AS total
    FROM descargas
    WHERE nombre_usuario IS NOT NULL AND nombre_usuario <> ''
");
if ($resultadoUsuarios && $filaUsuarios = $resultadoUsuarios->fetch_assoc()) {
    $totalUsuarios = (int)$filaUsuarios["total"];
}

/*
--------------------------------------------------
Consulta principal
--------------------------------------------------

Si hay búsqueda, filtra por nombre o dispositivo.
Si no hay búsqueda, muestra todos los accesos.
*/
if ($busqueda !== "") {
    $sql = "
        SELECT id, usuario_id, nombre_usuario, dispositivo, version_app, fecha_descarga
        FROM descargas
        WHERE nombre_usuario LIKE ? OR dispositivo LIKE ?
        ORDER BY fecha_descarga DESC, id DESC
    ";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al preparar la búsqueda: " . $conexion->error);
    }

    $stmt->bind_param("ss", $likeBusqueda, $likeBusqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = "
        SELECT id, usuario_id, nombre_usuario, dispositivo, version_app, fecha_descarga
        FROM descargas
        ORDER BY fecha_descarga DESC, id DESC
    ";

    $resultado = $conexion->query($sql);

    if (!$resultado) {
        die("Error al cargar los accesos: " . $conexion->error);
    }
}

/* Número de accesos que se van a mostrar */
$accesosEncontrados = $resultado ? $resultado->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesos a la app - Panel Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contenedor-descargas {
            max-width: 1250px;
            margin: 30px auto;
            padding: 20px;
        }

        .cabecera-descargas {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cabecera-descargas h1 {
            margin: 0;
            color: #1e3a8a;
        }

        .acciones {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-azul,
        .btn-verde,
        .btn-gris {
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-azul {
            background-color: #1e3a8a;
        }

        .btn-azul:hover {
            background-color: #163172;
        }

        .btn-verde {
            background-color: #28a745;
        }

        .btn-verde:hover {
            background-color: #218838;
        }

        .btn-gris {
            background-color: #6c757d;
        }

        .btn-gris:hover {
            background-color: #5a6268;
        }

        .resumen {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .card-resumen {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 18px;
        }

        .card-resumen h3 {
            margin: 0 0 8px 0;
            color: #1e3a8a;
            font-size: 18px;
        }

        .card-resumen p {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
            color: #222;
        }

        .buscador-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 18px;
            margin-bottom: 20px;
        }

        .buscador-box form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .buscador-box label {
            font-weight: bold;
            color: #1e3a8a;
            width: 100%;
        }

        .buscador-box input[type="text"] {
            flex: 1;
            min-width: 260px;
            padding: 11px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .info-busqueda {
            margin-top: 14px;
            color: #444;
            font-size: 15px;
        }

        .tabla-contenedor {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .tabla-scroll {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        th {
            background-color: #1e3a8a;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .sin-datos {
            padding: 20px;
            text-align: center;
            background: white;
            border-radius: 12px;
        }

        .nombre-usuario {
            font-weight: bold;
            color: #1e3a8a;
        }
    </style>
</head>
<body class="dashboard-body">

<div class="contenedor-descargas">
    <div class="cabecera-descargas">
        <h1>Accesos a la app</h1>

        <div class="acciones">
            <a href="dashboard.php" class="btn-azul">Volver al dashboard</a>
        </div>
    </div>

    <!-- Tarjetas resumen de la sección -->
    <div class="resumen">
        <div class="card-resumen">
            <h3>Total accesos</h3>
            <p><?php echo $totalAccesos; ?></p>
        </div>

        <div class="card-resumen">
            <h3>Usuarios distintos</h3>
            <p><?php echo $totalUsuarios; ?></p>
        </div>

        <div class="card-resumen">
            <h3>Resultados encontrados</h3>
            <p><?php echo $accesosEncontrados; ?></p>
        </div>
    </div>

    <!-- Buscador por nombre de usuario o dispositivo -->
    <div class="buscador-box">
        <form method="GET" action="descargas.php">
            <label for="busqueda">Buscar por usuario o dispositivo</label>
            <input
                type="text"
                name="busqueda"
                id="busqueda"
                placeholder="Ejemplo: Hamza o Samsung"
                value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8"); ?>"
            >
            <button type="submit" class="btn-verde">Buscar</button>
            <a href="descargas.php" class="btn-gris">Limpiar</a>
        </form>

        <?php if ($busqueda !== ""): ?>
            <div class="info-busqueda">
                Resultados para: <strong><?php echo htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8"); ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="tabla-contenedor">
            <div class="tabla-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Dispositivo</th>
                            <th>Versión app</th>
                            <th>Fecha de acceso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <?php
                                $nombreUsuario = trim($fila["nombre_usuario"] ?? "");
                                if ($nombreUsuario === "") {
                                    $nombreUsuario = "Anónimo";
                                }

                                $dispositivo = trim($fila["dispositivo"] ?? "");
                                if ($dispositivo === "") {
                                    $dispositivo = "No indicado";
                                }

                                $versionApp = trim($fila["version_app"] ?? "");
                                if ($versionApp === "") {
                                    $versionApp = "-";
                                }
                            ?>
                            <tr>
                                <td>#<?php echo (int)$fila["id"]; ?></td>
                                <td class="nombre-usuario"><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($dispositivo, ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($versionApp, ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($fila["fecha_descarga"], ENT_QUOTES, "UTF-8"); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="sin-datos">
            No hay accesos registrados.
        </div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
$conexion->close();
?>