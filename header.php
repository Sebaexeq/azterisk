<!DOCTYPE html>
<html>
<head>
    <title>RapiBnB</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Nueva Paleta de Colores */
        .navbar {
            background-color: #102C57; /* Color más oscuro de la paleta */
        }
        .navbar-brand, .nav-link {
            color: #F8F0E5; /* Color más claro de la paleta */
        }
        .nav-link:hover {
            color: #DAC0A3; /* Tercer color de la paleta */
        }
    </style>
</head>
<body>
    <header>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.php"><img class="navbar-brand" src="logo.png" href="index.php"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="#ffffff" class="bi bi-list" viewBox="0 0 16 16">
							<path fill-rule="evenodd" d="M2.5 11.5A.5.5 0 0 1 3 11h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 7h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4A.5.5 0 0 1 3 3h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
						</svg>
					</span>
				</button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    </ul>
                    <ul class="navbar-nav">
                        <!-- Botón de búsqueda siempre visible -->
                        <li class="nav-item">
                            <a class="nav-link" href="buscador.php">
                                <i class="bi bi-search"></i> Buscar Oferta
                            </a>
                        </li>
                        <?php
                        // Verificar si la sesión está activa
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start(); // Inicia la sesión si no está activa
                        }

                        // Botón "Crear Ofertas de Alquiler" con icono de "+"
                        if (isset($_SESSION["id"])) {
                            echo '<li class="nav-item"><a class="nav-link" href="crear_oferta.php"><i class="bi bi-plus"></i> Crear Oferta</a></li>';
                        }
                        ?>
                    </ul>
                    <ul class="navbar-nav ml-auto">
                        <?php
                        // Botón "Admin" con icono de una tuerca
                        if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) {
                            echo '<li class="nav-item"><a class="nav-link" href="admin_panel.php"><i class="bi bi-gear"></i> Panel de Administración</a></li>';
                        }

                        // Botón de usuario (person) en el lado derecho
                        if (isset($_SESSION["id"])) {
                            ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-person-fill"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="perfil.php"><i class="bi bi-person-badge"></i> Mi Perfil</a>
                                    <a class="dropdown-item" href="editar_perfil.php"><i class="bi bi-pencil-square"></i> Editar Perfil</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a>
                                </div>
                            </li>
                        <?php
                        } else {
                            echo '<li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-door-open"></i> Iniciar sesión</a></li>';
                            echo '<li class="nav-item"><a class="nav-link" href="registro.php"><i class="bi bi-person-plus"></i> Registrarse</a></li>';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Navbar -->
		<?php
		$sql_update = "UPDATE aplicaciones_alquiler SET estado = 'completado' WHERE fecha_fin <= CURDATE() AND estado = 'aceptado'";
		mysqli_query($conexion, $sql_update);
		
		//Quitar verificado si hoy es el día de vencimiento
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
		
		// Desactivar las ofertas de alquiler adicionales de usuarios regulares si ya tienen una oferta activa
		$sql_desactivar = "UPDATE alquileres a1 
                   INNER JOIN (
                       SELECT usuario_id, MAX(fecha_inicio) as latest_start 
                       FROM alquileres 
                       WHERE activa = 1 
                       GROUP BY usuario_id 
                       HAVING COUNT(id) > 1
                   ) a2 
                   ON a1.usuario_id = a2.usuario_id 
                   SET a1.activa = 0 
                   WHERE a1.fecha_inicio != a2.latest_start";

		if (!mysqli_query($conexion, $sql_desactivar)) {
			echo "Error al desactivar ofertas adicionales de usuarios regulares: " . mysqli_error($conexion);
		}
		
		// Activar las ofertas de alquiler de usuarios verificados que están dentro del rango de fechas
		$sql = "UPDATE alquileres a INNER JOIN usuarios u ON a.usuario_id = u.id SET a.activa = 1 WHERE u.verificado = 1 AND CURDATE() BETWEEN a.fecha_inicio AND a.fecha_fin";
		if (!mysqli_query($conexion, $sql)) {
			echo "Error al actualizar alquileres dentro de rango para usuarios verificados: " . mysqli_error($conexion);
		}
		
		// Activar las ofertas de alquiler de usuarios no verificados que han sido publicadas hace más de 3 días hábiles y están dentro del rango de fechas
		$fecha_hace_tres_dias = date('Y-m-d', strtotime("-3 weekdays"));
		$sql = "UPDATE alquileres a INNER JOIN usuarios u ON a.usuario_id = u.id 
			SET a.activa = 1 
			WHERE u.verificado = 0 
			AND a.activa = 0 
			AND DATEDIFF(CURDATE(), a.fecha_publicacion) >= 3 
			AND CURDATE() BETWEEN a.fecha_inicio AND a.fecha_fin";
		
		$stmt = $conexion->prepare($sql);
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
		
		// Activar las ofertas de alquiler que no tienen fecha o tienen la fecha '0000-00-00' en usuarios regulares
		$sql = "UPDATE alquileres a 
        INNER JOIN usuarios u ON a.usuario_id = u.id
        SET a.activa = 1 
        WHERE (a.fecha_inicio = '0000-00-00' OR a.fecha_fin = '0000-00-00') 
        AND DATEDIFF(CURDATE(), a.fecha_publicacion) >= 3
        AND u.verificado = 0
        AND NOT EXISTS (
            SELECT 1 FROM alquileres b 
            WHERE b.usuario_id = a.usuario_id AND b.activa = 1 AND b.id != a.id
        )";
		if (!mysqli_query($conexion, $sql)) {
			echo "Error al actualizar alquileres sin fecha o con fechas '0000-00-00': " . mysqli_error($conexion);
		}
		?>
    </header>
</body>
</html>
