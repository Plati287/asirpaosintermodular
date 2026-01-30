<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : array();

if (empty($carrito)) {
    header('Location: carrito.php');
    exit();
}

// Obtener datos del usuario
$sql = "SELECT * FROM clientes WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);

$error = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $direccion_envio = limpiarEntrada($_POST['direccion_envio']);
    
    if (empty($direccion_envio)) {
        $error = 'La dirección de envío es obligatoria';
    } else {
        // Iniciar transacción
        mysqli_begin_transaction($conn);
        
        try {
            // Crear pedido
            $fecha_envio = date('Y-m-d', strtotime('+3 days'));
            $sql = "INSERT INTO pedidos (id_cliente, estado, direccion_envio, fecha_envio) VALUES (?, 'Pendiente', ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iss", $_SESSION['usuario_id'], $direccion_envio, $fecha_envio);
            mysqli_stmt_execute($stmt);
            
            $pedido_id = mysqli_insert_id($conn);
            
            // Insertar líneas de pedido
            $sql = "INSERT INTO linea_pedido (id_pedido, id_producto, cantidad, precio_unidad) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            
            foreach ($carrito as $item) {
                mysqli_stmt_bind_param($stmt, "iiid", $pedido_id, $item['id'], $item['cantidad'], $item['precio']);
                mysqli_stmt_execute($stmt);
            }
            
            // Confirmar transacción
            mysqli_commit($conn);
            
            // Vaciar carrito
            $_SESSION['carrito'] = array();
            
            $exito = true;
            
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Error al procesar el pedido. Inténtalo de nuevo.';
        }
    }
}

$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}
$envio = $total >= 50 ? 0 : 5.99;
$total_final = $total + $envio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - TechStore</title>
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
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="mis-pedidos.php">Mis Pedidos</a></li>
                </ul>
            </nav>
            <div class="user-info">
                <span>Hola, <?php echo obtenerNombreUsuario(); ?></span>
                <a href="carrito.php" class="carrito-link">
                    🛒 Carrito <span class="carrito-count"><?php echo contarItemsCarrito(); ?></span>
                </a>
                <a href="logout.php" class="btn btn-secondary">Salir</a>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if ($exito): ?>
            <div class="form-container" style="max-width: 600px;">
                <h2>✅ ¡Pedido realizado con éxito!</h2>
                <div class="mensaje exito">
                    Tu pedido ha sido procesado correctamente
                </div>
                <p style="text-align: center; margin: 20px 0;">
                    Recibirás tu pedido en un plazo de 3-5 días laborables.
                </p>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <a href="mis-pedidos.php" class="btn" style="flex: 1;">Ver mis pedidos</a>
                    <a href="index.php" class="btn btn-secondary" style="flex: 1;">Volver a la tienda</a>
                </div>
            </div>
        <?php else: ?>
            <h1 style="margin-bottom: 30px;">Finalizar Compra</h1>
            
            <?php if ($error): ?>
                <div class="mensaje error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <div style="background: white; padding: 30px; border-radius: 8px;">
                    <h2>Información de Envío</h2>
                    
                    <form method="POST" action="checkout.php">
                        <div class="form-group">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" value="<?php echo $usuario['usuario']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="direccion_envio">Dirección de envío *</label>
                            <input type="text" id="direccion_envio" name="direccion_envio" 
                                   value="<?php echo $usuario['direccion']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" value="<?php echo $usuario['telefono']; ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" id="ciudad" value="<?php echo $usuario['ciudad']; ?>" readonly>
                        </div>
                        
                        <h3 style="margin-top: 30px; margin-bottom: 15px;">Método de Pago</h3>
                        <div style="padding: 15px; background-color: #ecf0f1; border-radius: 4px;">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="radio" name="metodo_pago" value="tarjeta" checked>
                                💳 Tarjeta de crédito/débito
                            </label>
                        </div>
                        
                        <div style="margin-top: 20px; display: flex; gap: 10px;">
                            <a href="carrito.php" class="btn btn-secondary" style="flex: 1;">Volver al carrito</a>
                            <button type="submit" class="btn btn-success" style="flex: 1;">Confirmar pedido</button>
                        </div>
                    </form>
                </div>
                
                <div>
                    <div style="background: white; padding: 20px; border-radius: 8px;">
                        <h3>Resumen del Pedido</h3>
                        
                        <div style="margin: 20px 0;">
                            <?php foreach ($carrito as $item): ?>
                                <div style="padding: 10px 0; border-bottom: 1px solid #ecf0f1;">
                                    <strong><?php echo $item['nombre']; ?></strong><br>
                                    <small><?php echo $item['cantidad']; ?> x <?php echo formatearPrecio($item['precio']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="resumen-linea">
                            <span>Subtotal:</span>
                            <span><?php echo formatearPrecio($total); ?></span>
                        </div>
                        <div class="resumen-linea">
                            <span>Envío:</span>
                            <span><?php echo $envio > 0 ? formatearPrecio($envio) : 'GRATIS'; ?></span>
                        </div>
                        <div class="resumen-linea resumen-total">
                            <span>TOTAL:</span>
                            <span><?php echo formatearPrecio($total_final); ?></span>
                        </div>
                        
                        <div style="margin-top: 20px; padding: 15px; background-color: #d5f4e6; border-radius: 4px;">
                            <small>✓ Envío estimado: 3-5 días laborables</small><br>
                            <small>✓ Garantía de devolución de 30 días</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
