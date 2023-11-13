<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/x-icon" href="favicon.ico">
    <title>Detalles de la Oferta de Alquiler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="estilos/estilo.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
	<style>
		a {
			text-decoration: none !important;
		}
	</style>
</head>
<body>

<?php
require_once('config.php');
require_once('header.php');
$id_oferta = null;

// Obtenemos el ID del alquiler desde el parámetro GET
$id_alquiler = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(isset($_SESSION['id'])) {
	$usuario_id = $_SESSION['id'];
	// Consultamos si el usuario está verificado, si tiene una oferta activa y las fechas de inicio y fin del alquiler
	$query = "SELECT u.verificado, a.fecha_publicacion, a.fecha_inicio, a.fecha_fin, a.activa 
			FROM alquileres a 
			JOIN usuarios u ON a.usuario_id = u.id 
			WHERE a.id = $id_alquiler";
	
	$resultado = mysqli_query($conexion, $query);
	
	if ($resultado && mysqli_num_rows($resultado) > 0) {
		$fila = mysqli_fetch_assoc($resultado);
		
		// Si el usuario es regular
		if ($fila['verificado'] == 0) {
			// Verificamos si el usuario ya tiene una oferta activa
			$queryOfertaActiva = "SELECT COUNT(*) as total_activas FROM alquileres WHERE usuario_id = $usuario_id AND activa = 1";
			$resultadoOfertaActiva = mysqli_query($conexion, $queryOfertaActiva);
			$filaOfertaActiva = mysqli_fetch_assoc($resultadoOfertaActiva);
			
			if ($filaOfertaActiva['total_activas'] > 0 && $fila['activa'] == 0) {
				echo "<div class='alert alert-danger text-center'>La oferta de alquiler está inactiva porque ya tienes una oferta de alquiler activa.</div>";
			}
		}
		
		$fecha_publicacion = new DateTime($fila['fecha_publicacion']);
		$fecha_actual = new DateTime();
		$diferencia = $fecha_actual->diff($fecha_publicacion);
		
		$fecha_inicio = isset($fila['fecha_inicio']) ? new DateTime($fila['fecha_inicio']) : null;
		$fecha_fin = isset($fila['fecha_fin']) ? new DateTime($fila['fecha_fin']) : null;
	
		if ($diferencia->days < 3 && $fila['verificado'] == 0) {
			echo "<div class='alert alert-warning text-center'>Tu alquiler está inactivo porque aún no han pasado 3 días desde su fecha de publicación.</div>";
		} elseif ($fila['activa'] == 0 && $fecha_inicio && $fecha_fin && ($fecha_actual < $fecha_inicio || $fecha_actual > $fecha_fin)) {
		echo "<div class='alert alert-secondary text-center'>Tu oferta de alquiler está inactiva porque su rango de actividad no coincide con el de hoy.</div>";
	}
	} else {
		echo "Error al obtener la información del alquiler o del usuario: " . mysqli_error($conexion);
	}
}
// Función para mostrar las estrellas
function mostrarEstrellas($puntuacion) {
    $estrellas = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $puntuacion) {
            $estrellas .= '<img src="estrella.png" alt="Estrella completa">';
        } else {
            $estrellas .= '<img src="noestrella.png" alt="Estrella incompleta">';
        }
    }
    return $estrellas;
}

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id_oferta = $_GET["id"];
    $yaHaResenado = false;

    // Verificar si el usuario ya ha realizado una reseña en esta oferta
    $sql_verificar_resena = "SELECT COUNT(*) FROM resenia WHERE id_oferta = ? AND id_usuario = ?";
    if ($stmt_verificar_resena = mysqli_prepare($conexion, $sql_verificar_resena)) {
        mysqli_stmt_bind_param($stmt_verificar_resena, "ii", $id_oferta, $_SESSION['id']);
        if (mysqli_stmt_execute($stmt_verificar_resena)) {
            mysqli_stmt_bind_result($stmt_verificar_resena, $num_resenas);
            mysqli_stmt_fetch($stmt_verificar_resena);
            mysqli_stmt_close($stmt_verificar_resena);

            if ($num_resenas > 0) {
                // El usuario ya ha realizado una reseña, mostrar un mensaje
                $yaHaResenado = true;
            }
        }
    }

	// Verificar si el usuario ya ha realizado una reseña en esta oferta y si está verificado
$sql_verificar_resena_y_usuario = "SELECT COUNT(resenia.id) 
    FROM resenia 
    INNER JOIN usuarios ON resenia.id_usuario = usuarios.id 
    WHERE resenia.id_oferta = ? AND resenia.id_usuario = ? AND usuarios.verificado = 1";

$yaHaResenado = false;
$puedeResenar = false;
if ($stmt_verificar_resena_y_usuario = mysqli_prepare($conexion, $sql_verificar_resena_y_usuario)) {
    mysqli_stmt_bind_param($stmt_verificar_resena_y_usuario, "ii", $id_oferta, $_SESSION['id']);
    if (mysqli_stmt_execute($stmt_verificar_resena_y_usuario)) {
        mysqli_stmt_bind_result($stmt_verificar_resena_y_usuario, $num_resenas);
        mysqli_stmt_fetch($stmt_verificar_resena_y_usuario);
        if ($num_resenas > 0) {
            // El usuario ya ha realizado una reseña, mostrar un mensaje
            $yaHaResenado = true;
        }
        mysqli_stmt_close($stmt_verificar_resena_y_usuario);
    }
}

// Verificar si el usuario puede dejar una reseña basado en la fecha de finalización y si está verificado
if (!$yaHaResenado) {
    $query_puede_resenar = "SELECT COUNT(*) 
        FROM aplicaciones_alquiler 
        INNER JOIN usuarios ON aplicaciones_alquiler.usuario_id = usuarios.id 
        WHERE aplicaciones_alquiler.usuario_id = ? AND aplicaciones_alquiler.alquiler_id = ? 
        AND aplicaciones_alquiler.fecha_fin <= CURDATE() AND usuarios.verificado = 1";

    if ($stmt_puede_resenar = mysqli_prepare($conexion, $query_puede_resenar)) {
        mysqli_stmt_bind_param($stmt_puede_resenar, "ii", $_SESSION['id'], $id_oferta);
        if (mysqli_stmt_execute($stmt_puede_resenar)) {
            mysqli_stmt_bind_result($stmt_puede_resenar, $num_aplicaciones);
            mysqli_stmt_fetch($stmt_puede_resenar);
            // Si hay al menos una aplicación que cumpla las condiciones, el usuario puede dejar una reseña
            $puedeResenar = ($num_aplicaciones > 0);
            mysqli_stmt_close($stmt_puede_resenar);
        }
    }
}


    // Si se presiona el botón "Eliminar Reseña"
    if (isset($_GET["action"]) && $_GET["action"] == "deleteReview" && isset($_GET["reviewId"]) && is_numeric($_GET["reviewId"])) {
        $reviewId = $_GET["reviewId"];
        $sql_delete_review = "DELETE FROM resenia WHERE id = ? AND id_usuario = ?";
        if ($stmt_delete_review = mysqli_prepare($conexion, $sql_delete_review)) {
            mysqli_stmt_bind_param($stmt_delete_review, "ii", $reviewId, $_SESSION['id']);
            if (mysqli_stmt_execute($stmt_delete_review)) {
                echo '<script>window.location.href = "detalles_alquiler.php?id=' . $id_oferta . '";</script>';
                exit();
            } else {
                echo '<div class="alert alert-danger" role="alert">Error al eliminar la reseña: ' . mysqli_error($conexion) . '</div>';
            }
            mysqli_stmt_close($stmt_delete_review);
        }
    }

    // Si se presiona el botón "Eliminar Oferta"
    if (isset($_GET["action"]) && $_GET["action"] == "delete") {
        $sql_delete_offer = "DELETE FROM alquileres WHERE id = ?";
        if ($stmt_delete_offer = mysqli_prepare($conexion, $sql_delete_offer)) {
            mysqli_stmt_bind_param($stmt_delete_offer, "i", $id_oferta);
            if (mysqli_stmt_execute($stmt_delete_offer)) {
                echo '<script>window.location.href = "index.php";</script>';
                exit();
            } else {
                echo '<div class="alert alert-danger" role="alert">Error al eliminar la oferta: ' . mysqli_error($conexion) . '</div>';
            }
            mysqli_stmt_close($stmt_delete_offer);
        }
    }

	// Código para calcular la puntuación general
    $sql_puntuacion = "SELECT AVG(puntuacion) as promedio FROM resenia WHERE id_oferta = ?";
    $puntuacion_general = 0;
    if ($stmt_puntuacion = mysqli_prepare($conexion, $sql_puntuacion)) {
        mysqli_stmt_bind_param($stmt_puntuacion, "i", $id_oferta);
        if (mysqli_stmt_execute($stmt_puntuacion)) {
            $resultado_puntuacion = mysqli_stmt_get_result($stmt_puntuacion);
            $fila_puntuacion = mysqli_fetch_assoc($resultado_puntuacion);
            $puntuacion_general = round($fila_puntuacion['promedio']);
        }
        mysqli_stmt_close($stmt_puntuacion);
    }

    // Código para mostrar detalles de la oferta y reseñas
    $sql = "SELECT a.*, u.id AS usuario_id FROM alquileres a
            INNER JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.id = ?";
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_oferta);
        if (mysqli_stmt_execute($stmt)) {
            $resultado = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($resultado) == 1) {
                $fila = mysqli_fetch_assoc($resultado);

                $esPropietario = false;
                if (isset($_SESSION['id']) && $_SESSION['id'] == $fila['usuario_id']) {
                    $esPropietario = true;
                }

				// Verificar si la oferta está inactiva y el usuario no es el propietario
				if ($fila['activa'] == 0 && !$esPropietario) {
					echo '<div class="container mt-4">';
					echo '<div class="alert alert-danger" role="alert">Esta oferta de alquiler a la que intentas acceder está inactiva.</div>';
					echo '</div>';
					include('footer.php');
					exit();
				}

                echo '<div class="container mt-4">';
				echo '<h1>' . htmlspecialchars($fila["titulo"]) . '</h1>';
				echo '<p><strong>Puntuación general:</strong> ' . mostrarEstrellas($puntuacion_general) . '</p>'; // Mostrar puntuación general
				echo '<p><strong>Descripción:</strong> ' . htmlspecialchars($fila["descripcion"]) . '</p>';
                echo '<p><strong>Ubicación:</strong> ' . htmlspecialchars($fila["ubicacion"]) . '</p>';
                $etiquetas = explode(',', $fila["etiquetas"]);
				echo '<p><strong>Etiquetas:</strong> ';
				foreach ($etiquetas as $q) {
					$q = trim($q);
					echo '<a href="buscador.php?q=' . urlencode($q) . '" class="q">#' . htmlspecialchars($q) . '</a> ';
				}
				echo '</p>';
                echo '<p><strong>Costo de Alquiler por Día:</strong> $' . number_format($fila["costo_alquiler"], 2) . '</p>';
                echo '<p><strong>Tiempo Mínimo de Permanencia:</strong> ' . $fila["tiempo_minimo"] . ' días</p>';
                echo '<p><strong>Tiempo Máximo de Permanencia:</strong> ' . $fila["tiempo_maximo"] . ' días</p>';
                echo '<p><strong>Cupo de Personas:</strong> ' . $fila["cupo"] . '</p>';
				// Mostrar servicios incluidos
                $servicios_incluidos = json_decode($fila['servicios'], true);
                echo '<h3>Servicios incluidos:</h3>';
                echo '<ul>';
                if (!empty($servicios_incluidos)) {
                    foreach ($servicios_incluidos as $servicio) {
                        echo "<li>" . htmlspecialchars($servicio) . "</li>";
                    }
                } else {
                    echo "<li>No hay servicios incluidos.</li>";
                }
                echo '</ul>';

				// Verificar si el usuario actual es el propietario de la oferta
				if ($esPropietario) {
					// Obtener las solicitudes pendientes relacionadas con este alquiler
					$query_solicitudes = "SELECT aplicaciones_alquiler.id, usuarios.nombre AS nombre_solicitante, aplicaciones_alquiler.fecha_aplicacion, aplicaciones_alquiler.fecha_inicio, aplicaciones_alquiler.fecha_fin FROM aplicaciones_alquiler 
										INNER JOIN usuarios ON aplicaciones_alquiler.usuario_id = usuarios.id 
										WHERE aplicaciones_alquiler.alquiler_id = ? AND aplicaciones_alquiler.estado = 'pendiente'";
					$stmt_solicitudes = $conexion->prepare($query_solicitudes);
					$stmt_solicitudes->bind_param("i", $id_oferta);
					$stmt_solicitudes->execute();
					$result_solicitudes = $stmt_solicitudes->get_result();
				
					echo "<h2>Solicitudes pendientes</h2>";
				
					if ($result_solicitudes->num_rows > 0) {
						echo "<table class='table'>";
						echo "<thead><tr><th>Nombre del solicitante</th><th>Fecha de solicitud</th><th>Fecha de ingreso</th><th>Fecha de salida</th><th>Acciones</th></tr></thead>";
						echo "<tbody>";
				
						while ($row_solicitud = $result_solicitudes->fetch_assoc()) {
							echo "<tr>";
							echo "<td>" . htmlspecialchars($row_solicitud['nombre_solicitante']) . "</td>";
							echo "<td>" . htmlspecialchars($row_solicitud['fecha_aplicacion']) . "</td>";
							echo "<td>" . htmlspecialchars($row_solicitud['fecha_inicio']) . "</td>";
							echo "<td>" . htmlspecialchars($row_solicitud['fecha_fin']) . "</td>";
							echo "<td>";
							echo "<a href='aceptar_solicitud.php?id=" . $row_solicitud['id'] . "' class='btn btn-success'>Aceptar</a> ";
							echo "<a href='rechazar_solicitud.php?id=" . $row_solicitud['id'] . "' class='btn btn-danger'>Rechazar</a>";
							echo "</td>";
							echo "</tr>";
						}
				
						echo "</tbody>";
						echo "</table>";
					} else {
						echo "<p>No hay solicitudes pendientes.</p>";
					}
				
					$stmt_solicitudes->close();
				}
				
				
				// Verificar si el usuario actual es el propietario de la oferta
				if ($esPropietario) {
					// Obtener las solicitudes aceptadas relacionadas con este alquiler
					$query_solicitudes = "SELECT aplicaciones_alquiler.id, usuarios.nombre AS nombre_solicitante, aplicaciones_alquiler.fecha_aplicacion, aplicaciones_alquiler.fecha_inicio, aplicaciones_alquiler.fecha_fin FROM aplicaciones_alquiler 
										INNER JOIN usuarios ON aplicaciones_alquiler.usuario_id = usuarios.id 
										WHERE aplicaciones_alquiler.alquiler_id = ? AND aplicaciones_alquiler.estado = 'aceptado'";
					$stmt_solicitudes = $conexion->prepare($query_solicitudes);
					$stmt_solicitudes->bind_param("i", $id_oferta);
					$stmt_solicitudes->execute();
					$result_solicitudes = $stmt_solicitudes->get_result();
				
					echo "<h2>Solicitudes aceptadas</h2>";
				
					if ($result_solicitudes->num_rows > 0) {
						echo "<table class='table'>";
						echo "<thead><tr><th>Nombre del solicitante</th><th>Fecha de solicitud</th><th>Fecha de ingreso</th><th>Fecha de salida</th></tr></thead>";
						echo "<tbody>";
				
						while ($row_solicitud = $result_solicitudes->fetch_assoc()) {
							echo "<tr>";
							echo "<td>" . htmlspecialchars($row_solicitud['nombre_solicitante']) . "</td>";
							echo "<td>" . htmlspecialchars($row_solicitud['fecha_aplicacion']) . "</td>";
							echo "<td>" . htmlspecialchars($row_solicitud['fecha_inicio']) . "</td>";
							echo "<td>" . htmlspecialchars($row_solicitud['fecha_fin']) . "</td>";
							echo "</tr>";
						}
				
						echo "</tbody>";
						echo "</table>";
					} else {
						echo "<p>No hay solicitudes pendientes.</p>";
					}
				
					$stmt_solicitudes->close();
				}


                echo '</div>';
                echo '</div>';
				
				

				
				$usuario_id = $_SESSION['id'];
				$alquiler_id = $id_oferta;

				
				// Verificar si el usuario está logueado y no es el propietario de la oferta
				if (isset($_SESSION['id']) && !$esPropietario) {
					
					// Verificar si el usuario ya ha solicitado una reserva para este alquiler
					$query = "SELECT id, estado, fecha_fin FROM aplicaciones_alquiler WHERE usuario_id = ? AND alquiler_id = ? AND (estado = 'pendiente' OR estado = 'aceptado')";
					$stmt_reserva = $conexion->prepare($query);
					$stmt_reserva->bind_param("ii", $usuario_id, $alquiler_id);
					$stmt_reserva->execute();
					$reserva_existente = $stmt_reserva->get_result()->fetch_assoc();
					$stmt_reserva->close();
					
					echo '<div class="mt-4 text-center">';
					
					if (!$reserva_existente) {
						// Muestra el botón "Reservar"
						echo '<a href="reservar.php?id=' . $id_oferta . '" class="btn btn-success">Reservar</a>';
					} else {
						if ($reserva_existente["estado"] == "pendiente") {
							echo '<div class="alert alert-warning">Tu alquiler está pendiente de aceptación.</div>';
						} elseif ($reserva_existente["estado"] == "aceptado") {
							echo '<div class="alert alert-success">Tu reserva fue aceptada.</div>';
						}
					}
				}
					echo '</div>';


				
                echo '<div class="container mt-4 text-center">';
                echo '<div class="btn-group" role="group" aria-label="Botones">';

                if ($esPropietario) {
                    echo '<a href="modificar_oferta.php?id=' . $id_oferta . '" class="btn btn-primary">Modificar Oferta</a>';
                    echo '<button type="button" class="btn btn-danger eliminar-oferta-button" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="' . $id_oferta . '">Eliminar Oferta</button>';
                }

                echo '<a href="perfil.php?id=' . $fila['usuario_id'] . '" class="btn btn-primary">Visitar Perfil del Usuario</a>';
                echo '</div>';
                echo '</div>';

                $galeria_fotos = json_decode($fila["galeria_fotos"]);
                if (!empty($galeria_fotos)) { ?>
                    <div class="container mt-4">
						<h2>Galería de Fotos</h2>
						<div id="fotoCarousel" class="carousel slide" data-bs-ride="carousel">
							<div class="carousel-inner">
								<?php
								$primer = true;
								foreach ($galeria_fotos as $key => $foto) {
									echo '<div class="carousel-item';
									if ($primer) {
										echo ' active';
										$primer = false;
									}
									echo '">';
									echo '<img src="' . htmlspecialchars($foto) . '" class="d-block w-100" alt="Foto">';
									echo '</div>';
								}
								?>
							</div>
							<a class="carousel-control-prev" href="#fotoCarousel" role="button" data-bs-slide="prev">
								<span class="carousel-control-prev-icon" aria-hidden="true"></span>
								<span class="visually-hidden">Previous</span>
							</a>
							<a class="carousel-control-next" href="#fotoCarousel" role="button" data-bs-slide="next">
								<span class="carousel-control-next-icon" aria-hidden="true"></span>
								<span class="visually-hidden">Next</span>
							</a>
						</div>
					</div>
                <?php }

                echo '<div id="reseñas" class="container mt-4">';
                echo '<h2>Reseñas</h2>';

                $sql_resenas = "SELECT r.*, u.nombre, u.foto_perfil FROM resenia r
                                INNER JOIN usuarios u ON r.id_usuario = u.id
                                WHERE r.id_oferta = ?";
                if ($stmt_resenas = mysqli_prepare($conexion, $sql_resenas)) {
                    mysqli_stmt_bind_param($stmt_resenas, "i", $id_oferta);
                    if (mysqli_stmt_execute($stmt_resenas)) {
                        $resultado_resenas = mysqli_stmt_get_result($stmt_resenas);
                        if (mysqli_num_rows($resultado_resenas) > 0) {
                            while ($fila_resena = mysqli_fetch_assoc($resultado_resenas)) {
                                echo '<div class="card mb-3">';
                                echo '<div class="card-header">';
								echo '<img src="' . htmlspecialchars($fila_resena["foto_perfil"]) . '" alt="Foto de perfil" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">';
                                echo '<strong><a href="perfil.php?id=' . $fila_resena["id_usuario"] . '">' . htmlspecialchars($fila_resena["nombre"]) . '</a></strong>';
                                echo ' - Puntuación: ' . mostrarEstrellas($fila_resena["puntuacion"]);
                                if (isset($_SESSION['id']) && $_SESSION['id'] == $fila_resena['id_usuario']) {
                                    echo ' <button type="button" class="btn btn-sm btn-danger eliminar-resena-button" data-bs-toggle="modal" data-bs-target="#confirmDeleteReviewModal" data-reviewid="' . $fila_resena['id'] . '">Eliminar Reseña</button>';
                                }
                                echo '</div>';
                                echo '<div class="card-body">';
                                echo '<p class="card-text">' . htmlspecialchars($fila_resena["comentario"]) . '</p>';
                                echo '</div>';
								$sql_respuesta = "SELECT respuesta FROM respuestas WHERE id_resena = ?";
								if ($stmt_respuesta = mysqli_prepare($conexion, $sql_respuesta)) {
									mysqli_stmt_bind_param($stmt_respuesta, "i", $fila_resena['id']);
									if (mysqli_stmt_execute($stmt_respuesta)) {
										$resultado_respuesta = mysqli_stmt_get_result($stmt_respuesta);
										if ($fila_respuesta = mysqli_fetch_assoc($resultado_respuesta)) {
											echo '<div class="card-footer">';
											echo '<strong>Respuesta del propietario:</strong> ' . htmlspecialchars($fila_respuesta["respuesta"]);
											echo '</div>';
										} else {
											if (isset($_SESSION['id']) && $_SESSION['id'] == $fila['usuario_id']) {
												echo "
												<div class='mt-3 border rounded p-3'>
													<h5>Responder a esta reseña:</h5>
													<form action='procesar_respuestas.php' method='post' class='form-inline'>
														<input type='hidden' name='id_resena' value='" . $fila_resena['id'] . "'>
														<input type='hidden' name='id_usuario' value='" . $_SESSION['id'] . "'>
														<input type='hidden' name='id_oferta' value='" . $id_oferta . "'>
														<textarea name='respuesta' placeholder='Escribe tu respuesta...' class='form-control mr-2' rows='2'></textarea>
														<br>
														<div class='text-center'><button type='submit' class='btn btn-primary'>Responder</button></div>
													</form>
													<div id='respuestaDisplay'></div>
												</div>
												";
											}
										}
									}
									mysqli_stmt_close($stmt_respuesta);
								}
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No hay reseñas para esta oferta.</p>';
                        }
                        mysqli_stmt_close($stmt_resenas);
                    }
                }

                if (isset($_SESSION['id']) && !$esPropietario && !$yaHaResenado && $puedeResenar) {
                    echo '<div class="container mt-4">';
                    echo '<h3>Deja tu reseña</h3>';
                    echo '<form action="procesar_resena.php" method="post">';
                    echo '<div class="form-group">';
                    echo '<label for="puntuacion">Puntuación (1-5 estrellas):</label>';
                    echo '<input type="number" class="form-control" id="puntuacion" name="puntuacion" min="1" max="5" required>';
                    echo '</div>';
                    echo '<div class="form-group">';
                    echo '<label for="comentario">Comentario:</label>';
                    echo '<textarea class="form-control" id="comentario" name="comentario" rows="4" required></textarea>';
                    echo '</div>';
                    echo '<input type="hidden" name="id_oferta" value="' . $id_oferta . '">';
                    echo '<input type="hidden" name="id_usuario" value="' . $_SESSION['id'] . '">';
                    echo '<div class="form-group text-center">'; // Centra solo el botón "Publicar Reseña"
                    echo '<button type="submit" class="btn btn-primary">Publicar Reseña</button>';
                    echo '</div>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo '<div class="container mt-4">';
                echo '<div class="alert alert-danger" role="alert">Oferta no encontrada.</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="container mt-4">';
            echo '<div class="alert alert-danger" role="alert">Error en la consulta: ' . mysqli_error($conexion) . '</div>';
            echo '</div>';
        }
        mysqli_stmt_close($stmt);
    }
} else {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-danger" role="alert">ID de oferta inválido.</div>';
    echo '</div>';
}

mysqli_close($conexion);
?>

<!-- Modal para confirmar la eliminación de la oferta -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar esta oferta de alquiler? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDeleteButton" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar la eliminación de la reseña -->
<div class="modal fade" id="confirmDeleteReviewModal" tabindex="-1" aria-labelledby="confirmDeleteReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteReviewModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar esta reseña? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDeleteReviewButton" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Configurar el botón de eliminación de oferta
    $(document).on("click", ".eliminar-oferta-button", function () {
        var id_oferta = $(this).data('id');
        $("#confirmDeleteButton").attr("href", "detalles_alquiler.php?id=" + id_oferta + "&action=delete");
    });

    // Configurar el botón de eliminación de reseña
    $(document).on("click", ".eliminar-resena-button", function () {
        var reviewId = $(this).data('reviewid');
        $("#confirmDeleteReviewButton").attr("href", "detalles_alquiler.php?id=<?php echo $id_oferta; ?>&action=deleteReview&reviewId=" + reviewId);
    });
	
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</div>
<?php
include('footer.php');
?>
</body>
</html>
