<?php
/*
--------------------------------------------------
Histórico ranking mensual
--------------------------------------------------

Esta página muestra el histórico de rankings
guardados mes a mes.

Permite visualizar:
- posición visual del ranking
- nombre real del jugador
- puntuación
- tiempo empleado
- mes y año
- fecha real de la partida
- fecha de guardado del histórico

También incluye:
- filtros por mes y año
- buscador por nombre
- contador de partidas encontradas
- contador de veces jugadas por jugador
*/

require_once "config.php";

/* Verificar sesión de administrador */
if (!isset($_SESSION["admin_logueado"]) || $_SESSION["admin_logueado"] !== true) {
    header("Location: login.php");
    exit;
}

$conexion = conectarDB();

/* Array auxiliar para mostrar el nombre del mes en texto */
$meses = [
    1 => "Enero",
    2 => "Febrero",
    3 => "Marzo",
    4 => "Abril",
    5 => "Mayo",
    6 => "Junio",
    7 => "Julio",
    8 => "Agosto",
    9 => "Septiembre",
    10 => "Octubre",
    11 => "Noviembre",
    12 => "Diciembre"
];

/* Filtros recibidos desde el formulario del histórico */
$mesSeleccionado = isset($_GET["mes"]) ? (int) $_GET["mes"] : 0;
$anioSeleccionado = isset($_GET["anio"]) ? (int) $_GET["anio"] : 0;
$busqueda = trim($_GET["busqueda"] ?? "");

/* Obtiene los años disponibles para rellenar el desplegable */
$sqlAnios = "SELECT DISTINCT anio FROM ranking_mensual_historico ORDER BY anio DESC";
$resultadoAnios = $conexion->query($sqlAnios);

$aniosDisponibles = [];
if ($resultadoAnios) {
    while ($filaAnio = $resultadoAnios->fetch_assoc()) {
        $aniosDisponibles[] = (int)$filaAnio["anio"];
    }
}

/* Consulta principal del histórico */
$sql = "SELECT
            rmh.id,
            rmh.usuario_id,
            rmh.nombre,
            rmh.puntos,
            rmh.tiempo,
            rmh.posicion,
            rmh.mes,
            rmh.anio,
            rmh.fecha_partida,
            rmh.fecha_guardado
        FROM ranking_mensual_historico rmh
        WHERE 1=1";

$tipos = "";
$parametros = [];

/* Si el admin selecciona mes, se filtra por ese mes */
if ($mesSeleccionado > 0) {
    $sql .= " AND rmh.mes = ?";
    $tipos .= "i";
    $parametros[] = $mesSeleccionado;
}

/* Si el admin selecciona año, se filtra por ese año */
if ($anioSeleccionado > 0) {
    $sql .= " AND rmh.anio = ?";
    $tipos .= "i";
    $parametros[] = $anioSeleccionado;
}

/* Si se escribe un nombre, se filtra por jugador */
if ($busqueda !== "") {
    $sql .= " AND rmh.nombre LIKE ?";
    $tipos .= "s";
    $parametros[] = "%" . $busqueda . "%";
}

/*
--------------------------------------------------
Orden correcto de ranking:
1. Más puntos
2. Menor tiempo
3. Fecha de partida más antigua
4. Fecha de guardado más antigua
--------------------------------------------------
*/
$sql .= " ORDER BY 
            rmh.puntos DESC,
            rmh.tiempo ASC,
            CASE 
                WHEN rmh.fecha_partida IS NULL 
                     OR rmh.fecha_partida = '' 
                     OR rmh.fecha_partida = '0000-00-00 00:00:00'
                THEN 1
                ELSE 0
            END ASC,
            rmh.fecha_partida ASC,
            rmh.fecha_guardado ASC";

/* Se usa consulta preparada para aplicar filtros de forma segura */
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error al preparar la consulta del histórico: " . $conexion->error);
}

if (!empty($parametros)) {
    $stmt->bind_param($tipos, ...$parametros);
}

$stmt->execute();
$resultado = $stmt->get_result();

/* Resumen superior con métricas generales del histórico */
$totalHistorico = 0;
$partidasEncontradas = $resultado ? $resultado->num_rows : 0;
$vecesJugadas = 0;

$resultadoTotal = $conexion->query("SELECT COUNT(*) AS total FROM ranking_mensual_historico");
if ($resultadoTotal && $filaTotal = $resultadoTotal->fetch_assoc()) {
    $totalHistorico = (int)$filaTotal["total"];
}

/* Si se busca por nombre, aquí se calcula cuántas partidas tiene ese jugador en el histórico */
if ($busqueda !== "") {
    $sqlConteo = "SELECT COUNT(*) AS total
                  FROM ranking_mensual_historico
                  WHERE nombre LIKE ?";
    $stmtConteo = $conexion->prepare($sqlConteo);

    if ($stmtConteo) {
        $likeBusqueda = "%" . $busqueda . "%";
        $stmtConteo->bind_param("s", $likeBusqueda);
        $stmtConteo->execute();
        $resultadoConteo = $stmtConteo->get_result();

        if ($resultadoConteo && $filaConteo = $resultadoConteo->fetch_assoc()) {
            $vecesJugadas = (int)$filaConteo["total"];
        }

        $stmtConteo->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico ranking mensual - Genshi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contenedor-historico {
            max-width: 1350px;
            margin: 30px auto;
            padding: 20px;
        }

        .cabecera-historico {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .cabecera-historico h1 {
            margin: 0;
            color: #1e3a8a;
        }

        .acciones {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-azul,
        .btn-filtrar,
        .btn-limpiar {
            color: white;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-azul,
        .btn-filtrar {
            background-color: #1e3a8a;
        }

        .btn-azul:hover,
        .btn-filtrar:hover {
            background-color: #163172;
        }

        .btn-limpiar {
            background: #6c757d;
        }

        .btn-limpiar:hover {
            background: #5a6268;
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

        .filtros {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 18px;
            margin-bottom: 20px;
        }

        .filtros form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: end;
        }

        .filtro-campo {
            display: flex;
            flex-direction: column;
            min-width: 180px;
        }

        .filtro-campo label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #1e3a8a;
        }

        .filtro-campo select,
        .filtro-campo input {
            padding: 10px;
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
            min-width: 1100px;
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

        /* Resalta visualmente la primera posición mostrada */
        .posicion-ganador {
            font-weight: bold;
            color: #155724;
        }

        .nombre-jugador {
            font-weight: bold;
            color: #1e3a8a;
        }
    </style>
</head>
<body class="dashboard-body">

<div class="contenedor-historico">
    <div class="cabecera-historico">
        <h1>Histórico ranking mensual</h1>
        <div class="acciones">
            <a href="ranking.php" class="btn-azul">Volver al ranking</a>
            <a href="dashboard.php" class="btn-azul">Volver al dashboard</a>
        </div>
    </div>

    <!-- Tarjetas resumen del histórico -->
    <div class="resumen">
        <div class="card-resumen">
            <h3>Total histórico</h3>
            <p><?php echo $totalHistorico; ?></p>
        </div>

        <div class="card-resumen">
            <h3>Partidas encontradas</h3>
            <p><?php echo $partidasEncontradas; ?></p>
        </div>

        <div class="card-resumen">
            <h3>Mes seleccionado</h3>
            <p style="font-size:20px;">
                <?php echo ($mesSeleccionado > 0) ? ($meses[$mesSeleccionado] ?? $mesSeleccionado) : "Todos"; ?>
            </p>
        </div>

        <div class="card-resumen">
            <h3>Veces jugadas<?php echo ($busqueda !== "") ? " por " . htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8") : ""; ?></h3>
            <p><?php echo ($busqueda !== "") ? $vecesJugadas : "-"; ?></p>
        </div>
    </div>

    <!-- Formulario de filtros del histórico -->
    <div class="filtros">
        <form method="GET">
            <div class="filtro-campo">
                <label for="mes">Mes</label>
                <select name="mes" id="mes">
                    <option value="0">Todos</option>
                    <?php foreach ($meses as $numeroMes => $nombreMes): ?>
                        <option value="<?php echo $numeroMes; ?>" <?php echo ($mesSeleccionado === $numeroMes) ? "selected" : ""; ?>>
                            <?php echo $nombreMes; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtro-campo">
                <label for="anio">Año</label>
                <select name="anio" id="anio">
                    <option value="0">Todos</option>
                    <?php foreach ($aniosDisponibles as $anio): ?>
                        <option value="<?php echo $anio; ?>" <?php echo ($anioSeleccionado === $anio) ? "selected" : ""; ?>>
                            <?php echo $anio; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filtro-campo">
                <label for="busqueda">Jugador</label>
                <input
                    type="text"
                    name="busqueda"
                    id="busqueda"
                    placeholder="Ejemplo: Hamza"
                    value="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8"); ?>"
                >
            </div>

            <button type="submit" class="btn-filtrar">Filtrar</button>
            <a href="ganadores.php" class="btn-limpiar">Limpiar</a>
        </form>

        <?php if ($busqueda !== ""): ?>
            <div class="info-busqueda">
                Resultados para: <strong><?php echo htmlspecialchars($busqueda, ENT_QUOTES, "UTF-8"); ?></strong>
                | Veces jugadas en histórico: <strong><?php echo $vecesJugadas; ?></strong>
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
                            <th>Mes</th>
                            <th>Año</th>
                            <th>Fecha partida</th>
                            <th>Fecha guardado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $posicionMostrada = 1; ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <?php
                                /* Si no hay nombre, se muestra Anónimo */
                                $nombreJugador = trim($fila["nombre"] ?? "");
                                if ($nombreJugador === "") {
                                    $nombreJugador = "Anónimo";
                                }

                                /* Si no existe fecha real de partida, se muestra un guion */
                                $fechaPartida = $fila["fecha_partida"] ?? "";
                                if ($fechaPartida === null || $fechaPartida === "" || $fechaPartida === "0000-00-00 00:00:00") {
                                    $fechaPartida = "-";
                                }

                                /* Marca visualmente la primera posición mostrada */
                                $clasePosicion = "";
                                if ($posicionMostrada === 1) {
                                    $clasePosicion = "posicion-ganador";
                                }
                            ?>
                            <tr>
                                <td class="<?php echo $clasePosicion; ?>">
                                    <?php echo $posicionMostrada; ?>
                                </td>
                                <td class="nombre-jugador">
                                    <?php echo htmlspecialchars($nombreJugador, ENT_QUOTES, "UTF-8"); ?>
                                </td>
                                <td><?php echo (int)$fila["puntos"]; ?></td>
                                <td><?php echo (int)$fila["tiempo"]; ?> s</td>
                                <td><?php echo $meses[(int)$fila["mes"]] ?? $fila["mes"]; ?></td>
                                <td><?php echo (int)$fila["anio"]; ?></td>
                                <td><?php echo htmlspecialchars($fechaPartida, ENT_QUOTES, "UTF-8"); ?></td>
                                <td><?php echo htmlspecialchars($fila["fecha_guardado"], ENT_QUOTES, "UTF-8"); ?></td>
                            </tr>
                            <?php $posicionMostrada++; ?>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="sin-datos">
            No hay histórico de ranking mensual registrado con ese criterio.
        </div>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conexion->close();
?>