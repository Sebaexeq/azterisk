<?php
// Inicia la sesión de usuario
session_start();

// Destruye la sesión actual
session_destroy();

// Redirige al usuario a la página de inicio de sesión
header("location: login.php");
exit;
?>
