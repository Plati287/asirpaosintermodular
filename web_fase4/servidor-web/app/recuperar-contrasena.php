<?php
require "includes/config.php";
require "includes/funciones.php";
require "includes/mailer.php";

if (estaLogueado()) { header("Location: index.php"); exit(); }

$mensaje = "";
$tipo    = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = limpiarEntrada($_POST["email"]);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Introduce un email válido.";
        $tipo    = "error";
    } else {
        // Buscar cliente con ese email
        $stmt = mysqli_prepare($conn, "SELECT id, usuario FROM clientes WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Siempre mostramos el mismo mensaje por seguridad
        $mensaje = "Si ese email está registrado, recibirás un enlace en breve.";
        $tipo    = "exito";

        if ($cliente = mysqli_fetch_assoc($result)) {
            // Generar token y guardar en BD
            $token   = bin2hex(random_bytes(32));
            $expira  = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Invalidar tokens anteriores del mismo usuario
            $stmt2 = mysqli_prepare($conn, "UPDATE password_resets SET usado=1 WHERE id_cliente=?");
            mysqli_stmt_bind_param($stmt2, "i", $cliente["id"]);
            mysqli_stmt_execute($stmt2);

            // Insertar nuevo token
            $stmt3 = mysqli_prepare($conn, "INSERT INTO password_resets (id_cliente, token, expira_at) VALUES (?,?,?)");
            mysqli_stmt_bind_param($stmt3, "iss", $cliente["id"], $token, $expira);
            mysqli_stmt_execute($stmt3);

            // Enviar email
            $enlace = SITE_URL . "/restablecer-contrasena.php?token=" . $token;
            $cuerpo = "
                <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;'>
                    <h2 style='color:#2C3E50;'>Recuperar contraseña — TechStore</h2>
                    <p>Hola <strong>" . htmlspecialchars($cliente["usuario"]) . "</strong>,</p>
                    <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                    <p>Haz clic en el botón para crear una nueva contraseña. El enlace caduca en <strong>1 hora</strong>.</p>
                    <p style='text-align:center;margin:30px 0;'>
                        <a href='$enlace'
                           style='background:#3498db;color:white;padding:12px 28px;border-radius:4px;text-decoration:none;font-size:15px;'>
                            restablecer contraseña
                        </a>
                    </p>
                    <p style='color:#999;font-size:12px;'>Si no solicitaste esto, ignora este correo. Tu contraseña no cambiará.</p>
                </div>";

            enviarEmail($email, $cliente["usuario"], "Recuperar contraseña - TechStore", $cuerpo);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - TechStore</title>
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
        <h2>recuperar contraseña</h2>
        <p style="text-align:center;color:#666;margin-bottom:20px;font-size:14px;">
            introduce tu email y te enviaremos un enlace para restablecer tu contraseña
        </p>

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($tipo !== "exito"): ?>
        <form method="POST" action="recuperar-contrasena.php">
            <div class="form-group">
                <label for="email">email</label>
                <input type="email" id="email" name="email" required
                       placeholder="tucorreo@email.com"
                       value="<?php echo isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : ""; ?>">
            </div>
            <button type="submit" class="btn">enviar enlace</button>
        </form>
        <?php endif; ?>

        <p class="text-center-spaced">
            <a href="login.php">volver al inicio de sesión</a>
        </p>
    </div>
</body>
</html>
