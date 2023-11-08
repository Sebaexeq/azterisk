<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="estilos/estilo.css" rel="stylesheet">
    <title>Crear Nueva Oferta de Alquiler</title>
</head>
<body>
    <?php
    require_once('config.php');
    require_once('header.php');

    function verificarExtensionesImagenes($imagenes) {
        $formatosPermitidos = ['jpg', 'jpeg', 'png', 'avif', 'webp'];

        foreach ($imagenes["name"] as $nombre) {
            $extension = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (!in_array($extension, $formatosPermitidos)) {
                return false;
            }
        }
        return true;
    }

    $fecha_actual = date("Y-m-d");

    if (!isset($_SESSION["id"])) {
        header("Location: login.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!verificarExtensionesImagenes($_FILES["fotos"])) {
            echo '<div class="alert alert-danger text-center" role="alert">Por favor, sube imágenes en formatos válidos: jpg, jpeg, png, avif o webp.</div>';
        } else {
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

            $galeria_fotos = array();
            if (!empty($_FILES["fotos"]["name"][0])) {
                $total = count($_FILES["fotos"]["name"]);
                for ($i = 0; $i < $total; $i++) {
                    $nombre_archivo = $_FILES["fotos"]["name"][$i];
                    $ruta_temporal = $_FILES["fotos"]["tmp_name"][$i];
                    $ruta_archivo = "galeria/" . uniqid() . "_" . $nombre_archivo;

                    if (move_uploaded_file($ruta_temporal, $ruta_archivo)) {
                        $galeria_fotos[] = $ruta_archivo;
                    }
                }
            }
            $galeria_json = json_encode($galeria_fotos);

            $consulta_verificado = "SELECT verificado FROM usuarios WHERE id = $usuario_id";
            $resultado_verificado = mysqli_query($conexion, $consulta_verificado);
            $usuario = mysqli_fetch_assoc($resultado_verificado);

            if ($usuario['verificado'] == 0) {
                $consulta_oferta_activa = "SELECT COUNT(*) as total FROM alquileres WHERE usuario_id = $usuario_id AND activa = 1";
                $resultado_oferta_activa = mysqli_query($conexion, $consulta_oferta_activa);
                $oferta_activa = mysqli_fetch_assoc($resultado_oferta_activa);

                if ($oferta_activa['total'] > 0) {
                    echo '<div class="alert alert-danger mt-4" role="alert">';
                    echo "<div class='text-center'>Ya tienes una oferta activa. No puedes crear otra hasta que la oferta actual expire o la desactives.</div>";
                    echo '</div>';
                    require_once('footer.php');
                    exit;
                }
                $sql = "INSERT INTO alquileres (usuario_id, titulo, descripcion, ubicacion, etiquetas, costo_alquiler, tiempo_minimo, tiempo_maximo, cupo, fecha_inicio, fecha_fin, activa, galeria_fotos) 
                VALUES ('$usuario_id', '$titulo', '$descripcion', '$ubicacion', '$etiquetas', '$costo_alquiler', '$tiempo_minimo', '$tiempo_maximo', '$cupo', '$fecha_inicio', '$fecha_fin', 0, '$galeria_json')";
            } else {
                $sql = "INSERT INTO alquileres (usuario_id, titulo, descripcion, ubicacion, etiquetas, costo_alquiler, tiempo_minimo, tiempo_maximo, cupo, fecha_inicio, fecha_fin, activa, galeria_fotos) 
                VALUES ('$usuario_id', '$titulo', '$descripcion', '$ubicacion', '$etiquetas', '$costo_alquiler', '$tiempo_minimo', '$tiempo_maximo', '$cupo', '$fecha_inicio', '$fecha_fin', 1, '$galeria_json')";
            }

            if (mysqli_query($conexion, $sql)) {
                $id_oferta = mysqli_insert_id($conexion);
                $servicios_seleccionados = isset($_POST["servicios"]) ? $_POST["servicios"] : [];
                $servicios_json = json_encode($servicios_seleccionados);

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

            <div class="form-group text-center">
                <label for="fotos">Fotos de la Galería</label>
                <input type="file" class="form-control-file" id="fotos" name="fotos[]" accept="image/jpeg, image/png, image/avif, image/webp" multiple required>
            </div>
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-create-offer btn-block">Crear Oferta</button>
            </div>
        </form>
    </div>

    <?php
    require_once('footer.php');
    ?>
</body>
</html>
