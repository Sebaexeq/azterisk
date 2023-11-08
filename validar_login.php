<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera el correo electrónico y la contraseña del formulario
    $email = $_POST["email"];
    $contrasena = $_POST["contrasena"];

    // Consulta SQL para buscar el usuario por su correo electrónico
    $sql = "SELECT id, nombre, apellido, contraseña, admin, verificado FROM usuarios WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $nombre, $apellido, $hashed_password, $admin, $verificado); // Agregar $verificado
                if (mysqli_stmt_fetch($stmt)) {
                    // Verifica la contraseña
                    if (password_verify($contrasena, $hashed_password)) {
                        // Inicio de sesión exitoso
                        session_start();
                        $_SESSION["id"] = $id;
                        $_SESSION["nombre"] = $nombre;
                        $_SESSION["apellido"] = $apellido;
                        $_SESSION["admin"] = $admin;
                        $_SESSION["verificado"] = $verificado; // Establecer la variable de sesión 'verificado'
                        header("location: perfil.php");
                    } else {
                        echo "Contraseña incorrecta. <a href='login.php'>Intenta de nuevo</a>.";
                    }
                }
            } else {
                echo "El correo electrónico ingresado no está registrado. <a href='registro.php'>Registrarse</a>.";
            }
        } else {
            echo "Error al ejecutar la consulta.";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error de preparación de la consulta.";
    }

    mysqli_close($conexion);
}
?>
