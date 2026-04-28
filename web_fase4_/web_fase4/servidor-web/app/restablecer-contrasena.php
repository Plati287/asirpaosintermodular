<?php
require "includes/config.php";
require "includes/funciones.php";
if (estaLogueado()) { header("Location: index.php"); exit(); }
$token   = isset($_GET["token"]) ? trim($_GET["token"]) : "";
$mensaje = "";
$tipo    = "";
$token_valido = false;
$id_cliente   = null;
if ($token) {
    $ahora = date("Y-m-d H:i:s");
    $stmt  = mysqli_prepare($conn,
        "SELECT id_cliente FROM password_resets
         WHERE token=? AND usado=0 AND expira_at > ?");
    mysqli_stmt_bind_param($stmt, "ss", $token, $ahora);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $token_valido = true;
        $id_cliente   = $row["id_cliente"];
    }
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && $token_valido) {
    $nueva   = $_POST["contrasena"];
    $nueva2  = $_POST["contrasena2"];
    if (empty($nueva) || empty($nueva2)) {
        $mensaje = "Completa ambos campos.";
        $tipo    = "error";
    } elseif ($nueva !== $nueva2) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo    = "error";
    } elseif (strlen($nueva) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo    = "error";
    } else {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE clientes SET contrasena=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $hash, $id_cliente);
        mysqli_stmt_execute($stmt);
        $stmt2 = mysqli_prepare($conn, "UPDATE password_resets SET usado=1 WHERE token=?");
        mysqli_stmt_bind_param($stmt2, "s", $token);
        mysqli_stmt_execute($stmt2);
        $mensaje = "¡Contraseña actualizada correctamente! Redirigiendo...";
        $tipo    = "exito";
        header("refresh:2;url=login.php");
        $token_valido = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - TechStore</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>TechStore</h1>
            </div>
            <?php require_once "includes/nav_helper.php"; generarNav(); generarUserInfo(); ?>
        </div>
    </header>
    <div class="form-container">
        <h2>nueva contraseña</h2>
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (!$token || !$token_valido && !$mensaje): ?>
            <div class="mensaje error">
                El enlace no es válido o ha caducado.
            </div>
            <p class="text-center-spaced">
                <a href="recuperar-contrasena.php">solicitar un nuevo enlace</a>
            </p>
        <?php elseif ($token_valido): ?>
            <p style="text-align:center;color:#666;margin-bottom:20px;font-size:14px;">
                introduce tu nueva contraseña
            </p>
            <form method="POST" action="restablecer-contrasena.php?token=<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label for="contrasena">nueva contraseña *</label>
                    <input type="password" id="contrasena" name="contrasena" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="contrasena2">repetir contraseña *</label>
                    <input type="password" id="contrasena2" name="contrasena2" required minlength="6">
                </div>
                <button type="submit" class="btn">guardar contraseña</button>
            </form>
        <?php endif; ?>
        <p class="text-center-spaced">
            <a href="login.php">volver al inicio de sesión</a>
        </p>
    </div>
</body>
</html>