<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'tienda_online');

// Conexión a la base de datos
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Configurar charset
mysqli_set_charset($conn, "utf8");

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
