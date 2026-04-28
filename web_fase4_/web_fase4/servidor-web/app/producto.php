<?php
require "includes/config.php";
require "includes/funciones.php";
if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit();
}
$producto_id = intval($_GET["id"]);
$sql = "SELECT p.*, c.categoria
        FROM productos p
        LEFT JOIN categoria c ON p.id_categoria=c.id
        WHERE p.id = ?";
$stmt=mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $producto_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit();
}
$producto = mysqli_fetch_assoc($result);
$precio = isset($producto["precio"]) && $producto["precio"] > 0 ? $producto["precio"] : 99.99;
$mensaje_resena = "";
if ($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["submit_resena"])) {
    if (estaLogueado()) {
        $valoracion = intval($_POST["valoracion"]);
        $comentario = limpiarEntrada($_POST["comentario"]);
        $id_cliente = $_SESSION["usuario_id"];
        $sql_check = "SELECT id FROM resenas WHERE id_producto = ? AND id_cliente = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ii", $producto_id, $id_cliente);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        if (mysqli_num_rows($result_check) > 0) {
            $mensaje_resena = "Ya has dejado una reseña para este producto.";
        } else {
            $sql_insert = "INSERT INTO resenas (id_producto, id_cliente, valoracion, comentario) VALUES (?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iiis", $producto_id, $id_cliente, $valoracion, $comentario);
            if (mysqli_stmt_execute($stmt_insert)) {
                $mensaje_resena="¡Gracias por tu reseña!";
            } else {
                $mensaje_resena = "Error al guardar la reseña.";
            }
	    }
    }
}
$sql_resenas = "SELECT r.*, c.usuario
                FROM resenas r
                JOIN clientes c ON r.id_cliente = c.id
                WHERE r.id_producto = ?
                ORDER BY r.fecha DESC";
$stmt_resenas = mysqli_prepare($conn, $sql_resenas);
mysqli_stmt_bind_param($stmt_resenas, "i", $producto_id);
mysqli_stmt_execute($stmt_resenas);
$result_resenas = mysqli_stmt_get_result($stmt_resenas);
$sql_promedio="SELECT AVG(valoracion) as promedio, COUNT(*) as total FROM resenas WHERE id_producto = ?";
$stmt_promedio = mysqli_prepare($conn, $sql_promedio);
mysqli_stmt_bind_param($stmt_promedio, "i", $producto_id);
mysqli_stmt_execute($stmt_promedio);
$result_promedio = mysqli_stmt_get_result($stmt_promedio);
$datos_promedio = mysqli_fetch_assoc($result_promedio);
$promedio = $datos_promedio["promedio"] ? round($datos_promedio["promedio"], 1) : 0;
$total_resenas=$datos_promedio["total"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $producto["nombre_producto"]; ?> - TechStore</title>
    <link rel="stylesheet" href="css/producto.css">
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
        <div class="producto-detalle">
            <div class="producto-detalle-grid">
                <div>
                    <img src="img/<?php echo $producto["codigo_producto"]; ?>.jpg"
                         alt="<?php echo $producto["nombre_producto"]; ?>"
                         class="producto-detalle-img"
                         onerror="this.src='img/no-image.jpg'">
                </div>
                <div class="producto-info">
                    <p class="categoria"><?php echo $producto["categoria"]; ?></p>
                    <h1><?php echo $producto["nombre_producto"]; ?></h1>
                    <p><p>codigo:</p> <?php echo $producto["codigo_producto"]; ?></p>
                    <?php if ($total_resenas > 0): ?>
                    <div class="rating-container">
                        <p class="rating-text">
                            <?php echo $promedio; ?>/5 (<?php echo $total_resenas; ?> <?php echo $total_resenas == 1 ? "reseña" : "reseñas"; ?>)
                        </p>
                    </div>
                    <?php endif; ?>
                    <div class="precio-grande"><?php echo formatearPrecio($precio); ?></div>
                    <div class="stock-box <?php echo $producto["stock"] > 0 ? "stock-available" : "stock-unavailable"; ?>">
                        <p><?php echo $producto["stock"] > 0 ? "En stock" : "Sin stock"; ?>:</p>
                        <?php echo $producto["stock"]; ?> unidades disponibles
                    </div>
                    <?php if (estaLogueado() && $producto["stock"] > 0): ?>
                        <form method="POST" action="agregar-carrito.php">
                            <input type="hidden" name="producto_id" value="<?php echo $producto["id"]; ?>">
                            <div class="cantidad-selector">
                                <label for="cantidad">cantidad:</label>
                                <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="<?php echo $producto["stock"]; ?>">
                            </div>
                            <button type="submit" class="btn btn-success btn-large btn-full">
                                añadir al carrito
                            </button>
                        </form>
                    <?php elseif ($producto["stock"] == 0): ?>
                        <div class="btn btn-disabled btn-large btn-block">
                            producto agotado
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-large btn-block">
                            inicia sesion para comprar
                        </a>
                    <?php endif; ?>
                    <div class="features-box">
                        <h3>envío gratis en pedidos superiores a 50€</h3>
                        <h3>garantia de 2 años</h3>
                        <h3>devolución en 30 dias</h3>
                    </div>
                </div>
            </div>
            <div class="section-spacer">
                <h2>descripción del producto</h2>
                <p class="description-text">
                    <?php echo nl2br($producto["descripcion"]); ?>
                </p>
            </div>
	        <div class="section-spacer-lg">
                <h2>reseñas de clientes</h2>
                <?php if (estaLogueado()): ?>
                    <div class="review-form-box">
                        <h3>escribe tu reseña</h3>
                        <?php if ($mensaje_resena): ?>
	                        <div class="mensaje <?php echo strpos($mensaje_resena, "Ya has") !== false ? "error" : "exito"; ?>">
                                <?php echo $mensaje_resena; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="valoracion">valoracion:</label>
                                <select id="valoracion" name="valoracion" required class="form-control">
                                    <option value="">Selecciona una valoracion</option>
                                    <option value="5">(5 estrellas - excelente)</option>
                                    <option value="4">(4 estrellas - muy bueno)</option>
                                    <option value="3">(3 estrellas - bueno)</option>
                                    <option value="2">(2 estrellas - regular)</option>
                                    <option value="1">(1 estrella - malo)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="comentario">comentario:</label>
                                <textarea id="comentario" name="comentario" rows="4" required class="form-control" placeholder="Comparte tu experiencia con este producto..."></textarea>
                            </div>
                            <button type="submit" name="submit_resena" class="btn btn-success">publicar reseña</button>
	                    </form>
                    </div>
                <?php else: ?>
                    <div class="login-prompt-box">
	                    <p>para dejar una reseña, por favor <a href="login.php" class="link-primary">inicia sesion</a></p>
                    </div>
                <?php endif; ?>
                <?php if (mysqli_num_rows($result_resenas) > 0): ?>
                    <div class="review-card">
                        <?php while ($resena = mysqli_fetch_assoc($result_resenas)): ?>
                            <div class="review-item">
                                <div class="review-header-full">
                                    <div>
                                        <p class="review-author"><?php echo htmlspecialchars($resena["usuario"]); ?></p>
                                        <p class="review-stars-display">
                                            <?php echo $resena["valoracion"]; ?>/5
                                        </p>
                                    </div>
                                    <p class="review-date">
                                        <?php echo date("d/m/Y", strtotime($resena["fecha"])); ?>
	                                </p>
                                </div>
                                <p class="review-comment">
                                    <?php echo nl2br(htmlspecialchars($resena["comentario"])); ?>
                                </p>
	                        </div>
                        <?php endwhile; ?>
                    </div>
	            <?php else: ?>
	                <div class="no-reviews-box">
	                    <p class="text-muted">aun no hay reseñas para este producto. ¡Se el primero en dejar una!</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="back-link-container">
                <a href="index.php" class="btn btn-secondary btn-inline btn-medium">
                    volver a la tienda
	            </a>
            </div>
        </div>
    </div>
</body>
</html>