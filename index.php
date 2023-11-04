<?php
session_start();
require_once 'config.php';
include 'header.php';

$sql_update = "UPDATE aplicaciones_alquiler SET estado = 'completado' WHERE fecha_fin = CURDATE() AND estado = 'aceptado'";
mysqli_query($conexion, $sql_update);

// Obtener el ID del usuario logueado
if (isset($_SESSION["id"])) {
    $usuario_id = $_SESSION["id"];

    // Consulta combinada para verificar la finalización del alquiler y si el usuario ha dejado una reseña,
    // además de verificar si el usuario está verificado.
    $query = "SELECT a.alquiler_id, al.titulo, 
              (SELECT r.id FROM resenia r WHERE r.id_usuario = a.usuario_id AND r.id_oferta = a.alquiler_id LIMIT 1) AS resenia_id,
              u.verificado
          FROM aplicaciones_alquiler a
          INNER JOIN alquileres al ON a.alquiler_id = al.id
          INNER JOIN usuarios u ON a.usuario_id = u.id
          WHERE a.usuario_id = ? AND a.fecha_fin >= CURDATE() AND a.estado = 'completado' AND u.verificado = 1
          LIMIT 1";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $alquiler_id = $row['alquiler_id'];
        $titulo_alquiler = $row['titulo'];
        $resenia_id = $row['resenia_id'];
        $verificado = $row['verificado'];

        // Si el usuario no ha dejado una reseña y está verificado, se muestra la opción de dejar una reseña
        if (empty($resenia_id) && $verificado) {
            $mensaje_resena = "<div class='text-center'>¿Qué te ha parecido <b>{$titulo_alquiler}</b>? <a href='detalles_alquiler.php?id={$alquiler_id}#reseñas'>¡Haz clic aquí para dejar una reseña!</a></div>";
            echo "<div class='alert alert-info'>{$mensaje_resena}</div>";
        }
    }

    $stmt->close();
}

include 'ultimosalquileres.php';
require_once('footer.php');
?>
