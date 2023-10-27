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

$fecha_actual = date("Y-m-d");
$query = "UPDATE usuarios SET verificado = 0, fecha_verificacion = NULL WHERE fecha_verificacion = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("s", $fecha_actual);
$stmt->execute();
$stmt->close();

// Actualizar el estado de los alquileres que no están dentro del rango de fechas a inactivos
$sql = "UPDATE alquileres SET activa = 0 WHERE (CURDATE() NOT BETWEEN fecha_inicio AND fecha_fin) AND fecha_inicio != '0000-00-00' AND fecha_fin != '0000-00-00'";
if (!mysqli_query($conexion, $sql)) {
    echo "Error al actualizar alquileres fuera de rango: " . mysqli_error($conexion);
}

// Activar las ofertas de alquiler de usuarios verificados que están dentro del rango de fechas
$sql = "UPDATE alquileres a INNER JOIN usuarios u ON a.usuario_id = u.id SET a.activa = 1 WHERE u.verificado = 1 AND CURDATE() BETWEEN a.fecha_inicio AND a.fecha_fin";
if (!mysqli_query($conexion, $sql)) {
    echo "Error al actualizar alquileres dentro de rango para usuarios verificados: " . mysqli_error($conexion);
}

// Activar las ofertas de alquiler de usuarios no verificados que han sido publicadas hace más de 3 días hábiles y están dentro del rango de fechas
$fecha_hace_tres_dias = date('Y-m-d', strtotime("-3 weekdays"));
$sql = "UPDATE alquileres a INNER JOIN usuarios u ON a.usuario_id = u.id SET a.activa = 1 WHERE u.verificado = 0 AND a.activa = 0 AND a.fecha_publicacion <= ? AND CURDATE() BETWEEN a.fecha_inicio AND a.fecha_fin";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $fecha_hace_tres_dias);
$stmt->execute();
$stmt->close();

// Eliminar las solicitudes de alquiler pendientes que han estado en ese estado durante más de 72 horas
$fecha_hace_tres_dias = date('Y-m-d H:i:s', strtotime("-3 days"));
$sql_eliminar = "DELETE FROM aplicaciones_alquiler WHERE estado = 'pendiente' AND fecha_aplicacion <= ?";
$stmt_eliminar = $conexion->prepare($sql_eliminar);
$stmt_eliminar->bind_param("s", $fecha_hace_tres_dias);
$stmt_eliminar->execute();
$stmt_eliminar->close();

// Activar las ofertas de alquiler que no tienen fecha o tienen la fecha '0000-00-00' y pertenecen a usuarios verificados
$sql = "UPDATE alquileres a INNER JOIN usuarios u ON a.usuario_id = u.id SET a.activa = 1 WHERE u.verificado = 1 AND (a.fecha_inicio = '0000-00-00' OR a.fecha_fin = '0000-00-00')";
if (!mysqli_query($conexion, $sql)) {
    echo "Error al actualizar alquileres sin fecha o con fechas '0000-00-00' de usuarios verificados: " . mysqli_error($conexion);
}

require_once('footer.php');
?>

