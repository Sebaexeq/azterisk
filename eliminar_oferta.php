<?php
session_start();
require_once('config.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["id"])) {
    header("Location: login.php"); // Redirige al usuario a la página de inicio de sesión si no ha iniciado sesión
    exit();
}

// Verificar si se proporcionó un ID de alquiler válido en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idAlquiler = $_GET['id'];

    // Consulta SQL para verificar si el usuario actual es el propietario del alquiler
    $sql = "SELECT usuario_id FROM alquileres WHERE id = ?";

    // Preparar la consulta
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        // Vincular parámetros a la consulta
        mysqli_stmt_bind_param($stmt, "i", $idAlquiler);

        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            $resultado = mysqli_stmt_get_result($stmt);

            if ($fila = mysqli_fetch_assoc($resultado)) {
                $idUsuarioAlquiler = $fila['usuario_id'];

                // Verificar si el usuario actual es el propietario del alquiler
                if ($_SESSION['id'] === $idUsuarioAlquiler) {
                    // Eliminar el alquiler de la base de datos
                    $sqlEliminar = "DELETE FROM alquileres WHERE id = ?";
                    if ($stmtEliminar = mysqli_prepare($conexion, $sqlEliminar)) {
                        // Vincular parámetros a la consulta de eliminación
                        mysqli_stmt_bind_param($stmtEliminar, "i", $idAlquiler);

                        // Ejecutar la consulta de eliminación
                        if (mysqli_stmt_execute($stmtEliminar)) {
                            // Redirigir al usuario a una página de éxito o a la página principal
                            header("Location: index.php");
                            exit();
                        } else {
                            echo '<div class="container mt-5"><p>Error al eliminar el alquiler: ' . mysqli_error($conexion) . '</p></div>';
                        }
                    }
                } else {
                    echo '<div class="container mt-5"><p>No tienes permiso para eliminar este alquiler.</p></div>';
                }
            } else {
                echo '<div class="container mt-5"><p>El alquiler especificado no existe.</p></div>';
            }
        }
        mysqli_stmt_close($stmt);
    }
} else {
    echo '<div class="container mt-5"><p>Parámetro de ID de alquiler no válido.</p></div>';
}

require_once('footer.php');
?>
