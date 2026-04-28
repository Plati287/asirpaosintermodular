<?php
require "includes/config.php";
require "includes/funciones.php";
if (!estaLogueado()) {
    header("Location: login.php");
    exit();
}
$sql = "SELECT * FROM pedidos WHERE id_cliente = ? ORDER BY id_pedido DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - TechStore</title>
	<link rel="stylesheet" href="css/mis-pedidos.css">
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
        <h1 class="page-title">mis pedidos</h1>
        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="carrito-vacio">
                <h2>no tienes pedidos todavia</h2>
                <p>cuando realices tu primera compra, aparecera aqui</p>
                <a href="index.php" class="btn btn-inline-top">ir a la tienda</a>
            </div>
        <?php else: ?>
            <div class="pedidos-lista">
                <?php while ($pedido = mysqli_fetch_assoc($result)):
                    $sql_productos = "SELECT lp.*, p.nombre_producto, p.codigo_producto
                                     FROM linea_pedido lp
                                     JOIN productos p ON lp.id_producto = p.id
                                     WHERE lp.id_pedido = ?";
                    $stmt_productos = mysqli_prepare($conn, $sql_productos);
                    mysqli_stmt_bind_param($stmt_productos, "i", $pedido["id_pedido"]);
                    mysqli_stmt_execute($stmt_productos);
                    $productos = mysqli_stmt_get_result($stmt_productos);
                    $total_pedido = 0;
	                $productos_array=array();
                    while ($prod = mysqli_fetch_assoc($productos)) {
                        $productos_array[]=$prod;
                        $total_pedido += $prod["precio_unidad"] * $prod["cantidad"];
                    }
	                $estado_class = "estado-pendiente";
                    if ($pedido["estado"] == "En tránsito") {
                        $estado_class="estado-entransito";
                    } elseif ($pedido["estado"] == "Entregado") {
                        $estado_class = "estado-entregado";
                    }
                ?>
                    <div class="pedido-item">
	                    <div class="pedido-header">
                            <div>
                                <h3>pedido #<?php echo $pedido["id_pedido"]; ?></h3>
                                <p>fecha de envío: <?php echo date("d/m/Y", strtotime($pedido["fecha_envio"])); ?></p>
                            </div>
                            <div>
                                <p class="estado-badge <?php echo $estado_class; ?>">
                                    <?php echo $pedido["estado"]; ?>
	                            </p>
                                <p class="margin-top-sm"><p>total: <?php echo formatearPrecio($total_pedido); ?></p></p>
                            </div>
                        </div>
	                    <div class="order-detail-box">
	                        <p>dirección de envío:</p><br>
                            <?php echo $pedido["direccion_envio"]; ?>
                        </div>
                        <div class="pedido-productos">
                            <h4>productos:</h4>
                            <?php foreach ($productos_array as $prod): ?>
                                <div class="pedido-producto">
                                    <p><?php echo $prod["nombre_producto"]; ?></p><br>
                                    <p>codigo: <?php echo $prod["codigo_producto"]; ?> |
                                           cantidad: <?php echo $prod["cantidad"]; ?> |
                                           precio: <?php echo formatearPrecio($prod["precio_unidad"]); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($pedido["estado"] == "Pendiente"): ?>
                            <div class="status-box status-pending">
                                tu pedido esta siendo preparado para el envio
                            </div>
                        <?php elseif ($pedido["estado"] == "En tránsito"): ?>
	                        <div class="status-box status-sent">
                                tu pedido esta en camino. Llegara pronto.
                            </div>
	                    <?php else: ?>
	                        <div class="status-box status-delivered">
	                            pedido entregado con exito
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>