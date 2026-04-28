<?php
session_start();
$host = "172.31.27.214";
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