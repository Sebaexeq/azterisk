<?php
include('config.php');

// Verificar si el usuario es administrador
session_start();
if (!isset($_SESSION['id']) || $_SESSION['admin'] != 1) {
    header("Location: index.php");
    exit();
}

$usuario_id = $_GET['usuario_id'];

// Obtener la solicitud de verificación del usuario
$query = "SELECT * FROM verificaciones WHERE usuario_id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$solicitud = $result->fetch_assoc();
$stmt->close();

// Procesar la aceptación de la solicitud
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aceptar'])) {
    $fecha_verificacion = $_POST['fecha_verificacion'];

    // Actualizar el estado de verificación y la fecha de verificación del usuario en la tabla 'usuarios'
    $query_update = "UPDATE usuarios SET verificado = 1, fecha_verificacion = ? WHERE id = ?";
    $stmt_update = $conexion->prepare($query_update);
    $stmt_update->bind_param("si", $fecha_verificacion, $usuario_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Eliminar la solicitud de verificación de la tabla 'verificaciones'
    $query_delete = "DELETE FROM verificaciones WHERE usuario_id = ?";
    $stmt_delete = $conexion->prepare($query_delete);
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Redirigir al panel de administración con un mensaje de éxito
    header("Location: admin_panel.php?success=1");
    exit();
}


// Obtener el nombre y apellido del usuario
$query_nombre = "SELECT nombre, apellido FROM usuarios WHERE id = ?";
$stmt_nombre = $conexion->prepare($query_nombre);
$stmt_nombre->bind_param("i", $usuario_id);
$stmt_nombre->execute();
$result_nombre = $stmt_nombre->get_result();
if ($result_nombre->num_rows > 0) {
    $usuario = $result_nombre->fetch_assoc();
} else {
    die("El usuario no existe o hubo un error al obtener los datos.");
}
$stmt_nombre->close();


// Procesar el rechazo de la solicitud
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rechazar'])) {
    // Eliminar la solicitud de verificación de la tabla 'verificaciones'
    $query_delete = "DELETE FROM verificaciones WHERE usuario_id = ?";
    $stmt_delete = $conexion->prepare($query_delete);
    $stmt_delete->bind_param("i", $usuario_id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // Redirigir al panel de administración con un mensaje de éxito
    header("Location: admin_panel.php?rejected=1");
    exit();
}

$fecha_manana = date("Y-m-d", strtotime("+1 day"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ver Solicitud de Verificación</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container mt-4 text-center">
        <h2>Solicitud de Verificación para <?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></h2>
        <div class="row justify-content-center">
            <div class="col-md-3">
                <h5>DNI Frente</h5>
                <img src="uploads/<?php echo $solicitud['dni_frente']; ?>" alt="DNI Frente" class="img-fluid mb-3">
            </div>
            <div class="col-md-3">
                <h5>DNI Dorso</h5>
                <img src="uploads/<?php echo $solicitud['dni_dorso']; ?>" alt="DNI Dorso" class="img-fluid mb-3">
            </div>
            <div class="col-md-3">
                <h5>Selfie</h5>
                <img src="uploads/<?php echo $solicitud['selfie']; ?>" alt="Selfie" class="img-fluid mb-3">
            </div>
        </div>
        <div class="mt-4">
            <form method="post">
                <div class="mb-3">
                    <label for="fecha_verificacion" class="form-label">Fecha de Vencimiento:</label>
                    <input type="date" class="form-control" name="fecha_verificacion" value="<?php echo $fecha_manana; ?>" min="<?php echo $fecha_manana; ?>">
                </div>
                <button type="submit" name="aceptar" class="btn btn-success" onclick="setRequiredFechaVerificacion(true)">Aceptar Solicitud</button>
				<button type="submit" name="rechazar" class="btn btn-danger" onclick="setRequiredFechaVerificacion(false)">Rechazar Solicitud</button>
            </form>
        </div>
    </div>

    <?php include('footer.php'); ?>
	<script>
    function setRequiredFechaVerificacion(required) {
        document.querySelector('input[name="fecha_verificacion"]').required = required;
    }
</script>

</body>
</html>
