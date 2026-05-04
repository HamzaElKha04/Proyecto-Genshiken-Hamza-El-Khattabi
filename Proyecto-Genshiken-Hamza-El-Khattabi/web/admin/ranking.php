<?php
/*
--------------------------------------------------
Panel de Ranking
--------------------------------------------------

Esta página muestra el ranking completo de partidas
jugadas en el panel admin.

Ahora permite:
- Ver todas las partidas jugadas
- Buscar por nombre de jugador
- Mostrar cuántas veces ha jugado ese jugador
- Ordenar por puntos, tiempo y fecha
- Mantener acceso al histórico y al reset

Criterios del ranking:
1. Más puntos
2. Menor tiempo
3. Fecha más reciente
*/

require_once "config.php";

/* Verificar sesión de administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/* Texto introducido en el buscador del ranking */
$busqueda = isset($_GET["busqueda"]) ? trim($_GET["busqueda"]) : "";
$busquedaLike = "%" . $busqueda . "%";

/* --------------------------------------------------
   Totales generales
   Se usan para las tarjetas resumen superiores
-------------------------------------------------- */
$sqlTotalPartidas = "SELECT COUNT(*) AS total FROM puntuaciones";
$resultadoTotalPartidas = $conexion->query($sqlTotalPartidas);
$totalPartidas = 0;

if ($resultadoTotalPartidas && $filaTotal = $resultadoTotalPartidas->fetch_assoc()) {
    $totalPartidas = (int)$filaTotal["total"];
}

/* Cuenta cuántos nombres distintos han jugado */
$sqlTotalJugadores = "SELECT COUNT(DISTINCT nombre) AS total FROM puntuaciones WHERE nombre IS NOT NULL AND nombre <> ''";
$resultadoTotalJugadores = $conexion->query($sqlTotalJugadores);
$totalJugadores = 0;

if ($resultadoTotalJugadores && $filaJugadores = $resultadoTotalJugadores->fetch_assoc()) {
    $totalJugadores = (int)$filaJugadores["total"];
}

/* --------------------------------------------------
   Conteo según búsqueda
   Si el admin busca un nombre, aquí se calcula
   cuántas veces ha jugado ese usuario
-------------------------------------------------- */
$vecesJugadas = 0;

if ($busqueda !== "") {
    $sqlConteoBusqueda = "SELECT COUNT(*) AS total 
                          FROM puntuaciones
                          WHERE nombre LIKE ?";
    $stmtConteo = $conexion->prepare($sqlConteoBusqueda);

    if (!$stmtConteo) {
        die("Error al preparar el conteo de búsqueda: " . $conexion->error);
    }

    $stmtConteo->bind_param("s", $busquedaLike);
    $stmtConteo->execute();
    $resultadoConteo = $stmtConteo->get_result();

    if ($resultadoConteo && $filaConteo = $resultadoConteo->fetch_assoc()) {
        $vecesJugadas = (int)$filaConteo["total"];
    }

    $stmtConteo->close();
}

/* --------------------------------------------------
   Consulta principal del ranking
   - Si hay búsqueda, filtra por nombre
   - Si no hay búsqueda, muestra todo el ranking
-------------------------------------------------- */
if ($busqueda !== "") {
    $sql = "SELECT id, nombre, puntos, tiempo, fecha
            FROM puntuaciones
            WHERE nombre LIKE ?
            ORDER BY puntos DESC, tiempo ASC, fecha DESC";

    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error al preparar la consulta del ranking: " . $conexion->error);
    }

    $stmt->bind_param("s", $busquedaLike);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $sql = "SELECT id, nombre, puntos, tiempo, fecha
            FROM puntuaciones
            ORDER BY puntos DESC, tiempo ASC, fecha DESC";

    $resultado = $conexion->query($sql);

    if (!$resultado) {
        die("Error al cargar el ranking: " . $conexion->error);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - Genshi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contenedor-ranking {
            max-width: 1250px;
            margin: 30px auto;
            padding: 20px;
        }

        .cabecera-ranking {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cabecera-ranking h1 {
            margin: 0;
            color: #1e3a8a;
        }

        .acciones {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-azul,
        .btn-rojo,
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

        .btn-rojo {
            background-color: #e74c3c;
        }

        .btn-rojo:hover {
            background-color: #c0392b;
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

        .resumen-ranking {
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
            border-collapse: collapse;
            min-width: 900px;
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

        /* Resalta visualmente los tres primeros puestos */
        .puesto-1 {
            font-weight: bold;
            color: #b8860b;
        }

        .puesto-2 {
            font-weight: bold;
            color: #666;
        }

        .puesto-3 {
            font-weight: bold;
            color: #8b5a2b;
        }

        .nombre-jugador {
            font-weight: bold;
            color: #1e3a8a;
        }

        .texto-secundario {
            color: #666;
            font-size: 13px;
        }
    </style>
</head>
<body class="dashboard-body">

<div class="contenedor-ranking">
    <div class="cabecera-ranking">
        <h1>Ranking actual completo</h1>

        <div class="acciones">
            <a href="dashboard.php" class="btn-azul">Volver al dashboard</a>
            <a href="ganadores.php" class="btn-azul">Ver histórico</a>
            <a href="reset_ranking.php" class="btn-rojo" onclick="return confirm('¿Seguro que quieres resetear todo el ranking actual?');">
                Resetear ranking
            </a>
        </div>
    </div>

    <!-- Tarjetas resumen con métricas generales del ranking -->
    <div class="resumen-ranking">
        <div class="card-resumen">
            <h3>Total de partidas</h3>
            <p><?php echo $totalPartidas; ?></p>
        </div>

        <div class="card-resumen">
            <h3>Jugadores distintos</h3>
            <p><?php echo $totalJugadores; ?></p>
        </div>

        <div class="card-resumen">
            <h3>Partidas encontradas</h3>
            <p><?php echo ($resultado) ? $resultado->num_rows : 0; ?></p>
        </div>

        <div class="card-resumen">
            <h3>Veces jugadas<?php echo ($busqueda !== "") ? " por " . htmlspecialchars($busqueda) : ""; ?></h3>
            <p><?php echo ($busqueda !== "") ? $vecesJugadas : $totalPartidas; ?></p>
        </div>
    </div>

    <!-- Buscador para filtrar el ranking por nombre de jugador -->
    <div class="buscador-box">
        <form method="GET" action="ranking.php">
            <label for="busqueda">Buscar por nombre del jugador</label>
            <input
                type="text"
                name="busqueda"
                id="busqueda"
                placeholder="Ejemplo: Hamza"
                value="<?php echo htmlspecialchars($busqueda); ?>"
            >
            <button type="submit" class="btn-verde">Buscar</button>
            <a href="ranking.php" class="btn-gris">Limpiar</a>
        </form>

        <?php if ($busqueda !== ""): ?>
            <div class="info-busqueda">
                Resultados para: <strong><?php echo htmlspecialchars($busqueda); ?></strong> |
                Veces jugadas: <strong><?php echo $vecesJugadas; ?></strong>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <div class="tabla-contenedor">
            <div class="tabla-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Jugador</th>
                            <th>Puntos</th>
                            <th>Tiempo</th>
                            <th>Fecha</th>
                            <th>ID partida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $posicion = 1; ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <?php
                                /* Asigna un estilo especial al top 3 */
                                $clasePosicion = "";

                                if ($posicion === 1) {
                                    $clasePosicion = "puesto-1";
                                } elseif ($posicion === 2) {
                                    $clasePosicion = "puesto-2";
                                } elseif ($posicion === 3) {
                                    $clasePosicion = "puesto-3";
                                }

                                /* Si no hay nombre guardado, muestra Anónimo */
                                $nombreJugador = trim($fila["nombre"] ?? "");
                                if ($nombreJugador === "") {
                                    $nombreJugador = "Anónimo";
                                }
                            ?>
                            <tr>
                                <td class="<?php echo $clasePosicion; ?>">
                                    <?php echo $posicion; ?>
                                </td>
                                <td>
                                    <span class="nombre-jugador"><?php echo htmlspecialchars($nombreJugador); ?></span>
                                </td>
                                <td>
                                    <?php echo (int)$fila["puntos"]; ?>
                                </td>
                                <td>
                                    <?php echo (int)$fila["tiempo"]; ?> s
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($fila["fecha"]); ?>
                                </td>
                                <td>
                                    <span class="texto-secundario">#<?php echo (int)$fila["id"]; ?></span>
                                </td>
                            </tr>
                            <?php $posicion++; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="sin-datos">
            No hay partidas registradas con ese criterio de búsqueda.
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