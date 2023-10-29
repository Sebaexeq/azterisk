<!DOCTYPE html>
<html lang="es">
<head>
    <link href="estilos/estilo.css" rel="stylesheet">
</head>
<body>

<?php
require_once('config.php');
require_once('header.php');

$hoy = date('Y-m-d');

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id_oferta = $_GET["id"];

    $sql = "SELECT a.*, u.id AS usuario_id FROM alquileres a
            INNER JOIN usuarios u ON a.usuario_id = u.id
            WHERE a.id = ?";
    if ($stmt = mysqli_prepare($conexion, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id_oferta);
        if (mysqli_stmt_execute($stmt)) {
            $resultado = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($resultado) == 1) {
                $fila = mysqli_fetch_assoc($resultado);

                if (isset($_SESSION['id']) && $_SESSION['id'] == $fila['usuario_id']) {
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $titulo = $_POST["titulo"];
                        $descripcion = $_POST["descripcion"];
                        $ubicacion = $_POST["ubicacion"];
                        $etiquetas = $_POST["etiquetas"];
                        $costo_alquiler = $_POST["costo_alquiler"];
                        $tiempo_minimo = $_POST["tiempo_minimo"];
                        $tiempo_maximo = $_POST["tiempo_maximo"];
                        $cupo = $_POST["cupo"];
                        $fecha_inicio = $_POST["fecha_inicio"];
                        $fecha_fin = $_POST["fecha_fin"];

                        // Procesar y guardar los servicios modificados
                        $servicios = $_POST['servicios'];
                        $servicios_json = json_encode($servicios);

                        // Procesar y guardar las nuevas fotos del alquiler
                        $fotosSubidas = false;
                        $rutas_fotos = json_decode($fila['galeria_fotos'], true) ?: [];

                        if (!empty($_FILES['fotos']['name'][0])) {
                            for ($i = 0; $i < count($_FILES['fotos']['name']); $i++) {
                                $nombre_archivo = basename($_FILES['fotos']['name'][$i]);
                                $ruta_destino = "galeria/" . $nombre_archivo;
                                if (move_uploaded_file($_FILES['fotos']['tmp_name'][$i], $ruta_destino)) {
                                    $rutas_fotos[] = $ruta_destino;
                                    $fotosSubidas = true;
                                }
                            }
                        }

                        if ($fotosSubidas) {
                            $galeria_fotos_json = json_encode($rutas_fotos);
                            $sql_update = "UPDATE alquileres SET 
                                    titulo = ?, 
                                    descripcion = ?, 
                                    ubicacion = ?, 
                                    etiquetas = ?, 
                                    costo_alquiler = ?, 
                                    tiempo_minimo = ?, 
                                    tiempo_maximo = ?, 
                                    cupo = ?, 
                                    fecha_inicio = ?, 
                                    fecha_fin = ?, 
                                    servicios = ?, 
                                    galeria_fotos = ? 
                                    WHERE id = ?";
                            if ($stmt_update = mysqli_prepare($conexion, $sql_update)) {
                                mysqli_stmt_bind_param($stmt_update, "ssssssssssssi", $titulo, $descripcion, $ubicacion, $etiquetas, $costo_alquiler, $tiempo_minimo, $tiempo_maximo, $cupo, $fecha_inicio, $fecha_fin, $servicios_json, $galeria_fotos_json, $id_oferta);
                                if (mysqli_stmt_execute($stmt_update)) {
                                    echo '<div class="container mt-4">';
                                    echo '<div class="alert alert-success" role="alert">La oferta se ha actualizado exitosamente.</div>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="container mt-4">';
                                    echo '<div class="alert alert-danger" role="alert">Error al actualizar la oferta: ' . mysqli_error($conexion) . '</div>';
                                    echo '</div>';
                                }
                            }
                        } else {
                            $sql_update = "UPDATE alquileres SET 
                                    titulo = ?, 
                                    descripcion = ?, 
                                    ubicacion = ?, 
                                    etiquetas = ?, 
                                    costo_alquiler = ?, 
                                    tiempo_minimo = ?, 
                                    tiempo_maximo = ?, 
                                    cupo = ?, 
                                    fecha_inicio = ?, 
                                    fecha_fin = ?, 
                                    servicios = ? 
                                    WHERE id = ?";
                            if ($stmt_update = mysqli_prepare($conexion, $sql_update)) {
                                mysqli_stmt_bind_param($stmt_update, "sssssssssssi", $titulo, $descripcion, $ubicacion, $etiquetas, $costo_alquiler, $tiempo_minimo, $tiempo_maximo, $cupo, $fecha_inicio, $fecha_fin, $servicios_json, $id_oferta);
                                if (mysqli_stmt_execute($stmt_update)) {
                                    echo '<div class="container mt-4">';
                                    echo '<div class="alert alert-success" role="alert">La oferta se ha actualizado exitosamente.</div>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="container mt-4">';
                                    echo '<div class="alert alert-danger" role="alert">Error al actualizar la oferta: ' . mysqli_error($conexion) . '</div>';
                                    echo '</div>';
                                }
                            }
                        }
                    } else {
                        echo '<div class="container mt-4">';
                        echo '<h1>Modificar Oferta de Alquiler</h1>';
                        echo '<form method="POST" enctype="multipart/form-data">';
                        echo '<div class="form-group">';
                        echo '<label for="titulo">Título</label>';
                        echo '<input type="text" class="form-control" id="titulo" name="titulo" value="' . htmlspecialchars($fila["titulo"]) . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="descripcion">Descripción</label>';
                        echo '<textarea class="form-control" id="descripcion" name="descripcion" required>' . htmlspecialchars($fila["descripcion"]) . '</textarea>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="ubicacion">Ubicación</label>';
                        echo '<input type="text" class="form-control" id="ubicacion" name="ubicacion" value="' . htmlspecialchars($fila["ubicacion"]) . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="etiquetas">Etiquetas</label>';
                        echo '<input type="text" class="form-control" id="etiquetas" name="etiquetas" value="' . htmlspecialchars($fila["etiquetas"]) . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="costo_alquiler">Costo de Alquiler por Día</label>';
                        echo '<input type="number" class="form-control" id="costo_alquiler" name="costo_alquiler" value="' . htmlspecialchars($fila["costo_alquiler"]) . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="tiempo_minimo">Tiempo Mínimo de Permanencia (días)</label>';
                        echo '<input type="number" class="form-control" id="tiempo_minimo" name="tiempo_minimo" value="' . htmlspecialchars($fila["tiempo_minimo"]) . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="tiempo_maximo">Tiempo Máximo de Permanencia (días)</label>';
                        echo '<input type="number" class="form-control" id="tiempo_maximo" name="tiempo_maximo" value="' . htmlspecialchars($fila["tiempo_maximo"]) . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="cupo">Cupo</label>';
                        echo '<input type="number" class="form-control" id="cupo" name="cupo" value="' . htmlspecialchars($fila["cupo"]) . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="fecha_inicio">Fecha de Inicio</label>';
                        echo '<input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="' . htmlspecialchars($fila["fecha_inicio"]) . '" min="' . $hoy . '" required>';
                        echo '</div>';
                        echo '<div class="form-group">';
                        echo '<label for="fecha_fin">Fecha de Finalización</label>';
                        echo '<input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="' . htmlspecialchars($fila["fecha_fin"]) . '" min="' . $hoy . '" required>';
                        echo '</div>';

                        // Campo para modificar servicios
                        echo '<div class="form-group">';
                        echo '<label for="servicios">Servicios incluidos:</label>';
                        $servicios_incluidos = json_decode($fila['servicios'], true);
						if (!is_array($servicios_incluidos)) {
							$servicios_incluidos = [];
						}
                        $servicios = ["Cocina", "Piscina", "Spa", "Aire acondicionado", "Limpieza", "Wi-Fi", "Desayuno", "Merienda"];
                        foreach ($servicios as $servicio) {
                            echo '<div class="form-check">';
                            echo '<input class="form-check-input" type="checkbox" name="servicios[]" value="' . $servicio . '"' . (in_array($servicio, $servicios_incluidos) ? ' checked' : '') . '>';
                            echo '<label class="form-check-label" for="' . $servicio . '">' . $servicio . '</label>';
                            echo '</div>';
                        }
                        echo '</div>';

                        // Campo para modificar fotos
                        echo '<div class="form-group">';
                        echo '<label for="fotos">Fotos del alquiler:</label>';
                        echo '<input type="file" class="form-control" id="fotos" name="fotos[]" multiple>'; // Aceptar múltiples archivos
                        echo '</div>';

                        echo '<div class="text-center"><button type="submit" class="btn btn-primary">Guardar Cambios</button></div>';
                        echo '</form>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="container mt-4">';
                    echo '<div class="alert alert-danger" role="alert">No tienes permiso para modificar esta oferta.</div>';
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
    echo '<div class="alert alert-danger" role="alert">ID de oferta no válido.</div>';
    echo '</div>';
}

require_once('footer.php');
?>

</body>
</html>
