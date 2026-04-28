<?php
require "includes/config.php";
require "includes/funciones.php";

if (!estaLogueado()) {
    header("Location: login.php");
    exit();
}

$carrito = isset($_SESSION["carrito"]) ? $_SESSION["carrito"] : [];

if (empty($carrito)) {
    header("Location: carrito.php");
    exit();
}

$sql  = "SELECT * FROM clientes WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["usuario_id"]);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);

$total = 0;
foreach ($carrito as $item) {
    $total += $item["precio"] * $item["cantidad"];
}
$envio       = $total >= 50 ? 0 : 5.99;
$total_final = $total + $envio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - TechStore</title>
    <link rel="stylesheet" href="css/checkout.css">
    <script src="https://js.stripe.com/v3/"></script>
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
        <h1 class="page-title">finalizar compra</h1>

        <div id="mensaje-error" class="mensaje error" style="display:none;"></div>

        <div class="checkout-grid">
            <div class="white-box box-large">
                <h2>información de envío</h2>

                <div class="form-group">
                    <label>nombre completo</label>
                    <input type="text" value="<?php echo htmlspecialchars($usuario["usuario"]); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="direccion_envio">dirección de envío *</label>
                    <input type="text" id="direccion_envio" name="direccion_envio"
                           value="<?php echo htmlspecialchars($usuario["direccion"] ?? ""); ?>" required>
                </div>

                <div class="form-group">
                    <label>teléfono</label>
                    <input type="text" value="<?php echo htmlspecialchars($usuario["telefono"] ?? ""); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>ciudad</label>
                    <input type="text" value="<?php echo htmlspecialchars($usuario["ciudad"] ?? ""); ?>" readonly>
                </div>

                <h3 class="section-title">método de pago</h3>
                <div class="payment-method-box">
                    <label class="radio-label">
                        <input type="radio" name="metodo_pago" value="tarjeta" checked>
                         tarjeta de crédito/débito (Stripe)
                    </label>
                </div>

                <div class="info-box" style="margin-top:12px; font-size:0.85rem; background:#f0f7ff; border:1px solid #b3d4f5; border-radius:8px; padding:12px;">
                    <strong> Modo test  usa estas tarjetas:</strong><br>
                    Visa: <code>4242 4242 4242 4242</code><br>
                    Fecha: cualquier fecha futura · CVC: cualquier 3 dígitos
                </div>

                <div class="button-group" style="margin-top:24px;">
                    <a href="carrito.php" class="btn btn-secondary flex-1">volver al carrito</a>
                    <button id="btn-pagar" class="btn btn-success flex-1" onclick="iniciarPago()">
                        pagar con Stripe
                    </button>
                </div>
            </div>

            <div>
                <div class="white-box">
                    <h3>resumen del pedido</h3>
                    <div class="items-list">
                        <?php foreach ($carrito as $item): ?>
                            <div class="item-row">
                                <p><?php echo htmlspecialchars($item["nombre"]); ?></p><br>
                                <p><?php echo $item["cantidad"]; ?> x <?php echo formatearPrecio($item["precio"]); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="resumen-linea">
                        <p>subtotal:</p>
                        <p><?php echo formatearPrecio($total); ?></p>
                    </div>
                    <div class="resumen-linea">
                        <p>envío:</p>
                        <p><?php echo $envio > 0 ? formatearPrecio($envio) : "GRATIS"; ?></p>
                    </div>
                    <div class="resumen-linea resumen-total">
                        <p>total:</p>
                        <p><?php echo formatearPrecio($total_final); ?></p>
                    </div>
                    <div class="info-box info-box-success">
                        <p>envío estimado: 3-5 días laborables</p><br>
                        <p>garantía de devolución de 30 días</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    async function iniciarPago() {
        const direccion = document.getElementById("direccion_envio").value.trim();
        const btnPagar  = document.getElementById("btn-pagar");
        const msgError  = document.getElementById("mensaje-error");

        msgError.style.display = "none";

        if (!direccion) {
            msgError.textContent = "La dirección de envío es obligatoria";
            msgError.style.display = "block";
            return;
        }

        btnPagar.disabled    = true;
        btnPagar.textContent = "redirigiendo a Stripe...";

        try {
            const response = await fetch("crear-sesion-stripe.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ direccion_envio: direccion })
            });

            const data = await response.json();

            if (data.url) {
                window.location.href = data.url;
            } else {
                msgError.textContent = data.error || "Error al iniciar el pago";
                msgError.style.display = "block";
                btnPagar.disabled    = false;
                btnPagar.textContent = "pagar con Stripe";
            }
        } catch (e) {
            msgError.textContent = "Error de conexión. Inténtalo de nuevo.";
            msgError.style.display = "block";
            btnPagar.disabled    = false;
            btnPagar.textContent = "pagar con Stripe";
        }
    }
    </script>
</body>
</html>
