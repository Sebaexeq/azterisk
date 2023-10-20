<?php
// Configuración de la base de datos
$db_host = 'localhost'; // Host de la base de datos
$db_nombre = 'rapibnb'; // Nombre de la base de datos
$db_usuario = 'root'; // Usuario de MySQL
$db_contrasena = ''; // Contraseña de MySQL

// Conexión a la base de datos
$conexion = mysqli_connect($db_host, $db_usuario, $db_contrasena, $db_nombre);

// Verificar la conexión
if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
