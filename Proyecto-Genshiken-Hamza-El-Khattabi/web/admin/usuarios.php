<?php
require_once "config.php";

// Comprueba que el administrador haya iniciado sesión
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

// Conexión a la base de datos
$conexion = new mysqli("127.0.0.1", "root", "", "u842177649_genshiapp");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los usuarios registrados
$sql = "SELECT id, username, email, fecha_registro, email_verificado 
        FROM usuarios 
        ORDER BY id ASC";

$resultado = $conexion->query($sql);
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
                            <td><?php echo $fila["id"]; ?></td>
                            <td><?php echo htmlspecialchars($fila["username"]); ?></td>
                            <td><?php echo htmlspecialchars($fila["email"]); ?></td>
                            <td><?php echo $fila["fecha_registro"]; ?></td>
                            <td>
                                <!-- Muestra visualmente si el correo está verificado o no -->
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