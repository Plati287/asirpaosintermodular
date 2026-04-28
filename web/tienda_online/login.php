<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = limpiarEntrada($_POST['usuario']);
    $contrasena = $_POST['contrasena'];
    
    if (empty($usuario) || empty($contrasena)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $sql = "SELECT * FROM clientes WHERE usuario = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($contrasena, $row['contrasena'])) {
                $_SESSION['usuario_id'] = $row['id'];
                $_SESSION['usuario_nombre'] = $row['usuario'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
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
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>TechStore</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="registro.php">Registrarse</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="form-container">
        <h2>Iniciar Sesión</h2>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            
            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </p>
        
        <div style="margin-top: 30px; padding: 15px; background-color: #ecf0f1; border-radius: 4px;">
            <h4>Usuario de prueba:</h4>
            <p><strong>Usuario:</strong> admin</p>
            <p><strong>Contraseña:</strong> admin123</p>
        </div>
    </div>
</body>
</html>
