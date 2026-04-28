<?php
require "includes/config.php";
require "includes/funciones.php";

$error = "";
$exito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$usuario = limpiarEntrada($_POST["usuario"]);
    $email = limpiarEntrada($_POST["email"]);
    $contrasena = $_POST["contrasena"];
    $contrasena2 = $_POST["contrasena2"];
    $direccion = limpiarEntrada($_POST["direccion"]);
	$telefono = limpiarEntrada($_POST["telefono"]);
    $ciudad = limpiarEntrada($_POST["ciudad"]);
    
    if (empty($usuario) || empty($contrasena) || empty($contrasena2) || empty($email)) {
        $error = "Todos los campos obligatorios deben estar completos";
	} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El email no es válido";
    } elseif ($contrasena !== $contrasena2)
    {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($contrasena) < 6) {
	    $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Comprobar usuario Y email únicos
        $sql = "SELECT id FROM clientes WHERE usuario = ? OR email = ?";
        $stmt=mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $usuario, $email);
        mysqli_stmt_execute($stmt);
        $result=mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0)
        {
            $error = "El nombre de usuario o email ya está en uso";
        } else { 
            $contrasena_hash=password_hash($contrasena, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO clientes (usuario, email, contrasena, direccion, telefono, ciudad) VALUES (?, ?, ?, ?, ?, ?)"; 
            $stmt=mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $usuario, $email, $contrasena_hash, $direccion, $telefono, $ciudad);
            
            if (mysqli_stmt_execute($stmt)) {
                $exito="Registro exitoso. Ahora puedes iniciar sesión"; 
                header("refresh:2;url=login.php");
            } else {
                $error = "Error al registrar usuario";
            }
        } 
    } 
}
?>
<!DOCTYPE html> 
<html lang="es"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - TechStore</title>
	<link rel="stylesheet" href="css/registro.css"> 
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
        <h2>crear cuenta</h2>
        
        <?php if ($error): ?>
	        <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
	    
        <?php if ($exito): ?>
            <div class="mensaje exito"><?php echo $exito; ?></div>
	    <?php endif; ?>
        
        <form method="POST" action="registro.php"> 
            <div class="form-group">
                <label for="usuario">usuario *</label>
	            <input type="text" id="usuario" name="usuario" required 
                       value="<?php echo isset($_POST["usuario"]) ? htmlspecialchars($_POST["usuario"]) : ""; ?>">
            </div>

            <div class="form-group">
                <label for="email">email *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo isset($_POST["email"]) ? htmlspecialchars($_POST["email"]) : ""; ?>">
            </div>
            
            <div class="form-group">
                <label for="contrasena">contraseña *</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            
            <div class="form-group">
                <label for="contrasena2">repetir contraseña *</label>
                <input type="password" id="contrasena2" name="contrasena2" required>
            </div>
            
            <div class="form-group">
                <label for="direccion">direccion</label>
                <input type="text" id="direccion" name="direccion" 
                       value="<?php echo isset($_POST["direccion"]) ? htmlspecialchars($_POST["direccion"]) : ""; ?>">
            </div>
            
            <div class="form-group">
                <label for="telefono">telefono</label>
	            <input type="text" id="telefono" name="telefono" 
                       value="<?php echo isset($_POST["telefono"]) ? htmlspecialchars($_POST["telefono"]) : ""; ?>">
            </div>
            
            <div class="form-group">
                <label for="ciudad">ciudad</label>
	            <input type="text" id="ciudad" name="ciudad" 
                       value="<?php echo isset($_POST["ciudad"]) ? htmlspecialchars($_POST["ciudad"]) : ""; ?>">
            </div>
            
            <button type="submit" class="btn">registrarse</button>
        </form>
        
        <p class="text-center-spaced"> 
            ¿ya tienes cuenta? <a href="login.php">inicia sesion aqui</a>
        </p>
    </div>
</body>
</html>
