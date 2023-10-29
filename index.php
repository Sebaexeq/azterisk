<?php
session_start();
require_once 'config.php';
include 'header.php';

$sql_update = "UPDATE aplicaciones_alquiler SET estado = 'completado' WHERE fecha_fin = CURDATE() AND estado = 'aceptado'";
mysqli_query($conexion, $sql_update);

// Obtener el ID del usuario logueado
if (isset($_SESSION["id"])) {
    $usuario_id = $_SESSION["id"];

    // Primero, verifica si la fecha de finalización coincide con la fecha actual
    $query = "SELECT aplicaciones_alquiler.alquiler_id, alquileres.titulo 
          FROM aplicaciones_alquiler 
          INNER JOIN alquileres ON aplicaciones_alquiler.alquiler_id = alquileres.id 
          WHERE aplicaciones_alquiler.usuario_id = ? AND aplicaciones_alquiler.fecha_fin >= CURDATE() AND aplicaciones_alquiler.estado = 'completado'";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $alquiler_id = $row['alquiler_id'];
        $titulo_alquiler = $row['titulo'];

        // Ahora, verifica si ya existe una reseña del usuario para esa oferta de alquiler
        $query_resena = "SELECT id FROM resenia WHERE id_usuario = ? AND id_oferta = ?";
        $stmt_resena = $conexion->prepare($query_resena);
        $stmt_resena->bind_param("ii", $usuario_id, $alquiler_id);
        $stmt_resena->execute();
        $result_resena = $stmt_resena->get_result();

        // Si no hay resultados, significa que el usuario aún no ha dejado una reseña
        if (!$result_resena->fetch_assoc()) {
            $mensaje_resena = "<div class='text-center'>¿Qué te ha parecido <b>{$titulo_alquiler}</b>? <a href='detalles_alquiler.php?id={$alquiler_id}#reseñas'>¡Haz clic aquí para dejar una reseña!</a></div>";
            echo "<div class='alert alert-info'>{$mensaje_resena}</div>";
        }

        $stmt_resena->close();
    }

    $stmt->close();
}

include 'ultimosalquileres.php';
require_once('footer.php');
?>
