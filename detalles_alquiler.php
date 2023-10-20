<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Oferta de Alquiler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="estilos/estilo.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</head>
<body>

<?php
require_once('config.php');
require_once('header.php');
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

                echo '<div class="container mt-4">';
				echo '<h1>' . htmlspecialchars($fila["titulo"]) . '</h1>';
				echo '<p><strong>Puntuación general:</strong> ' . mostrarEstrellas($puntuacion_general) . '</p>'; // Mostrar puntuación general
				echo '<p><strong>Descripción:</strong> ' . htmlspecialchars($fila["descripcion"]) . '</p>';
                echo '<p><strong>Ubicación:</strong> ' . htmlspecialchars($fila["ubicacion"]) . '</p>';
                echo '<p><strong>Etiquetas:</strong> ' . htmlspecialchars($fila["etiquetas"]) . '</p>';
                echo '<p><strong>Costo de Alquiler por Día:</strong> $' . number_format($fila["costo_alquiler"], 2) . '</p>';
                echo '<p><strong>Tiempo Mínimo de Permanencia:</strong> ' . $fila["tiempo_minimo"] . ' días</p>';
                echo '<p><strong>Tiempo Máximo de Permanencia:</strong> ' . $fila["tiempo_maximo"] . ' días</p>';
                echo '<p><strong>Cupo de Personas:</strong> ' . $fila["cupo"] . '</p>';
                echo '<p><strong>Fecha de Inicio:</strong> ' . ($fila["fecha_inicio"] ? date("d/m/Y", strtotime($fila["fecha_inicio"])) : "No especificada") . '</p>';
                echo '<p><strong>Fecha de Fin:</strong> ' . ($fila["fecha_fin"] ? date("d/m/Y", strtotime($fila["fecha_fin"])) : "No especificada") . '</p>';
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

                echo '</div>';
                echo '</div>';

                echo '<div class="container mt-4 text-center">';
                echo '<div class="btn-group" role="group" aria-label="Botones">';

                if ($esPropietario) {
                    echo '<a href="modificar_oferta.php?id=' . $id_oferta . '" class="btn btn-primary">Modificar Oferta</a>';
                    echo '<button type="button" class="btn btn-danger eliminar-resena-button" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="' . $id_oferta . '">Eliminar Oferta</button>';
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

                echo '<div class="container mt-4">';
                echo '<h2>Reseñas</h2>';

                $sql_resenas = "SELECT r.*, u.nombre FROM resenia r
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
                                echo '<strong>' . htmlspecialchars($fila_resena["nombre"]) . '</strong>';
                                echo ' - Puntuación: ' . mostrarEstrellas($fila_resena["puntuacion"]);
                                if (isset($_SESSION['id']) && $_SESSION['id'] == $fila_resena['id_usuario']) {
                                    echo ' <button type="button" class="btn btn-sm btn-danger eliminar-resena-button" data-bs-toggle="modal" data-bs-target="#confirmDeleteReviewModal" data-reviewid="' . $fila_resena['id'] . '">Eliminar Reseña</button>';
                                }
                                echo '</div>';
                                echo '<div class="card-body">';
                                echo '<p class="card-text">' . htmlspecialchars($fila_resena["comentario"]) . '</p>';
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p>No hay reseñas para esta oferta.</p>';
                        }
                        mysqli_stmt_close($stmt_resenas);
                    }
                }

                if (isset($_SESSION['id']) && !$esPropietario && !$yaHaResenado) {
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

// Modal de confirmación para eliminar oferta
echo '<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>';
echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
echo '</div>';
echo '<div class="modal-body">';
echo '¿Estás seguro de que deseas eliminar esta oferta? Esta acción no se puede deshacer.';
echo '</div>';
echo '<div class="modal-footer">';
echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
echo '<form method="post" action="detalles_alquiler.php?id=' . $id_oferta . '&action=delete">';
echo '<button type="submit" class="btn btn-danger">Eliminar</button>';
echo '</form>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

// Modal de confirmación para eliminar reseña
echo '<div class="modal fade" id="confirmDeleteReviewModal" tabindex="-1" aria-labelledby="confirmDeleteReviewModalLabel" aria-hidden="true">';
echo '<div class="modal-dialog">';
echo '<div class="modal-content">';
echo '<div class="modal-header">';
echo '<h5 class="modal-title" id="confirmDeleteReviewModalLabel">Confirmar Eliminación de Reseña</h5>';
echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
echo '</div>';
echo '<div class="modal-body">';
echo '¿Estás seguro de que deseas eliminar tu reseña? Esta acción no se puede deshacer.';
echo '</div>';
echo '<div class="modal-footer">';
echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';

?>

<script>
    // Script para manejar la eliminación de la oferta
    $('#confirmDeleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var modal = $(this);
        modal.find('#confirmDeleteButton').attr('href', 'detalles_alquiler.php?id=' + id + '&action=delete');
    });

    // Script para manejar la eliminación de la reseña
    $('#confirmDeleteReviewModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var reviewId = button.data('reviewid');
        var modal = $(this);
        modal.find('#confirmDeleteReviewButton').attr('href', 'detalles_alquiler.php?id=<?php echo $id_oferta; ?>&action=deleteReview&reviewId=' + reviewId);
    });

</script>
</div>
<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>