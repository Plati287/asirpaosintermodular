<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$categorias = obtenerCategorias($conn);

$where = "WHERE 1=1";
$params = array();

if (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
    $where .= " AND p.id_categoria = ?";
    $params[] = $_GET['categoria'];
}

if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $where .= " AND (p.nombre_producto LIKE ? OR p.descripcion LIKE ?)";
    $buscar = "%" . $_GET['buscar'] . "%";
    $params[] = $buscar;
    $params[] = $buscar;
}

$sql = "SELECT p.*, c.categoria 
        FROM productos p 
        LEFT JOIN categoria c ON p.id_categoria = c.id 
        $where 
        ORDER BY p.id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - TechStore</title>
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
                    <?php if (estaLogueado()): ?>
                        <li><a href="mis-pedidos.php">Mis Pedidos</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="user-info">
                <?php if (estaLogueado()): ?>
                    <span>Hola, <?php echo obtenerNombreUsuario(); ?></span>
                    <a href="carrito.php" class="carrito-link">
                        🛒 Carrito <span class="carrito-count"><?php echo contarItemsCarrito(); ?></span>
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
        <h1 style="margin-bottom: 20px;">Catálogo de Productos</h1>
        
        <div class="filtros">
            <h3>Filtrar Productos</h3>
            <form method="GET" action="productos.php">
                <select name="categoria" onchange="this.form.submit()">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo $cat['categoria']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="buscar" placeholder="Buscar productos..." 
                       value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>"
                       style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                
                <button type="submit" class="btn" style="width: auto; padding: 8px 20px;">Buscar</button>
                
                <?php if (isset($_GET['categoria']) || isset($_GET['buscar'])): ?>
                    <a href="productos.php" class="btn btn-secondary" style="display: inline-block; width: auto; padding: 8px 20px;">Limpiar filtros</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="productos-grid">
            <?php 
            $precios = [
                1 => 2499.99, 2 => 3999.99, 3 => 899.99, 4 => 1899.99, 5 => 2999.99,
                6 => 2199.99, 7 => 899.99, 8 => 1099.99, 9 => 449.99, 10 => 599.99,
                11 => 299.99, 12 => 399.99, 13 => 189.99, 14 => 279.99, 15 => 189.99,
                16 => 149.99, 17 => 149.99, 18 => 89.99, 19 => 99.99, 20 => 129.99,
                21 => 549.99, 22 => 899.99, 23 => 299.99, 24 => 249.99, 25 => 179.99,
                26 => 89.99, 27 => 199.99, 28 => 149.99, 29 => 189.99
            ];
            
            while ($producto = mysqli_fetch_assoc($result)): 
                $precio = isset($precios[$producto['id']]) ? $precios[$producto['id']] : 99.99;
            ?>
                <div class="producto-card">
                    <img src="img/<?php echo $producto['codigo_producto']; ?>.jpg" 
                         alt="<?php echo $producto['nombre_producto']; ?>" 
                         class="producto-img"
                         onerror="this.src='img/no-image.jpg'">
                    
                    <span class="categoria"><?php echo $producto['categoria']; ?></span>
                    <h3><?php echo $producto['nombre_producto']; ?></h3>
                    <p class="descripcion"><?php echo substr($producto['descripcion'], 0, 100); ?>...</p>
                    <div class="precio"><?php echo formatearPrecio($precio); ?></div>
                    
                    <div class="producto-acciones">
                        <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-secondary">Ver más</a>
                        <?php if (estaLogueado()): ?>
                            <form method="POST" action="agregar-carrito.php" style="flex: 1;">
                                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" class="btn">Añadir al carrito</button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn">Comprar</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="carrito-vacio">
                <h2>No se encontraron productos</h2>
                <p>Intenta con otros filtros de búsqueda</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
