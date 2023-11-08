<?php
$db_host = 'localhost';
$db_nombre = 'rapibnb';
$db_usuario = 'root';
$db_contrasena = '';

$conexion = mysqli_connect($db_host, $db_usuario, $db_contrasena, $db_nombre);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

?>
