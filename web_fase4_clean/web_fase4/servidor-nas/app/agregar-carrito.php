<?php

require "includes/config.php"; 
require "includes/funciones.php";

if (!estaLogueado()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $producto_id = intval($_POST["producto_id"]);
    $cantidad = isset($_POST["cantidad"]) ? intval($_POST["cantidad"]) : 1;
    
    if ($cantidad < 1) $cantidad = 1;
	if ($cantidad > 10) $cantidad = 10;
    
    $sql="SELECT p.*, c.categoria FROM productos p LEFT JOIN categoria c ON p.id_categoria = c.id WHERE p.id = ?";
    $stmt = mysqli_prepare($conn, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $producto_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt); 
    
    if ($producto = mysqli_fetch_assoc($result)) {
        $precio = isset($producto["precio"]) && $producto["precio"] > 0 ? $producto["precio"] : 99.99;
        
        if (!isset($_SESSION["carrito"])) {
            $_SESSION["carrito"] = array();
        }
	    
        if (isset($_SESSION["carrito"][$producto_id])) {
	        $_SESSION["carrito"][$producto_id]["cantidad"] += $cantidad;
        } else {
            $_SESSION["carrito"][$producto_id] = array(
                "id" => $producto_id,
                "nombre" => $producto["nombre_producto"],
                "codigo" => $producto["codigo_producto"],
                "precio" => $precio,
                "cantidad" => $cantidad 
            );
	    }
        
        header("Location: carrito.php");
        exit();
    }
} 

header("Location: index.php");
exit();
?>
