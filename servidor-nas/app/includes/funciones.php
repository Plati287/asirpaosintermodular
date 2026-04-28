<?php
// Función para verificar si el usuario está logueado
function estaLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Función para obtener el nombre del usuario
function obtenerNombreUsuario() {
    return isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : '';
}

// Función para contar items del carrito
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

// Función para formatear precio
function formatearPrecio($precio) {
    return number_format($precio, 2, ',', '.') . ' €';
}

// Función para limpiar entrada
function limpiarEntrada($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// Función para obtener categorías
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
