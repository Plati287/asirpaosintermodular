<?php
require "includes/config.php";
require "includes/funciones.php";
require "vendor/autoload.php";

if (!estaLogueado()) {
    header("Location: login.php");
    exit();
}

\Stripe\Stripe::setApiKey("sk_test_51T99CG3SoBxxvN7zsmz96dZFVVESCtxCOk2hKYaxRBKgmwWBcpIdJmZ3q27FIGbaZhE9izn1Qyf6OB9JqRSzx8iC00MCRH9Sqh");

$session_id      = isset($_GET["session_id"]) ? $_GET["session_id"] : "";
$error           = "";
$pedido_creado   = false;

if (empty($session_id)) {
    header("Location: index.php");
    exit();
}

try {
    $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);

    if ($stripe_session->payment_status !== "paid") {
        $error = "El pago no se completó correctamente.";
    } else {
        $carrito         = isset($_SESSION["carrito"]) ? $_SESSION["carrito"] : [];
        $direccion_envio = isset($_SESSION["direccion_envio_pendiente"]) ? $_SESSION["direccion_envio_pendiente"] : "";

        if (!empty($carrito) && !empty($direccion_envio)) {
            mysqli_begin_transaction($conn);
            try {
                
                foreach ($carrito as $item) {
                    $stmt_check = mysqli_prepare($conn, "SELECT stock FROM productos WHERE id = ?");
                    mysqli_stmt_bind_param($stmt_check, "i", $item["id"]);
                    mysqli_stmt_execute($stmt_check);
                    $result_check  = mysqli_stmt_get_result($stmt_check);
                    $producto_stock = mysqli_fetch_assoc($result_check);
                    if ($producto_stock["stock"] < $item["cantidad"]) {
                        throw new Exception("Stock insuficiente para: " . $item["nombre"]);
                    }
                }

                
                $fecha_envio = date("Y-m-d", strtotime("+3 days"));
                $stmt = mysqli_prepare($conn, "INSERT INTO pedidos (id_cliente, estado, direccion_envio, fecha_envio) VALUES (?, 'Pagado', ?, ?)");
                mysqli_stmt_bind_param($stmt, "iss", $_SESSION["usuario_id"], $direccion_envio, $fecha_envio);
                mysqli_stmt_execute($stmt);
                $pedido_id = mysqli_insert_id($conn);

                
                $stmt_linea = mysqli_prepare($conn, "INSERT INTO linea_pedido (id_pedido, id_producto, cantidad, precio_unidad) VALUES (?, ?, ?, ?)");
                $stmt_stock = mysqli_prepare($conn, "UPDATE productos SET stock = stock - ? WHERE id = ?");

                foreach ($carrito as $item) {
                    mysqli_stmt_bind_param($stmt_linea, "iiid", $pedido_id, $item["id"], $item["cantidad"], $item["precio"]);
                    mysqli_stmt_execute($stmt_linea);
                    mysqli_stmt_bind_param($stmt_stock, "ii", $item["cantidad"], $item["id"]);
                    mysqli_stmt_execute($stmt_stock);
                }

                mysqli_commit($conn);

                
                $_SESSION["carrito"] = [];
                unset($_SESSION["direccion_envio_pendiente"]);
                $pedido_creado = true;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        } else {
            
            $pedido_creado = true;
        }
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    $error = "Error verificando el pago: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago completado - TechStore</title>
    <link rel="stylesheet" href="css/checkout.css">
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
        <div class="form-container form-container-narrow">
            <?php if ($pedido_creado): ?>
                <h2>¡pago realizado con éxito! </h2>
                <div class="mensaje exito">
                    tu pedido ha sido procesado y el pago confirmado por Stripe.
                </div>
                <p class="text-center-spaced">
                    recibirás tu pedido en un plazo de 3-5 días laborables.
                </p>
                <div class="button-group">
                    <a href="mis-pedidos.php" class="btn flex-1">ver mis pedidos</a>
                    <a href="index.php" class="btn btn-secondary flex-1">volver a la tienda</a>
                </div>
            <?php else: ?>
                <h2>error en el pago</h2>
                <div class="mensaje error"><?php echo htmlspecialchars($error); ?></div>
                <div class="button-group">
                    <a href="checkout.php" class="btn flex-1">intentar de nuevo</a>
                    <a href="index.php" class="btn btn-secondary flex-1">volver a la tienda</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
