<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$producto_id = intval($_GET['id']);

$sql = "SELECT p.*, c.categoria 
        FROM productos p 
        LEFT JOIN categoria c ON p.id_categoria = c.id 
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $producto_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

$producto = mysqli_fetch_assoc($result);

$precios = [
    1 => 2499.99, 2 => 3999.99, 3 => 899.99, 4 => 1899.99, 5 => 2999.99,
    6 => 2199.99, 7 => 899.99, 8 => 1099.99, 9 => 449.99, 10 => 599.99,
    11 => 299.99, 12 => 399.99, 13 => 189.99, 14 => 279.99, 15 => 189.99,
    16 => 149.99, 17 => 149.99, 18 => 89.99, 19 => 99.99, 20 => 129.99,
    21 => 549.99, 22 => 899.99, 23 => 299.99, 24 => 249.99, 25 => 179.99,
    26 => 89.99, 27 => 199.99, 28 => 149.99, 29 => 189.99
];

$precio = isset($precios[$producto_id]) ? $precios[$producto_id] : 99.99;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $producto['nombre_producto']; ?> - TechStore</title>
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
                    <?php if (estaLogueado()): ?>
                        <li><a href="mis-pedidos.php">Mis Pedidos</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-info">
                <?php if (estaLogueado()): ?>
                    <span>Hola, <?php echo obtenerNombreUsuario(); ?></span>
                    <a href="carrito.php" class="carrito-link">
                        Carrito <span class="carrito-count"><?php echo contarItemsCarrito(); ?></span>
                    </a>
                    <a href="logout.php" class="btn btn-secondary">Salir</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-secondary">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="producto-detalle">
            <div class="producto-detalle-grid">
                <div>
                    <img src="img/<?php echo $producto['codigo_producto']; ?>.jpg" 
                         alt="<?php echo $producto['nombre_producto']; ?>" 
                         class="producto-detalle-img"
                         onerror="this.src='img/no-image.jpg'">
                </div>
                
                <div class="producto-info">
                    <span class="categoria"><?php echo $producto['categoria']; ?></span>
                    <h1><?php echo $producto['nombre_producto']; ?></h1>
                    <p><strong>Código:</strong> <?php echo $producto['codigo_producto']; ?></p>
                    
                    <div class="precio-grande"><?php echo formatearPrecio($precio); ?></div>
                    
                    <?php if (estaLogueado()): ?>
                        <form method="POST" action="agregar-carrito.php">
                            <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                            
                            <div class="cantidad-selector">
                                <label for="cantidad">Cantidad:</label>
                                <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="10">
                            </div>
                            
                            <button type="submit" class="btn btn-success" style="width: 100%; padding: 15px; font-size: 18px;">
                                🛒 Añadir al Carrito
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn" style="display: block; text-align: center; padding: 15px; font-size: 18px;">
                            Inicia sesión para comprar
                        </a>
                    <?php endif; ?>
                    
                    <div style="margin-top: 30px; padding: 20px; background-color: #ecf0f1; border-radius: 4px;">
                        <h3>✓ Envío gratis en pedidos superiores a 50€</h3>
                        <h3>✓ Garantía de 2 años</h3>
                        <h3>✓ Devolución en 30 días</h3>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px;">
                <h2>Descripción del Producto</h2>
                <p style="line-height: 1.8; color: #555; font-size: 16px;">
                    <?php echo nl2br($producto['descripcion']); ?>
                </p>
            </div>
            
            <div style="margin-top: 30px; text-align: center;">
                <a href="index.php" class="btn btn-secondary" style="display: inline-block; width: auto; padding: 12px 30px;">
                    ← Volver a la tienda
                </a>
            </div>
        </div>
    </div>
</body>
</html>
