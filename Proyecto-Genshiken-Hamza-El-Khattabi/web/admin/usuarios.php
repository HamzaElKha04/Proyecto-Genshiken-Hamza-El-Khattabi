<?php
/*
--------------------------------------------------
Panel de administración - Usuarios registrados
--------------------------------------------------

Muestra los usuarios registrados en la aplicación.

Este archivo usa config.php para conectarse a la
base de datos, por lo que funciona en local y hosting.

Además, se excluye el usuario admin de pruebas para
que la tabla muestre solo usuarios reales de la app.
*/

require_once "config.php";

/* Solo accede el administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/*
--------------------------------------------------
Consulta de usuarios
--------------------------------------------------

Se oculta el usuario admin porque pertenece al panel,
no a un usuario real de la app.
*/
$sql = "
    SELECT id, username, email, fecha_registro, email_verificado
    FROM usuarios
    WHERE username <> 'admin'
    ORDER BY id ASC
";

$resultado = $conexion->query($sql);

if (!$resultado) {
    die("Error al cargar los usuarios: " . $conexion->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios registrados - Genshi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contenedor-usuarios {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        .cabecera-usuarios {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cabecera-usuarios h1 {
            margin: 0;
            color: #1e3a8a;
        }

        .btn-volver {
            background-color: #1e3a8a;
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-block;
        }

        .btn-volver:hover {
            background-color: #163172;
        }

        .tabla-contenedor {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
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

        .estado-si {
            color: green;
            font-weight: bold;
        }

        .estado-no {
            color: red;
            font-weight: bold;
        }

        .sin-datos {
            padding: 20px;
            text-align: center;
            background: white;
            border-radius: 12px;
        }
    </style>
</head>
<body class="dashboard-body">

<div class="contenedor-usuarios">
    <div class="cabecera-usuarios">
        <h1>Usuarios registrados</h1>
        <a href="dashboard.php" class="btn-volver">Volver al dashboard</a>
    </div>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Fecha de registro</th>
                        <th>Email verificado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($fila = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$fila["id"]; ?></td>
                            <td><?php echo htmlspecialchars($fila["username"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($fila["email"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td><?php echo htmlspecialchars($fila["fecha_registro"], ENT_QUOTES, "UTF-8"); ?></td>
                            <td>
                                <?php if ((int)$fila["email_verificado"] === 1): ?>
                                    <span class="estado-si">Sí</span>
                                <?php else: ?>
                                    <span class="estado-no">No</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="sin-datos">
            No hay usuarios registrados.
        </div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$conexion->close();
?>