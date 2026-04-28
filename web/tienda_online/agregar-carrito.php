<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $producto_id = intval($_POST['producto_id']);
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 1;
    
    if ($cantidad < 1) $cantidad = 1;
    if ($cantidad > 10) $cantidad = 10;
    
    $sql = "SELECT p.*, c.categoria FROM productos p LEFT JOIN categoria c ON p.id_categoria = c.id WHERE p.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $producto_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($producto = mysqli_fetch_assoc($result)) {
        $precios = [
            1 => 2499.99, 2 => 3999.99, 3 => 899.99, 4 => 1899.99, 5 => 2999.99,
            6 => 2199.99, 7 => 899.99, 8 => 1099.99, 9 => 449.99, 10 => 599.99,
            11 => 299.99, 12 => 399.99, 13 => 189.99, 14 => 279.99, 15 => 189.99,
            16 => 149.99, 17 => 149.99, 18 => 89.99, 19 => 99.99, 20 => 129.99,
            21 => 549.99, 22 => 899.99, 23 => 299.99, 24 => 249.99, 25 => 179.99,
            26 => 89.99, 27 => 199.99, 28 => 149.99, 29 => 189.99
        ];
        
        $precio = isset($precios[$producto_id]) ? $precios[$producto_id] : 99.99;
        
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = array();
        }
        
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$producto_id] = array(
                'id' => $producto_id,
                'nombre' => $producto['nombre_producto'],
                'codigo' => $producto['codigo_producto'],
                'precio' => $precio,
                'cantidad' => $cantidad
            );
        }
        
        header('Location: carrito.php');
        exit();
    }
}

header('Location: index.php');
exit();
?>
