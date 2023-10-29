<!DOCTYPE html>
<html>
<head>
	<title>Reservar</title>
	<link href="estilos/estilo.css" rel="stylesheet">
</head>
<?php
session_start();
require_once 'config.php';
include 'header.php';

$usuario_id = $_SESSION["id"];
$alquiler_id = $_GET['id'];

// Verificar si el usuario está verificado
$query_usuario = "SELECT verificado FROM usuarios WHERE id = ?";
$stmt_usuario = $conexion->prepare($query_usuario);
$stmt_usuario->bind_param("i", $usuario_id);
$stmt_usuario->execute();
$resultado = $stmt_usuario->get_result();
$usuario = $resultado->fetch_assoc();
$es_verificado = $usuario['verificado'];
$stmt_usuario->close();

// Obtener detalles del alquiler
$query = "SELECT * FROM alquileres WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $alquiler_id);
$stmt->execute();
$alquiler = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener la fecha de mañana
$hoy = date('Y-m-d');

// Si el dueño del alquiler ha especificado fechas, usar esas fechas como min y max
$fecha_inicio_min = ($alquiler["fecha_inicio"] && $alquiler["fecha_inicio"] > $hoy) ? $alquiler["fecha_inicio"] : $hoy;
$fecha_fin_max = $alquiler["fecha_fin"] ?? '2099-12-31'; // Puedes usar una fecha muy lejana como valor predeterminado si no hay fecha fin

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];
    $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400; // 86400 segundos en un día
	
	// Verifica colisiones con reservas existentes
    $query_colision = "SELECT * FROM aplicaciones_alquiler WHERE usuario_id = ? AND (estado = 'aceptado' OR estado = 'pendiente') AND ((fecha_inicio <= ? AND fecha_fin >= ?) OR (fecha_inicio <= ? AND fecha_fin >= ?) OR (fecha_inicio >= ? AND fecha_fin <= ?))";
    $stmt_colision = $conexion->prepare($query_colision);
    $stmt_colision->bind_param("issssss", $usuario_id, $fecha_inicio, $fecha_inicio, $fecha_fin, $fecha_fin, $fecha_inicio, $fecha_fin);
    $stmt_colision->execute();
    $resultado_colision = $stmt_colision->get_result();

    if ($resultado_colision->num_rows > 0) {
        // Hay una colisión
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-danger text-center" role="alert">Ya tienes una reserva para esas fechas. Por favor, selecciona otras fechas.</div>';
        echo '<div class="text-center"><a href="reservar.php?id=' . $alquiler_id . '" class="btn btn-primary">Volver a reservar</a></div>';
        echo '</div>';
    } else {
        // No hay colisión
	
	// Control de tiempo mínimo y máximo
if ($dias < $alquiler["tiempo_minimo"] || $dias > $alquiler["tiempo_maximo"]) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-danger text-center" role="alert">La duración de la reserva no cumple con los requisitos de permanencia.</div>';
    echo '<div class="text-center" role="alert"><a href="detalles_alquiler.php?id=' . $alquiler_id . '" class="btn btn-primary">Volver a los detalles del alquiler</a></div>';
    echo '</div>';
} else {
    $costo_total = $dias * $alquiler["costo_alquiler"];

    $estado = $es_verificado ? 'aceptado' : 'pendiente';

    $query = "INSERT INTO aplicaciones_alquiler (usuario_id, alquiler_id, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("iisss", $usuario_id, $alquiler_id, $fecha_inicio, $fecha_fin, $estado);
    if ($stmt->execute()) {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-success text-center" role="alert">Tu reserva ha sido enviada exitosamente.</div>';
        echo '<div class="text-center"><a href="detalles_alquiler.php?id=' . $alquiler_id . '" class="btn btn-primary">Volver a los detalles del alquiler</a></div>';
        echo '</div>';
    } else {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-danger" role="alert">Hubo un error al enviar tu reserva. Por favor, inténtalo de nuevo.</div>';
        echo '<a href="detalles_alquiler.php?id=' . $alquiler_id . '" class="btn btn-primary">Volver a los detalles del alquiler</a>';
        echo '</div>';
    }
    $stmt->close();
	}}
} else {
    echo '<div class="container mt-4">';
    echo '<h1 class="mb-4">Reservar alquiler</h1>';
    echo '<form action="reservar.php?id=' . $alquiler_id . '" method="post">';
    echo '<div class="mb-3">';
    echo '<label for="fecha_inicio" class="form-label">Fecha de inicio</label>';
    echo '<input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" min="' . $fecha_inicio_min . '" max="' . $fecha_fin_max . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="fecha_fin" class="form-label">Fecha de fin</label>';
    echo '<input type="date" class="form-control" id="fecha_fin" name="fecha_fin" min="' . $fecha_inicio_min . '" max="' . $fecha_fin_max . '" required>';
    echo '</div>';
    echo '<div id="precio_total" class="text-center"><b>Precio total: $0</div></b><br>'; // Lugar donde se mostrará el precio total
    echo '<div class="text-center"><button type="submit" class="btn btn-primary">Confirmar reserva</button></div>';
    echo '</form>';
    echo '</div>';
}

include 'footer.php';
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    var costoPorDia = <?php echo $alquiler["costo_alquiler"]; ?>;

    function calcularPrecioTotal() {
        var fechaInicio = new Date($('#fecha_inicio').val());
        var fechaFin = new Date($('#fecha_fin').val());

        var diferencia = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24) + 1;

        if (!isNaN(diferencia) && diferencia > 0) {
            var precioTotal = diferencia * costoPorDia;
            $('#precio_total').text('Precio total: $' + precioTotal.toFixed(2));
        } else {
            $('#precio_total').text('Precio total: $0');
        }
    }

    $('#fecha_inicio, #fecha_fin').change(calcularPrecioTotal);
});
</script>
