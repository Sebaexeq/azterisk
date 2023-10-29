<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="estilos/estilo.css" rel="stylesheet">
    <title>Crear Nueva Oferta de Alquiler</title>
</head>
<body>
    <?php
    // Incluye el archivo de configuración de la base de datos y el encabezado
    require_once('config.php');
    require_once('header.php');
	$fecha_actual = date("Y-m-d");
    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION["id"])) {
        // Si el usuario no ha iniciado sesión, redirigirlo a la página de inicio de sesión
        header("Location: login.php");
        exit();
    }

    // Función para redimensionar una imagen a un tamaño específico (1200x800)
    function redimensionarImagen($imagen, $ancho, $alto)
    {
        list($ancho_original, $alto_original, $tipo) = getimagesize($imagen);

        $ratio = $ancho_original / $alto_original;

        if ($ancho / $alto > $ratio) {
            $ancho = $alto * $ratio;
        } else {
            $alto = $ancho / $ratio;
        }

        $imagen_redimensionada = imagecreatetruecolor($ancho, $alto);

        switch ($tipo) {
            case IMAGETYPE_JPEG:
                $imagen_original = imagecreatefromjpeg($imagen);
                break;
            case IMAGETYPE_PNG:
                $imagen_original = imagecreatefrompng($imagen);
                break;
            default:
                return false; // Tipo de imagen no soportado
        }

        imagecopyresampled($imagen_redimensionada, $imagen_original, 0, 0, 0, 0, $ancho, $alto, $ancho_original, $alto_original);

        return $imagen_redimensionada;
    }

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

        $usuario_id = $_SESSION["id"];
		// Verificar si el usuario está verificado
		$consulta_verificado = "SELECT verificado FROM usuarios WHERE id = $usuario_id";
		$resultado_verificado = mysqli_query($conexion, $consulta_verificado);
		$usuario = mysqli_fetch_assoc($resultado_verificado);
		
		if ($usuario['verificado'] == 0) { // Si el usuario NO está verificado
		// Verificar si el usuario ya tiene una oferta activa
		$consulta_oferta_activa = "SELECT COUNT(*) as total FROM alquileres WHERE usuario_id = $usuario_id AND activa = 1";
		$resultado_oferta_activa = mysqli_query($conexion, $consulta_oferta_activa);
		$oferta_activa = mysqli_fetch_assoc($resultado_oferta_activa);
		
		if ($oferta_activa['total'] > 0) { 
			echo '<div class="alert alert-danger mt-4" role="alert">';
			echo "<div class='text-center'>Ya tienes una oferta activa. No puedes crear otra hasta que la oferta actual expire o la desactives.</div>";
			echo '</div>';
			require_once('footer.php');
			exit; // Detener la ejecución del script
		}
		$fecha_actual = date('Y-m-d H:i:s');
		$sql = "INSERT INTO alquileres (usuario_id, titulo, descripcion, ubicacion, etiquetas, costo_alquiler, tiempo_minimo, tiempo_maximo, cupo, fecha_inicio, fecha_fin, activa, fecha_publicacion) 
		VALUES ('$usuario_id', '$titulo', '$descripcion', '$ubicacion', '$etiquetas', '$costo_alquiler', '$tiempo_minimo', '$tiempo_maximo', '$cupo', '$fecha_inicio', '$fecha_fin', 0, '$fecha_actual')";
	} else { // Si el usuario ESTÁ verificado
		$sql = "INSERT INTO alquileres (usuario_id, titulo, descripcion, ubicacion, etiquetas, costo_alquiler, tiempo_minimo, tiempo_maximo, cupo, fecha_inicio, fecha_fin, activa, fecha_publicacion) 
		VALUES ('$usuario_id', '$titulo', '$descripcion', '$ubicacion', '$etiquetas', '$costo_alquiler', '$tiempo_minimo', '$tiempo_maximo', '$cupo', '$fecha_inicio', '$fecha_fin', 1, '$fecha_actual')";
	}


        if (mysqli_query($conexion, $sql)) {
            $id_oferta = mysqli_insert_id($conexion);

			// Verificar si el usuario es regular o verificado
			$consulta_usuario = "SELECT verificado FROM usuarios WHERE id = $usuario_id";
			$resultado_usuario = mysqli_query($conexion, $consulta_usuario);
			$usuario = mysqli_fetch_assoc($resultado_usuario);

            $galeria_fotos = array();

            if (!empty($_FILES["fotos"]["name"][0])) {
                $total = count($_FILES["fotos"]["name"]);
                for ($i = 0; $i < $total; $i++) {
                    $nombre_archivo = $_FILES["fotos"]["name"][$i];
                    $ruta_archivo = "galeria/" . uniqid() . "_" . $nombre_archivo;

                    $imagen_redimensionada = redimensionarImagen($_FILES["fotos"]["tmp_name"][$i], 1200, 800);

                    if (!$imagen_redimensionada) {
                        echo '<div class="alert alert-danger" role="alert">Tipo de imagen no soportado.</div>';
                        continue;
                    }

                    if (imagejpeg($imagen_redimensionada, $ruta_archivo, 85)) {
                        $galeria_fotos[] = $ruta_archivo;
                    }

                    imagedestroy($imagen_redimensionada);
                }
            }

            if (!empty($galeria_fotos)) {
                $galeria_json = json_encode($galeria_fotos);
                $sql = "UPDATE alquileres SET galeria_fotos = ? WHERE id = ?";
                if ($stmt = mysqli_prepare($conexion, $sql)) {
                    mysqli_stmt_bind_param($stmt, "si", $galeria_json, $id_oferta);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }

            // Procesar los servicios seleccionados
            $servicios_seleccionados = isset($_POST["servicios"]) ? $_POST["servicios"] : [];
            $servicios_json = json_encode($servicios_seleccionados);

            // Actualizar el campo 'servicios' en la base de datos
            $sql = "UPDATE alquileres SET servicios = ? WHERE id = ?";
            if ($stmt = mysqli_prepare($conexion, $sql)) {
                mysqli_stmt_bind_param($stmt, "si", $servicios_json, $id_oferta);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            echo '<div class="alert alert-success text-center" role="alert">La oferta se ha creado exitosamente.</div>';
        } else {
            echo '<div class="alert alert-danger" role="alert">Error al crear la oferta: ' . mysqli_error($conexion) . '</div>';
        }
    }
    ?>
    
    <div class="container mt-4">
        <h1 class="text">Crear Nueva Oferta de Alquiler</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="ubicacion">Ubicación</label>
                <input type="text" class="form-control" id="ubicacion" name="ubicacion" required>
            </div>

            <div class="form-group">
                <label for="etiquetas">Etiquetas (separadas por comas)</label>
                <input type="text" class="form-control" id="etiquetas" name="etiquetas">
            </div>

            <div class="form-group">
                <label for="costo_alquiler">Costo de Alquiler por Día</label>
                <input type="number" min="1" class="form-control" id="costo_alquiler" name="costo_alquiler" required>
            </div>

            <div class="form-group">
                <label for="tiempo_minimo">Tiempo Mínimo de Permanencia (días)</label>
                <input type="number" min="1" class="form-control" id="tiempo_minimo" name="tiempo_minimo" required>
            </div>

            <div class="form-group">
                <label for="tiempo_maximo">Tiempo Máximo de Permanencia (días)</label>
                <input type="number" class="form-control" id="tiempo_maximo" name="tiempo_maximo" required>
            </div>

            <div class="form-group">
                <label for="cupo">Cupo de Personas</label>
                <input type="number" min="1" class="form-control" id="cupo" name="cupo" required>
            </div>

            <div class="form-group">
                <label for="fecha_inicio">Fecha de Inicio (opcional)</label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" min="<?php echo $fecha_actual; ?>">
            </div>

            <div class="form-group">
                <label for="fecha_fin">Fecha de Fin (opcional)</label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" min="<?php echo $fecha_actual; ?>">
            </div>

            <div class="form-group">
                <label>Servicios</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="cocina" name="servicios[]" value="Cocina">
                    <label class="form-check-label" for="cocina">Cocina</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="piscina" name="servicios[]" value="Piscina">
                    <label class="form-check-label" for="piscina">Piscina</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="spa" name="servicios[]" value="Spa">
                    <label class="form-check-label" for="spa">Spa</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="aire_acondicionado" name="servicios[]" value="Aire acondicionado">
                    <label class="form-check-label" for="aire_acondicionado">Aire acondicionado</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="limpieza" name="servicios[]" value="Limpieza">
                    <label class="form-check-label" for="limpieza">Limpieza</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="wifi" name="servicios[]" value="Wi-Fi">
                    <label class="form-check-label" for="wifi">Wi-Fi</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="desayuno" name="servicios[]" value="Desayuno">
                    <label class="form-check-label" for="desayuno">Desayuno</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="merienda" name="servicios[]" value="Merienda">
                    <label class="form-check-label" for="merienda">Merienda</label>
                </div>
            </div>

            <div class="form-group">
                <label for="fotos">Fotos de la Galería (1200x800 píxeles)</label>
                <input type="file" class="form-control-file" id="fotos" name="fotos[]" accept="image/*" multiple required>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-create-offer btn-block">Crear Oferta</button>
            </div>
        </form>
    </div>

    <?php
    // Incluye el pie de página
    require_once('footer.php');
    ?>
</body>
</html>
