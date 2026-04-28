<?php

require "includes/config.php";
require "includes/funciones.php";

$error = "";
if (isset($_GET['sesion']) && $_GET['sesion'] === 'expirada') {
    $error = "Tu sesión ha expirado por inactividad. Por favor inicia sesión de nuevo.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $usuario = limpiarEntrada($_POST["usuario"]);
    $contrasena = $_POST["contrasena"];
    
    if (empty($usuario) || empty($contrasena))
    {
        $error = "Por favor completa todos los campos";
    } else {
        $sql = "SELECT * FROM clientes WHERE usuario = ?"; 
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt); 
        
        if ($row=mysqli_fetch_assoc($result)) {
            if (password_verify($contrasena, $row["contrasena"])) {
                $_SESSION["usuario_id"] = $row["id"];
                $_SESSION["usuario_nombre"] = $row["usuario"]; 
                header("Location: index.php"); 
                exit(); 
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
	}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TechStore</title>
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
        <h2>iniciar sesion</h2>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?> 
        
        <form method="POST" action="login.php"> 
            <div class="form-group"> 
	            <label for="usuario">Usuario</label> 
                <input type="text" id="usuario" name="usuario" required>
            </div> 
            
            <div class="form-group">
                <label for="contrasena">contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            
            <button type="submit" class="btn">iniciar sesion</button>
        </form>
        
        <p class="text-center-spaced">
            <a href="recuperar-contrasena.php">¿olvidaste tu contraseña?</a>
        </p>

        <p class="text-center-spaced">
            ¿no tienes cuenta? <a href="registro.php">registrate aqui</a>
        </p>
        
	</div>
</body>
</html>
