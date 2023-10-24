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

// Incluyendo la conexión a la base de datos
include 'config.php';

// Consulta para obtener la información del usuario
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

$sql_alquileres = "
    SELECT a.*, AVG(r.puntuacion) as avg_puntuacion
    FROM alquileres a
    LEFT JOIN resenia r ON a.id = r.id_oferta
    WHERE a.usuario_id = ?
    GROUP BY a.id";
$stmt_alquileres = $conexion->prepare($sql_alquileres);
$stmt_alquileres->bind_param("i", $id_usuario_mostrar);
$stmt_alquileres->execute();
$ofertas = $stmt_alquileres->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Perfil de Usuario</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            border-radius: 15px 15px 0 0;
        }
        .img-fluid {
            border: 3px solid #f4f4f4;
        }
        h2, h4, h5 {
            font-weight: 500;
        }
		.card-header {
		background-color: #DAC0A3 !important;
		}
		.container-verification {
    font-family: Arial, sans-serif;
    max-width: 300px;
    margin: auto;
}


.upload-box {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 50px;
    border: 2px dashed #c1c1c1;
    border-radius: 5px;
    margin-bottom: 15px;
    position: relative;
    cursor: pointer;
    transition: border-color 0.2s;
}

.upload-box:hover {
    border-color: #8a8a8a;
}

.upload-label {
    z-index: 1;
}

.upload-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    color: #c1c1c1;
    font-size: 18px;
}

.upload-input {
    opacity: 0;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    z-index: 0;
}

div.card-body .btn-enviar {
    background-color: #DAC0A3;
    color: #FFFFFF;
    border: none;
}

div.card-body .btn-enviar:hover {
    background-color: #C2B093;
}
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
    <div class="row">
        <!-- Tarjeta del lado izquierdo (foto de perfil) -->
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

	<!-- Formulario de verificación -->
	<div class="container-verification">
		<?php if ($usuario["verificado"] == 0): ?>
			<div class="card mt-4">
				<div class="card-header text-white text-center">
					<b>Verifica tu cuenta</b>
				</div>
				<div class="card-body">
					<form action="procesar_verificacion.php" method="post" enctype="multipart/form-data">
						<div class="upload-box mb-3">
							<label class="upload-label" for="dni_frente">Foto Frente DNI</label>
							<span class="upload-icon">📎</span>
							<input type="file" id="dni_frente" name="dni_frente" class="upload-input">
						</div>
						
						<div class="upload-box mb-3">
							<label class="upload-label" for="dni_dorso">Foto Dorso DNI</label>
							<span class="upload-icon">📎</span>
							<input type="file" id="dni_dorso" name="dni_dorso" class="upload-input">
						</div>
						
						<div class="upload-box mb-3">
							<label class="upload-label" for="selfie">Selfie</label>
							<span class="upload-icon">📸</span>
							<input type="file" id="selfie" name="selfie" class="upload-input">
						</div>
						<div class="text-center">
						<button type="submit" class="btn btn-primary btn-enviar">Enviar Documentación</button>
						</div>
					</form>
				</div>
			</div><br>
			<?php
        // Muestra el mensaje de error o éxito
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
        }

        if (isset($_GET['mensaje'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['mensaje']) . '</div>';
        }
        ?>
		<?php endif; ?>
		</div>
		</div>
        <!-- Tarjeta del lado derecho (perfil de usuario y ofertas de alquiler) -->
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
                        <h5>Biografía</h5>
                        <p><?php echo $bio; ?></p>
                    </div>
                </div>
            </div>

            <div class="card"> 
                <div class="card-header text-white bg-personalizado">
                    <h2>Ofertas de Alquiler</h2>
                </div>
                <div class="card-body">
					<?php if ($ofertas->num_rows > 0): ?>
						<div class="list-group">
							<?php while ($oferta = $ofertas->fetch_assoc()): ?>
								<a href="detalles_alquiler.php?id=<?php echo $oferta["id"]; ?>" class="list-group-item list-group-item-action mb-0">
									<div class="d-flex w-100 justify-content-between">
										<h5 class="mb-1"><?php echo $oferta["titulo"]; ?></h5>
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

