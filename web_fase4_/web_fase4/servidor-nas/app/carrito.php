<?php
require "includes/config.php";
require "includes/funciones.php";
if (!estaLogueado()) {
	header("Location: login.php");
    exit();
}
if (isset($_POST["actualizar"])) {
    $producto_id = intval($_POST["producto_id"]);
    $cantidad = intval($_POST["cantidad"]);
    if ($cantidad > 0 && $cantidad <= 10) {
        $_SESSION["carrito"][$producto_id]["cantidad"] = $cantidad;
    }
    header("Location: carrito.php");
    exit();
}
if (isset($_GET["eliminar"])) {
    $producto_id = intval($_GET["eliminar"]);
	unset($_SESSION["carrito"][$producto_id]);
    header("Location: carrito.php");
    exit();
}
if (isset($_GET["vaciar"]))
{
    $_SESSION["carrito"] = array();
    header("Location: carrito.php");
    exit();
}
$carrito = isset($_SESSION["carrito"]) ? $_SESSION["carrito"] : array();
$total=0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Carrito de Compras - TechStore</title>
    <link rel="stylesheet" href="css/carrito.css">
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
        <h1 class="page-title">mi carrito de compras</h1>
        <?php if (empty($carrito)): ?>
            <div class="carrito-vacio">
                <h2>tu carrito está vacio</h2>
                <p>añade productos desde nuestra tienda</p>
                <a href="index.php" class="btn btn-inline-top">ir a la tienda</a>
            </div>
        <?php else: ?>
            <div class="carrito-tabla">
	            <table>
	                <thead>
                        <tr>
                            <th>imagen</th>
                            <th>producto</th>
                            <th>precio</th>
	                        <th>cantidad</th>
                            <th>subtotal</th>
                            <th>acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrito as $item):
                            $subtotal = $item["precio"] * $item["cantidad"];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td>
                                    <img src="img/<?php echo $item["codigo"]; ?>.jpg"
                                         alt="<?php echo $item["nombre"]; ?>"
                                         onerror="this.src='img/no-image.jpg'">
                                </td>
	                            <td>
                                    <p><?php echo $item["nombre"]; ?></p><br>
                                    <p><?php echo $item["codigo"]; ?></p>
	                            </td>
                                <td><?php echo formatearPrecio($item["precio"]); ?></td>
                                <td>
	                                <form method="POST" action="carrito.php" class="cantidad-control">
                                        <input type="hidden" name="producto_id" value="<?php echo $item["id"]; ?>">
                                        <input type="number" name="cantidad" value="<?php echo $item["cantidad"]; ?>" min="1" max="10">
                                        <button type="submit" name="actualizar">actualizar</button>
                                    </form>
                                </td>
                                <td><p><?php echo formatearPrecio($subtotal); ?></p></td>
                                <td>
	                                <a href="carrito.php?eliminar=<?php echo $item["id"]; ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm("¿Eliminar este producto del carrito?")">
                                        eliminar
                                    </a>
                                </td>
	                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="carrito-resumen">
                <h3>resumen del pedido</h3>
                <div class="resumen-linea">
	                <p>subtotal:</p>
                    <p><?php echo formatearPrecio($total); ?></p>
                </div>
                <div class="resumen-linea">
                    <p>envio:</p>
                    <p><?php echo $total >= 50 ? "GRATIS" : formatearPrecio(5.99); ?></p>
                </div>
                <div class="resumen-linea resumen-total">
                    <p>total:</p>
                    <p><?php echo formatearPrecio($total >= 50 ? $total : $total + 5.99); ?></p>
                </div>
                <div class="button-group">
                    <a href="index.php" class="btn btn-secondary flex-1">seguir comprando</a>
                    <a href="checkout.php" class="btn btn-success flex-1">proceder al pago</a>
                </div>
                <a href="carrito.php?vaciar=1"
	               class="btn btn-danger btn-margin-top"
                   onclick="return confirm('¿Vaciar todo el carrito?')">
                    vaciar carrito
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>