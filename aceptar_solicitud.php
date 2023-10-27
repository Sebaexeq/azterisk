<?php
require_once('config.php');
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $solicitud_id = $_GET['id'];

    // Obtener el ID de la oferta relacionada con la solicitud
    $query_oferta = "SELECT alquiler_id FROM aplicaciones_alquiler WHERE id = ?";
    $stmt_oferta = $conexion->prepare($query_oferta);
    $stmt_oferta->bind_param("i", $solicitud_id);
    $stmt_oferta->execute();
    $result_oferta = $stmt_oferta->get_result();
    $row_oferta = $result_oferta->fetch_assoc();
    $id_oferta = $row_oferta['alquiler_id'];
    $stmt_oferta->close();

    $query = "UPDATE aplicaciones_alquiler SET estado = 'aceptado' WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $solicitud_id);
    if ($stmt->execute()) {
        header("Location: detalles_alquiler.php?id=" . $id_oferta); // Redirige de nuevo a detalles_alquiler.php
        exit();
    } else {
        echo "Error al aceptar la solicitud.";
    }
    $stmt->close();
}
?>
