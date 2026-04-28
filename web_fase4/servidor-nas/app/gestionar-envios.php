<?php

require "includes/config.php";
require "includes/funciones.php";

if (!estaLogueado()) {
    header("Location: login.php");
    exit();
} 

if (obtenerNombreUsuario() != "admin") {
    header("Location: index.php");
	exit();
}

$mensaje = "";
$tipo_mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cambiar_estado"])) {
    $id_pedido = intval($_POST["id_pedido"]); 
    $nuevo_estado = limpiarEntrada($_POST["nuevo_estado"]);
    
    $sql = "UPDATE pedidos SET estado = ? WHERE id_pedido = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $id_pedido);
    
    if (mysqli_stmt_execute($stmt)) {
        $mensaje="Estado del pedido actualizado correctamente";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al actualizar el estado del pedido";
        $tipo_mensaje = "error";
    }
}

$sql = "SELECT p.*, c.usuario, c.telefono, c.ciudad
        FROM pedidos p
	    JOIN clientes c ON p.id_cliente = c.id
        ORDER BY p.id_pedido DESC"; 
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Envíos - TechStore</title>
    <link rel="stylesheet" href="css/gestionar-envios.css">

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
        <h1 class="page-title">gestion de envios</h1>
	    
        <?php if ($mensaje): ?> 
            <div class="mensaje <?php echo $tipo_mensaje; ?>"> 
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
	    <div class="tabla-envios">
            <table>
	            <thead>
	                <tr>
                        <th>ID Pedido</th>
                        <th>Cliente</th>
                        <th>Dirección Envío</th>
                        <th>Teléfono</th>
                        <th>Fecha Envío</th> 
	                    <th>Estado Actual</th>
                        <th>Cambiar Estado</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($pedido = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><p>#<?php echo $pedido["id_pedido"]; ?></p></td>
                                <td><?php echo htmlspecialchars($pedido["usuario"]); ?></td>
                                <td><?php echo htmlspecialchars($pedido["direccion_envio"]); ?></td>
                                <td><?php echo htmlspecialchars($pedido["telefono"]); ?></td>
                                <td><?php echo date("d/m/Y", strtotime($pedido["fecha_envio"])); ?></td>
                                <td>
                                    <?php
                                    $clase_estado = "";
                                    switch($pedido["estado"]) {
                                        case "Pendiente": 
                                            $clase_estado = "estado-pendiente";
                                            break;
                                        case "En tránsito": 
                                            $clase_estado = "estado-transito";
                                            break;
                                        case "Entregado":
                                            $clase_estado = "estado-entregado";
                                            break; 
                                        case "Cancelado":
                                            $clase_estado = "estado-cancelado";
                                            break;
	                                }
                                    ?>
                                    <p class="estado-badge <?php echo $clase_estado; ?>">
                                        <?php echo $pedido["estado"]; ?>
                                    </p>
                                </td>
                                <td>
                                    <form method="POST" action="" class="inline-form">
                                        <input type="hidden" name="id_pedido" value="<?php echo $pedido["id_pedido"]; ?>">
                                        <select name="nuevo_estado" class="select-estado" required>
                                            <option value="">Seleccionar...</option>
                                            <option value="Pendiente" <?php echo $pedido["estado"] == "Pendiente" ? "selected" : ""; ?>>pendiente</option>
                                            <option value="En tránsito" <?php echo $pedido["estado"] == "En tránsito" ? "selected" : ""; ?>>en tránsito</option>
	                                        <option value="Entregado" <?php echo $pedido["estado"]=="Entregado" ? "selected" : ""; ?>>entregado</option>
                                            <option value="Cancelado" <?php echo $pedido["estado"] == "Cancelado" ? "selected" : ""; ?>>cancelado</option>
                                        </select>
	                                    <button type="submit" name="cambiar_estado" class="btn btn-success btn-small">
                                            actualizar
                                        </button>
                                    </form>
                                </td>
                            </tr>
	                    <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                no hay pedidos registrados
                            </td> 
                        </tr>
                    <?php endif; ?>
                </tbody> 
            </table>
        </div>
</body>
</html> 
