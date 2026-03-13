<?php
/*
--------------------------------------------------
Panel de administración - Crear nueva pregunta
--------------------------------------------------

Esta página permite al administrador añadir nuevas
preguntas al juego desde el panel de administración.

El formulario recoge:
- el texto de la pregunta
- la imagen asociada
- el nivel al que pertenece
- las cuatro posibles respuestas
- cuál de ellas es la correcta

Cuando se envía el formulario, la pregunta se guarda
en la tabla "preguntas" de la base de datos y las
respuestas se almacenan en la tabla "respuestas",
quedando vinculadas mediante el id de la pregunta.

De esta forma el contenido del juego puede gestionarse
directamente desde el panel web sin modificar el código.
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

$mensaje = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pregunta = trim($_POST["pregunta"]);
    $imagen = trim($_POST["imagen"]);
    $nivel = (int)$_POST["nivel"];

    $respuesta1 = trim($_POST["respuesta1"]);
    $respuesta2 = trim($_POST["respuesta2"]);
    $respuesta3 = trim($_POST["respuesta3"]);
    $respuesta4 = trim($_POST["respuesta4"]);

    $correcta = (int)$_POST["correcta"];

    if ($pregunta == "" || $respuesta1 == "" || $respuesta2 == "" || $respuesta3 == "" || $respuesta4 == "") {
        $error = "Debes rellenar todos los campos.";
    } else {

        $stmt = $conexion->prepare("INSERT INTO preguntas (pregunta, imagen, nivel_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $pregunta, $imagen, $nivel);
        $stmt->execute();

        $pregunta_id = $conexion->insert_id;

        $respuestas = [$respuesta1, $respuesta2, $respuesta3, $respuesta4];

        for ($i = 0; $i < 4; $i++) {

            $texto = $respuestas[$i];
            $esCorrecta = ($correcta == ($i + 1)) ? 1 : 0;

            $stmt2 = $conexion->prepare("INSERT INTO respuestas (pregunta_id, texto, correcta) VALUES (?, ?, ?)");
            $stmt2->bind_param("isi", $pregunta_id, $texto, $esCorrecta);
            $stmt2->execute();
        }

        $mensaje = "Pregunta creada correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva pregunta</title>
<link rel="stylesheet" href="style.css">
</head>

<body class="dashboard-body">

<header class="topbar">
<div>
<h1>Nueva pregunta</h1>
<p>Panel de administración</p>
</div>

<a class="logout-btn" href="logout.php">Cerrar sesión</a>
</header>

<div class="contenedor" style="padding:30px; max-width:900px; margin:auto;">

<a href="preguntas.php" class="btn-volver">Volver</a>

<?php if ($mensaje): ?>
<div class="mensaje-ok"><?php echo $mensaje; ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="mensaje-error"><?php echo $error; ?></div>
<?php endif; ?>

<form method="POST">

<h3>Pregunta</h3>

<textarea name="pregunta" required></textarea>

<h3>Imagen</h3>

<input type="text" name="imagen" placeholder="ejemplo: nivel1/espada.png">

<h3>Nivel</h3>

<select name="nivel">

<option value="1">Nivel 1</option>
<option value="2">Nivel 2</option>
<option value="3">Nivel 3</option>

</select>

<h3>Respuestas</h3>

<input type="text" name="respuesta1" placeholder="Respuesta 1" required>
<input type="text" name="respuesta2" placeholder="Respuesta 2" required>
<input type="text" name="respuesta3" placeholder="Respuesta 3" required>
<input type="text" name="respuesta4" placeholder="Respuesta 4" required>

<h3>Respuesta correcta</h3>

<select name="correcta">

<option value="1">Respuesta 1</option>
<option value="2">Respuesta 2</option>
<option value="3">Respuesta 3</option>
<option value="4">Respuesta 4</option>

</select>

<br><br>

<button type="submit" class="btn-guardar">Crear pregunta</button>

</form>

</div>

</body>
</html>