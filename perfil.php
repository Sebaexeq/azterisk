<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

$id_usuario_mostrar = $_SESSION["id"];

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id_usuario_mostrar = $_GET["id"];
}

include 'config.php';

// Consulta para obtener la informaci贸n del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario_mostrar);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if ($usuario) {
    $nombre = $usuario["nombre"];
    $apellido = $usuario["apellido"];
    $foto_perfil = $usuario["foto_perfil"];
    $intereses = $usuario["intereses"];
    $bio = $usuario["bio"];
    $es_admin = $usuario["admin"];
} else {
    echo "Usuario no encontrado";
    exit;
}
$condicion_activa = ($id_usuario_mostrar != $_SESSION["id"]) ? "AND a.activa = 1" : "";
$sql_alquileres = "
    SELECT a.*, AVG(r.puntuacion) as avg_puntuacion
    FROM alquileres a
    LEFT JOIN resenia r ON a.id = r.id_oferta
    WHERE a.usuario_id = ? $condicion_activa
    GROUP BY a.id
	ORDER BY a.fecha_publicacion DESC";
$stmt_alquileres = $conexion->prepare($sql_alquileres);
$stmt_alquileres->bind_param("i", $id_usuario_mostrar);
$stmt_alquileres->execute();
$ofertas = $stmt_alquileres->get_result();

$sql_reservas = "
    SELECT aa.*, a.titulo 
    FROM aplicaciones_alquiler aa
    JOIN alquileres a ON aa.alquiler_id = a.id
    WHERE aa.usuario_id = ?
    ORDER BY aa.fecha_inicio DESC";
$stmt_reservas = $conexion->prepare($sql_reservas);
$stmt_reservas->bind_param("i", $id_usuario_mostrar);
$stmt_reservas->execute();
$reservas = $stmt_reservas->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Perfil de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="estilos/perfil.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
    <div class="row">
		<div class="col-md-3">
			<div class="card"> 
				<div class="card-body text-center">
					<img src="<?php echo $foto_perfil; ?>" alt="Foto de Perfil" class="img-fluid rounded-circle mb-3" width="150">
					<h4><?php echo $nombre . ' ' . $apellido; ?></h4>
					<?php 
					echo ($usuario["verificado"] == 1) ? '<img height=40 width=40 src="verified.png" title="Usuario verificado">' : '';
					echo ($es_admin == 1) ? ' <img height=40 width=40 src="admin.png" title="Administrador">' : ''; 
					?>
				</div>
			</div>
	<?php
	// Consulta para verificar si el usuario tiene una solicitud de verificaci贸n pendiente
	$query_verificacion = "SELECT * FROM verificaciones WHERE usuario_id = ?";
	$stmt_verificacion = $conexion->prepare($query_verificacion);
	$stmt_verificacion->bind_param("i", $id_usuario_mostrar);
	$stmt_verificacion->execute();
	$result_verificacion = $stmt_verificacion->get_result();
	$esperando_verificacion = ($result_verificacion->num_rows > 0);
	$stmt_verificacion->close();
	?>
	<!-- Formulario de verificaci贸n -->
	<?php if ($id_usuario_mostrar == $_SESSION["id"]): ?>
	<div class="container-verification">
		<?php if ($usuario["verificado"] == 0): ?>
			<?php if ($esperando_verificacion): ?>
				
				<div class="text-center"><div class="alert alert-info mt-4">Esperando verificaci贸n.</div></div>
			<?php else: ?>
			<div class="card mt-4">
				<div class="card-header text-white text-center">
					<b>Verifica tu cuenta</b>
				</div>
				<div class="card-body">
					<form action="procesar_verificacion.php" method="post" enctype="multipart/form-data">
						<div class="upload-box mb-3">
							<label class="upload-label" for="dni_frente">Foto Frente DNI</label>
							<span class="upload-icon">馃搸</span>
							<input type="file" id="dni_frente" name="dni_frente" class="upload-input">
						</div>
						
						<div class="upload-box mb-3">
							<label class="upload-label" for="dni_dorso">Foto Dorso DNI</label>
							<span class="upload-icon">馃搸</span>
							<input type="file" id="dni_dorso" name="dni_dorso" class="upload-input">
						</div>
						
						<div class="upload-box mb-3">
							<label class="upload-label" for="selfie">Selfie</label>
							<span class="upload-icon">馃摳</span>
							<input type="file" id="selfie" name="selfie" class="upload-input">
						</div>
						<div class="text-center">
						<button type="submit" class="btn btn-primary btn-enviar">Enviar Documentaci贸n</button>
						</div>
					</form>
				</div>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			<br>
		<?php
        // Muestra el mensaje de error o 茅xito
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
        }

        if (isset($_GET['mensaje'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['mensaje']) . '</div>';
        }
        ?>
		
		</div>
		<?php endif; ?>

		</div>
        <div class="col-md-9">
            <div class="card mb-4"> 
                <div class="card-header bg-personalizado text-white">
                    <h2>Perfil de Usuario</h2>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5>Intereses</h5>
                        <p><?php echo $intereses; ?></p>
                    </div>
                    <div>
                        <h5>Biograf铆a</h5>
                        <p><?php echo $bio; ?></p>
                    </div>
                </div>
            </div>

<?php if ($id_usuario_mostrar == $_SESSION["id"]): ?>
				<div class="card mb-4"> 
					<div class="card-header bg-personalizado text-white">
						<h2>Mis Reservas</h2>
					</div>
					<div class="card-body">
						<?php if ($reservas->num_rows > 0): ?>
							<div class="list-group">
								<?php while ($reserva = $reservas->fetch_assoc()): ?>
									<a href="detalles_alquiler.php?id=<?php echo $reserva["alquiler_id"]; ?>" class="list-group-item list-group-item-action mb-0">
										<div class="d-flex w-100 justify-content-between">
											<h5 class="mb-1"><?php echo $reserva["titulo"]; ?></h5>
											<p>Inicio: <?php echo $reserva["fecha_inicio"]; ?> - Fin: <?php echo $reserva["fecha_fin"]; ?></p>
											<small>
												<?php 
												switch ($reserva["estado"]) {
													case "completado":
														echo '<span class="badge bg-success">Completada</span>';
														break;
													case "aceptado":
														echo '<span class="badge bg-primary">Aceptada</span>';
														break;
													case "pendiente":
														echo '<span class="badge bg-warning">Pendiente</span>';
														break;
												}
												?>
											</small>
										</div>
										
									</a>
								<?php endwhile; ?>
							</div>
						<?php else: ?>
							<p>No tiene reservas.</p>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		
            <div class="card"> 
                <div class="card-header text-white bg-personalizado">
                    <h2>Ofertas de Alquiler</h2>
                </div>
                <div class="card-body">
					<?php if ($ofertas->num_rows > 0): ?>
						<div class="list-group">
							<?php while ($oferta = $ofertas->fetch_assoc()): ?>
							<?php
							$oferta_id = $oferta["id"];
							$query_solicitudes = "SELECT estado, COUNT(*) as cantidad FROM aplicaciones_alquiler WHERE alquiler_id = ? GROUP BY estado";
							$stmt_solicitudes = $conexion->prepare($query_solicitudes);
							$stmt_solicitudes->bind_param("i", $oferta_id);
							$stmt_solicitudes->execute();
							$resultado_solicitudes = $stmt_solicitudes->get_result();
							$solicitudes = [];
							while ($row = $resultado_solicitudes->fetch_assoc()) {
								$solicitudes[$row['estado']] = $row['cantidad'];
							}
							$stmt_solicitudes->close();
							?>

								<a href="detalles_alquiler.php?id=<?php echo $oferta["id"]; ?>" class="list-group-item list-group-item-action mb-0">
									<div class="d-flex w-100 justify-content-between">
										<h5 class="mb-1">
											<?php echo $oferta["titulo"]; ?>
											<!-- Muestra el badge si la oferta est谩 inactiva y el usuario es el due帽o del perfil -->
											<?php if ($oferta["activa"] == 0 && $id_usuario_mostrar == $_SESSION["id"]): ?>
												<span class="badge bg-secondary">Inactiva</span>
											<?php endif; ?>
											<?php
											if (isset($solicitudes['pendiente']) && $id_usuario_mostrar == $_SESSION["id"]) {
												echo '<span class="badge bg-warning">' . $solicitudes['pendiente'] . ' pendientes</span> ';
											}
											
											
											if (isset($solicitudes['aceptado']) && $id_usuario_mostrar == $_SESSION["id"]) {
												echo '<span class="badge bg-success">' . $solicitudes['aceptado'] . ' aceptados</span> ';
											}?>
										</h5>
										<small>
											<?php 
											$estrellas = round($oferta["avg_puntuacion"]);
											for ($i = 1; $i <= 5; $i++): 
												if ($i <= $estrellas): ?>
													<img src="estrella.png" alt="Estrella">
												<?php else: ?>
													<img src="noestrella.png" alt="No Estrella">
												<?php endif; 
											endfor; ?>
										</small>
									</div>
								</a>
							<?php endwhile; ?>
						</div>
					<?php else: ?>
						<p>No tiene ofertas de alquiler publicadas.</p>
					<?php endif; ?>
				</div>
			</div>	
        </div>
    </div>
</div>
    <?php require_once('footer.php'); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
