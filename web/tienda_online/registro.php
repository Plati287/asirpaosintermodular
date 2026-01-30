<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$error = '';
$exito = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = limpiarEntrada($_POST['usuario']);
    $contrasena = $_POST['contrasena'];
    $contrasena2 = $_POST['contrasena2'];
    $direccion = limpiarEntrada($_POST['direccion']);
    $telefono = limpiarEntrada($_POST['telefono']);
    $ciudad = limpiarEntrada($_POST['ciudad']);
    
    // Validaciones
    if (empty($usuario) || empty($contrasena) || empty($contrasena2)) {
        $error = 'Todos los campos obligatorios deben estar completos';
    } elseif ($contrasena !== $contrasena2) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($contrasena) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        // Verificar si el usuario ya existe
        $sql = "SELECT id FROM clientes WHERE usuario = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $error = 'El nombre de usuario ya está en uso';
        } else {
            // Encriptar contraseña
            $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $sql = "INSERT INTO clientes (usuario, contrasena, direccion, telefono, ciudad) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $usuario, $contrasena_hash, $direccion, $telefono, $ciudad);
            
            if (mysqli_stmt_execute($stmt)) {
                $exito = 'Registro exitoso. Ahora puedes iniciar sesión';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Error al registrar usuario';
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
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>🖥️ TechStore</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="form-container">
        <h2>Crear Cuenta</h2>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div class="mensaje exito"><?php echo $exito; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="registro.php">
            <div class="form-group">
                <label for="usuario">Usuario *</label>
                <input type="text" id="usuario" name="usuario" required 
                       value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="contrasena">Contraseña *</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            
            <div class="form-group">
                <label for="contrasena2">Repetir Contraseña *</label>
                <input type="password" id="contrasena2" name="contrasena2" required>
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion" 
                       value="<?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" 
                       value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" 
                       value="<?php echo isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : ''; ?>">
            </div>
            
            <button type="submit" class="btn">Registrarse</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </p>
    </div>
</body>
</html>
