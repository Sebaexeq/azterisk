<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Oferta de Alquiler</title>
    <link href="estilos/estilo.css" rel="stylesheet">
</head>
<body>
<?php
require_once('config.php');
require_once('header.php');
?>
<div class="container mt-4">
    <h1>Buscar Ofertas de Alquiler</h1>
    <form method="GET" action="buscador.php">
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Buscar ofertas de alquiler..." name="q">
            <div class="input-group-append">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
            </div>
        </div>
    </form>

    <?php
    if (isset($_GET['q'])) {
        $busqueda = $_GET['q'];

        // Si no se proporciona una consulta de búsqueda, selecciona todas las ofertas de alquiler activas
        if (empty($busqueda)) {
            $sql = "SELECT * FROM alquileres WHERE activa = 1";
        } else {
            $sql = "SELECT * FROM alquileres WHERE activa = 1 AND 
                    (titulo LIKE '%" . $busqueda . "%' OR descripcion LIKE '%" . $busqueda . "%')";
        }

        $resultado = mysqli_query($conexion, $sql);

        if ($resultado) {
            // Mostrar los resultados de la búsqueda
            echo '<h2>Resultados de la búsqueda</h2>';

            if (mysqli_num_rows($resultado) > 0) {
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    // Mostrar detalles de la oferta de alquiler
                    echo '<div class="card mb-4">';
                    echo '<div class="row g-0">';
                    echo '<div class="col-md-4">';
                    echo '<img src="' . json_decode($fila["galeria_fotos"])[0] . '" class="img-thumbnail" alt="Imagen del alquiler">';
                    echo '</div>';
                    echo '<div class="col-md-8">';
                    echo '<div class="card-body">';
                    echo '<h3 class="card-title">' . htmlspecialchars($fila["titulo"]) . '</h3>';
                    echo '<p class="card-text">' . htmlspecialchars($fila["descripcion"]) . '</p>';
                    echo '<a href="detalles_alquiler.php?id=' . $fila["id"] . '" class="btn btn-primary">Ver Detalles</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p>No se encontraron resultados.</p>';
            }
        } else {
            echo '<div class="alert alert-danger" role="alert">Error en la búsqueda: ' . mysqli_error($conexion) . '</div>';
        }
    }
    ?>
</div>
<?php
require_once('footer.php');
?>
</body>
</html>