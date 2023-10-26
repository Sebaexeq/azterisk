<?php
session_start();
require_once 'config.php';
include 'header.php';
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

// Activar las ofertas de alquiler que no tienen fecha o tienen la fecha '0000-00-00' y pertenecen a usuarios verificados
$sql = "UPDATE alquileres a INNER JOIN usuarios u ON a.usuario_id = u.id SET a.activa = 1 WHERE u.verificado = 1 AND (a.fecha_inicio = '0000-00-00' OR a.fecha_fin = '0000-00-00')";
if (!mysqli_query($conexion, $sql)) {
    echo "Error al actualizar alquileres sin fecha o con fechas '0000-00-00' de usuarios verificados: " . mysqli_error($conexion);
}

require_once('footer.php');
?>
