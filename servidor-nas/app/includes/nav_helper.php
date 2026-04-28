<?php
// Genera el menú de navegación consistente en toda la web
function generarNav() {
    $es_admin = estaLogueado() && obtenerNombreUsuario() === 'admin';
    $logueado = estaLogueado();
    ?>
    <nav>
        <ul>
            <li><a href="index.php">inicio</a></li>
            <li><a href="productos.php">productos</a></li>
            <?php if ($logueado): ?>
                <li><a href="mis-pedidos.php">mis pedidos</a></li>
                <?php if ($es_admin): ?>
                    <li><a href="gestionar-envios.php">gestionar envíos</a></li>
                    <li><a href="gestionar-productos.php">gestionar productos</a></li>
                    <li><a href="admin.php">panel admin</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
    </nav>
    <?php
}

// Genera la sección user-info del header
function generarUserInfo() {
    ?>
    <div class="user-info">
        <?php if (estaLogueado()): ?>
            <p>hola, <?php echo htmlspecialchars(obtenerNombreUsuario()); ?></p>
            <a href="carrito.php" class="carrito-link">
                carrito <p class="carrito-count"><?php echo contarItemsCarrito(); ?></p>
            </a>
            <a href="logout.php" class="btn btn-secondary">salir</a>
        <?php else: ?>
            <a href="login.php" class="btn">iniciar sesión</a>
            <a href="registro.php" class="btn btn-secondary">registrarse</a>
        <?php endif; ?>
    </div>
    <?php
}
