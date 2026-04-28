<?php
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

function obtenerNombreUsuario() {
    return isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : '';
}

function contarItemsCarrito() {
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['cantidad'];
    }
    return $total;
}

function formatearPrecio($precio) {
    return number_format($precio, 2, ',', '.') . ' €';
}

function limpiarEntrada($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

function obtenerCategorias($conn) {
    $sql = "SELECT * FROM categoria ORDER BY categoria";
    $result = mysqli_query($conn, $sql);
    $categorias = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $categorias[] = $row;
    }
    return $categorias;
}
?>
