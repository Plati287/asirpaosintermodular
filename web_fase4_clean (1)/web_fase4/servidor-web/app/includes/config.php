<?php
session_start();

define('SESSION_TIMEOUT', 15 * 60); 

if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
        header("Location: login.php?sesion=expirada");
        exit();
    }
}
if (isset($_SESSION['usuario_id'])) {
    $_SESSION['ultimo_acceso'] = time();
}

$host = "db";   
$user = "tienda_user";
$pass = "tienda_pass";
$db   = "tienda_online";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión");
}

function logueado() {
    return isset($_SESSION['user']);
}

function get_carrito() {
    $key = logueado() ? 'c_' . $_SESSION['user'] : 'c_guest';
    return $_SESSION[$key] ?? [];
}

function save_carrito($carrito) {
    $key = logueado() ? 'c_' . $_SESSION['user'] : 'c_guest';
    $_SESSION[$key] = $carrito;
}

function count_carrito() {
    return count(get_carrito());
}
?>
