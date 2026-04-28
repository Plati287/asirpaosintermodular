<?php
require "includes/config.php";
require "includes/funciones.php";

$categorias = obtenerCategorias($conn);

$where = "WHERE 1=1";
$params = array();

if (isset($_GET["categoria"]) && !empty($_GET["categoria"])) {
    $where .= " AND p.id_categoria=?";
    $params[] = $_GET["categoria"];
} 

if (isset($_GET["buscar"]) && !empty($_GET["buscar"])) {
    $where .= " AND (p.nombre_producto LIKE ? OR p.descripcion LIKE ?)"; 
	$buscar="%" . $_GET["buscar"] . "%";
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
    $types=str_repeat("s", count($params));
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
    <link rel="stylesheet" href="css/productos.css">
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

    <div class="container">
        <h1 class="page-title-sm">catalogo de productos</h1>
        
        <div class="filtros">
            <h3>filtrar productos</h3>
            <form method="GET" action="productos.php">
                <select name="categoria" onchange="this.form.submit()"> 
                    <option value="">todas las categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat["id"]; ?>" 
                                <?php echo (isset($_GET["categoria"]) && $_GET["categoria"] == $cat["id"]) ? "selected" : ""; ?>>
	                        <?php echo $cat["categoria"]; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="buscar" placeholder="Buscar productos..." 
                       value="<?php echo isset($_GET["buscar"]) ? htmlspecialchars($_GET["buscar"]) : ""; ?>"
	                   class="search-input">
                
                <button type="submit" class="btn btn-compact">buscar</button>
                
                <?php if (isset($_GET["categoria"]) || isset($_GET["buscar"])): ?>
                    <a href="productos.php" class="btn btn-secondary btn-compact btn-inline">limpiar filtros</a>
                <?php endif; ?>
            </form> 
	    </div>

	    <div class="productos-grid"> 
            <?php 
            while ($producto = mysqli_fetch_assoc($result)): 
                $precio = isset($producto["precio"]) && $producto["precio"] > 0 ? $producto["precio"] : 99.99;
            ?>
                <div class="producto-card"> 
                    <img src="img/<?php echo $producto["codigo_producto"]; ?>.jpg" 
                         alt="<?php echo $producto["nombre_producto"]; ?>" 
                         class="producto-img"
	                     onerror="this.src='img/no-image.jpg'">
	                
                    <p class="categoria"><?php echo $producto["categoria"]; ?></p>
                    <h3><?php echo $producto["nombre_producto"]; ?></h3>
                    <p class="descripcion"><?php echo substr($producto["descripcion"], 0, 100); ?>...</p>
                    <div class="precio"><?php echo formatearPrecio($precio); ?></div>
                    
                    <div class="producto-acciones">
                        <a href="producto.php?id=<?php echo $producto["id"]; ?>" class="btn btn-secondary">ver mas</a>
	                    <?php if (estaLogueado()): ?>
                            <form method="POST" action="agregar-carrito.php" class="flex-1">
                                <input type="hidden" name="producto_id" value="<?php echo $producto["id"]; ?>"> 
                                <button type="submit" class="btn">añadir al carrito</button>
                            </form> 
                        <?php else: ?>
                            <a href="login.php" class="btn">comprar</a>
                        <?php endif; ?>
	                </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="carrito-vacio">
                <h2>no se encontraron productos</h2>
                <p>intenta con otros filtros de busqueda</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 
