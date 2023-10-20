<?php
session_start();
// Verifica si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluye el archivo de configuración de la base de datos
    require_once('config.php');

    // Obtiene los datos del formulario
    $id_oferta = $_POST["id_oferta"];
    $id_usuario = $_SESSION['id'];
    $puntuacion = $_POST["puntuacion"];
    $comentario = $_POST["comentario"];

    // Consulta SQL para insertar la reseña en la base de datos
    $sql = "INSERT INTO resenia (id_oferta, id_usuario, puntuacion, comentario)
            VALUES (?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "iiis", $id_oferta, $id_usuario, $puntuacion, $comentario);
        if (mysqli_stmt_execute($stmt)) {
            // La reseña se ha guardado correctamente
            header("Location: detalles_alquiler.php?id=" . $id_oferta);
            exit();
        } else {
            // Error al guardar la reseña
            echo 'Error al guardar la reseña: ' . mysqli_error($conexion);
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // El formulario no se ha enviado correctamente
    echo 'Acceso no autorizado.';
}
?>
