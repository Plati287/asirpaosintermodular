<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

$sql = "SELECT * FROM pedidos WHERE id_cliente = ? ORDER BY id_pedido DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - TechStore</title>
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
                    <li><a href="productos.php">Productos</a></li>
                    <li><a href="mis-pedidos.php">Mis Pedidos</a></li>
                </ul>
            </nav>
            <div class="user-info">
                <span>Hola, <?php echo obtenerNombreUsuario(); ?></span>
                <a href="carrito.php" class="carrito-link">
                    Carrito <span class="carrito-count"><?php echo contarItemsCarrito(); ?></span>
                </a>
                <a href="logout.php" class="btn btn-secondary">Salir</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="margin-bottom: 30px;">📦 Mis Pedidos</h1>
        
        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="carrito-vacio">
                <h2>No tienes pedidos todavía</h2>
                <p>Cuando realices tu primera compra, aparecerá aquí</p>
                <a href="index.php" class="btn" style="display: inline-block; margin-top: 20px;">Ir a la tienda</a>
            </div>
        <?php else: ?>
            <div class="pedidos-lista">
                <?php while ($pedido = mysqli_fetch_assoc($result)): 
                    $sql_productos = "SELECT lp.*, p.nombre_producto, p.codigo_producto 
                                     FROM linea_pedido lp 
                                     JOIN productos p ON lp.id_producto = p.id 
                                     WHERE lp.id_pedido = ?";
                    $stmt_productos = mysqli_prepare($conn, $sql_productos);
                    mysqli_stmt_bind_param($stmt_productos, "i", $pedido['id_pedido']);
                    mysqli_stmt_execute($stmt_productos);
                    $productos = mysqli_stmt_get_result($stmt_productos);
                    
                    $total_pedido = 0;
                    $productos_array = array();
                    while ($prod = mysqli_fetch_assoc($productos)) {
                        $productos_array[] = $prod;
                        $total_pedido += $prod['precio_unidad'] * $prod['cantidad'];
                    }
                    
                    $estado_class = 'estado-pendiente';
                    if ($pedido['estado'] == 'En tránsito') {
                        $estado_class = 'estado-entransito';
                    } elseif ($pedido['estado'] == 'Entregado') {
                        $estado_class = 'estado-entregado';
                    }
                ?>
                    <div class="pedido-item">
                        <div class="pedido-header">
                            <div>
                                <h3>Pedido #<?php echo $pedido['id_pedido']; ?></h3>
                                <p>Fecha de envío: <?php echo date('d/m/Y', strtotime($pedido['fecha_envio'])); ?></p>
                            </div>
                            <div>
                                <span class="estado-badge <?php echo $estado_class; ?>">
                                    <?php echo $pedido['estado']; ?>
                                </span>
                                <p style="margin-top: 10px;"><strong>Total: <?php echo formatearPrecio($total_pedido); ?></strong></p>
                            </div>
                        </div>
                        
                        <div style="padding: 15px; background-color: #f8f9fa; border-radius: 4px; margin-bottom: 15px;">
                            <strong>📍 Dirección de envío:</strong><br>
                            <?php echo $pedido['direccion_envio']; ?>
                        </div>
                        
                        <div class="pedido-productos">
                            <h4>Productos:</h4>
                            <?php foreach ($productos_array as $prod): ?>
                                <div class="pedido-producto">
                                    <strong><?php echo $prod['nombre_producto']; ?></strong><br>
                                    <small>Código: <?php echo $prod['codigo_producto']; ?> | 
                                           Cantidad: <?php echo $prod['cantidad']; ?> | 
                                           Precio: <?php echo formatearPrecio($prod['precio_unidad']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($pedido['estado'] == 'Pendiente'): ?>
                            <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border-radius: 4px;">
                                Tu pedido está siendo preparado para el envío
                            </div>
                        <?php elseif ($pedido['estado'] == 'En tránsito'): ?>
                            <div style="margin-top: 15px; padding: 10px; background-color: #cfe2ff; border-radius: 4px;">
                                Tu pedido está en camino. Llegará pronto.
                            </div>
                        <?php else: ?>
                            <div style="margin-top: 15px; padding: 10px; background-color: #d1e7dd; border-radius: 4px;">
                                Pedido entregado con éxito
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
