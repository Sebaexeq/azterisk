<?php
// Incluye el archivo de configuración de la base de datos
require_once 'config.php';

// Verifica si se ha enviado el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera los datos del formulario
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $email = $_POST["email"];
    $contrasena = $_POST["contrasena"];
    $intereses = $_POST["intereses"];
    $foto_perfil = $_POST["foto_perfil"];
    $bio = $_POST["bio"];

    // Hash de la contraseña
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

    // Inserta los datos en la tabla de usuarios
    $sql = "INSERT INTO usuarios (nombre, apellido, email, contraseña, intereses, foto_perfil, bio) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssssss", $nombre, $apellido, $email, $hashed_password, $intereses, $foto_perfil, $bio);
        if (mysqli_stmt_execute($stmt)) {
            echo "Registro exitoso. ¡Bienvenido!";
        } else {
            echo "Error al registrar el usuario.";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error de preparación de la consulta.";
    }

    // Cierra la conexión a la base de datos
    mysqli_close($conexion);
}
?>
