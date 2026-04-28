<?php
require "includes/config.php";
require "includes/funciones.php";
require "vendor/autoload.php";

if (!estaLogueado()) {
    http_response_code(401);
    echo json_encode(["error" => "No autenticado"]);
    exit();
}

$carrito = isset($_SESSION["carrito"]) ? $_SESSION["carrito"] : [];

if (empty($carrito)) {
    http_response_code(400);
    echo json_encode(["error" => "Carrito vacío"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$direccion_envio = isset($data["direccion_envio"]) ? trim($data["direccion_envio"]) : "";

if (empty($direccion_envio)) {
    http_response_code(400);
    echo json_encode(["error" => "La dirección de envío es obligatoria"]);
    exit();
}

$_SESSION["direccion_envio_pendiente"] = $direccion_envio;

\Stripe\Stripe::setApiKey("sk_test_51T99CG3SoBxxvN7zsmz96dZFVVESCtxCOk2hKYaxRBKgmwWBcpIdJmZ3q27FIGbaZhE9izn1Qyf6OB9JqRSzx8iC00MCRH9Sqh");

$line_items = [];
foreach ($carrito as $item) {
    $line_items[] = [
        "price_data" => [
            "currency"     => "eur",
            "product_data" => ["name" => $item["nombre"]],
            "unit_amount"  => intval(round($item["precio"] * 100)), 
        ],
        "quantity" => $item["cantidad"],
    ];
}

$total = 0;
foreach ($carrito as $item) {
    $total += $item["precio"] * $item["cantidad"];
}
if ($total < 50) {
    $line_items[] = [
        "price_data" => [
            "currency"     => "eur",
            "product_data" => ["name" => "Gastos de envío"],
            "unit_amount"  => 599, 
        ],
        "quantity" => 1,
    ];
}

$protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$base_url  = $protocol . "://" . $_SERVER["HTTP_HOST"];

try {
    $session = \Stripe\Checkout\Session::create([
        "payment_method_types" => ["card"],
        "line_items"           => $line_items,
        "mode"                 => "payment",
        "success_url"          => $base_url . "/pago-exito.php?session_id={CHECKOUT_SESSION_ID}",
        "cancel_url"           => $base_url . "/checkout.php",
        "locale"               => "es",
    ]);

    echo json_encode(["url" => $session->url]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
