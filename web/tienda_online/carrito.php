<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['actualizar'])) {
    $producto_id = intval($_POST['producto_id']);
    $cantidad = intval($_POST['cantidad']);
    
    if ($cantidad > 0 && $cantidad <= 10) {
        $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
    }
    header('Location: carrito.php');
    exit();
}

if (isset($_GET['eliminar'])) {
    $producto_id = intval($_GET['eliminar']);
    unset($_SESSION['carrito'][$producto_id]);
    header('Location: carrito.php');
    exit();
}

if (isset($_GET['vaciar'])) {
    $_SESSION['carrito'] = array();
    header('Location: carrito.php');
    exit();
}

$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : array();
$total = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - TechStore</title>
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
                    🛒 Carrito <span class="carrito-count"><?php echo contarItemsCarrito(); ?></span>
                </a>
                <a href="logout.php" class="btn btn-secondary">Salir</a>
            </div>
        </div>
    </header>

    <div class="container">
        <h1 style="margin-bottom: 30px;">🛒 Mi Carrito de Compras</h1>
        
        <?php if (empty($carrito)): ?>
            <div class="carrito-vacio">
                <h2>Tu carrito está vacío</h2>
                <p>Añade productos desde nuestra tienda</p>
                <a href="index.php" class="btn" style="display: inline-block; margin-top: 20px;">Ir a la tienda</a>
            </div>
        <?php else: ?>
            <div class="carrito-tabla">
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrito as $item): 
                            $subtotal = $item['precio'] * $item['cantidad'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td>
                                    <img src="img/<?php echo $item['codigo']; ?>.jpg" 
                                         alt="<?php echo $item['nombre']; ?>"
                                         onerror="this.src='img/no-image.jpg'">
                                </td>
                                <td>
                                    <strong><?php echo $item['nombre']; ?></strong><br>
                                    <small><?php echo $item['codigo']; ?></small>
                                </td>
                                <td><?php echo formatearPrecio($item['precio']); ?></td>
                                <td>
                                    <form method="POST" action="carrito.php" class="cantidad-control">
                                        <input type="hidden" name="producto_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" min="1" max="10">
                                        <button type="submit" name="actualizar">Actualizar</button>
                                    </form>
                                </td>
                                <td><strong><?php echo formatearPrecio($subtotal); ?></strong></td>
                                <td>
                                    <a href="carrito.php?eliminar=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('¿Eliminar este producto del carrito?')">
                                        Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="carrito-resumen">
                <h3>Resumen del Pedido</h3>
                <div class="resumen-linea">
                    <span>Subtotal:</span>
                    <span><?php echo formatearPrecio($total); ?></span>
                </div>
                <div class="resumen-linea">
                    <span>Envío:</span>
                    <span><?php echo $total >= 50 ? 'GRATIS' : formatearPrecio(5.99); ?></span>
                </div>
                <div class="resumen-linea resumen-total">
                    <span>TOTAL:</span>
                    <span><?php echo formatearPrecio($total >= 50 ? $total : $total + 5.99); ?></span>
                </div>
                
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <a href="index.php" class="btn btn-secondary" style="flex: 1;">Seguir comprando</a>
                    <a href="checkout.php" class="btn btn-success" style="flex: 1;">Proceder al pago</a>
                </div>
                
                <a href="carrito.php?vaciar=1" 
                   class="btn btn-danger" 
                   style="margin-top: 10px;"
                   onclick="return confirm('¿Vaciar todo el carrito?')">
                    Vaciar carrito
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
