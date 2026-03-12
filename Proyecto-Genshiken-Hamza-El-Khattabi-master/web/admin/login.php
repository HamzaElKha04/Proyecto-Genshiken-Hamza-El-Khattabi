<?php
/*
--------------------------------------------------
Login del panel de administración
--------------------------------------------------

Esta página permite acceder al panel de
administración mediante usuario y contraseña.

Si las credenciales introducidas son correctas,
se crea la sesión del administrador y se redirige
al dashboard principal.
*/
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($usuario === $USUARIO_ADMIN && $password === $PASSWORD_ADMIN) {
        $_SESSION["admin_logueado"] = true;
        $_SESSION["admin_usuario"] = $usuario;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Genshi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-box">
        <h1>Panel Admin Genshi</h1>
        <p>Acceso restringido</p>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" required>

            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>