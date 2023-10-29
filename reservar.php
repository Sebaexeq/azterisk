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
$fecha_manana = date('Y-m-d', strtotime('+1 day'));

// Si el dueño del alquiler ha especificado fechas, usar esas fechas como min y max
$fecha_inicio_min = ($alquiler["fecha_inicio"] && $alquiler["fecha_inicio"] > $fecha_manana) ? $alquiler["fecha_inicio"] : $fecha_manana;
$fecha_fin_max = $alquiler["fecha_fin"] ?? '2099-12-31'; // Puedes usar una fecha muy lejana como valor predeterminado si no hay fecha fin

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fecha_inicio = $_POST["fecha_inicio"];
    $fecha_fin = $_POST["fecha_fin"];
    $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400; // 86400 segundos en un día
    $costo_total = $dias * $alquiler["costo_alquiler"];

    $estado = $es_verificado ? 'aceptado' : 'pendiente';

    $query = "INSERT INTO aplicaciones_alquiler (usuario_id, alquiler_id, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("iisss", $usuario_id, $alquiler_id, $fecha_inicio, $fecha_fin, $estado);
    if ($stmt->execute()) {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-success" role="alert">Tu reserva ha sido enviada exitosamente.</div>';
        echo '<a href="detalles_alquiler.php?id=' . $alquiler_id . '" class="btn btn-primary">Volver a los detalles del alquiler</a>';
        echo '</div>';
    } else {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-danger" role="alert">Hubo un error al enviar tu reserva. Por favor, inténtalo de nuevo.</div>';
        echo '<a href="detalles_alquiler.php?id=' . $alquiler_id . '" class="btn btn-primary">Volver a los detalles del alquiler</a>';
        echo '</div>';
    }
    $stmt->close();
} else {
    echo '<div class="container mt-4">';
    echo '<h2>Reservar alquiler</h2>';
    echo '<form action="reservar.php?id=' . $alquiler_id . '" method="post">';
    echo '<div class="mb-3">';
    echo '<label for="fecha_inicio" class="form-label">Fecha de inicio</label>';
    echo '<input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" min="' . $fecha_inicio_min . '" max="' . $fecha_fin_max . '" required>';
    echo '</div>';
    echo '<div class="mb-3">';
    echo '<label for="fecha_fin" class="form-label">Fecha de fin</label>';
    echo '<input type="date" class="form-control" id="fecha_fin" name="fecha_fin" min="' . $fecha_inicio_min . '" max="' . $fecha_fin_max . '" required>';
    echo '</div>';
    echo '<div id="precio_total">Precio total: $0</div>'; // Lugar donde se mostrará el precio total
    echo '<button type="submit" class="btn btn-primary">Confirmar reserva</button>';
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
