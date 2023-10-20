<style>
    .container h1 {
        border-bottom: 2px solid #DAC0A3; /* Tercer color de la paleta */
        padding-bottom: 10px; /* Espaciado debajo del título */
        margin-bottom: 20px; /* Margen debajo del título */
        color: #102C57; /* Color más oscuro de la paleta */
    }



    .card {
        border: none; /* Eliminar el borde por defecto */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra suave */
    }

    .card-title, .card-text {
        color: #102C57; /* Color más oscuro de la paleta */
    }

    .btn-primary {
        background-color: #DAC0A3; /* Tercer color de la paleta */
        border-color: #DAC0A3; /* Tercer color de la paleta */
    }

    .btn-primary:hover {
        background-color: #C2B093; /* Variante más oscura del tercer color */
        border-color: #C2B093; /* Variante más oscura del tercer color */
    }

    .pagination .page-link {
        color: #102C57; /* Color más oscuro de la paleta */
    }

    .pagination .page-item.active .page-link {
        background-color: #DAC0A3; /* Tercer color de la paleta */
        border-color: #DAC0A3; /* Tercer color de la paleta */
    }
	
	.text-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 2; /* El número de líneas a mostrar. */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis; /* Esto añadirá los puntos suspensivos al final. */
	}

</style>

<?php
// Definir la cantidad de alquileres por página
$alquileresPorPagina = 6;

// Obtener la página actual
$paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Calcular el inicio de la consulta para la paginación
$inicioConsulta = ($paginaActual - 1) * $alquileresPorPagina;

// Consulta SQL para obtener los alquileres ordenados por fecha
$sql = "SELECT alquileres.*, usuarios.nombre, usuarios.apellido 
        FROM alquileres
        INNER JOIN usuarios ON alquileres.usuario_id = usuarios.id
        WHERE alquileres.activa = 1
        ORDER BY alquileres.fecha_publicacion DESC
        LIMIT ?, ?";

// Preparar la consulta
if ($stmt = mysqli_prepare($conexion, $sql)) {
    // Vincular parámetros a la consulta
    mysqli_stmt_bind_param($stmt, "ii", $inicioConsulta, $alquileresPorPagina);

    // Ejecutar la consulta
    if (mysqli_stmt_execute($stmt)) {
        $resultados = mysqli_stmt_get_result($stmt);

        // Mostrar resultados
        echo '<div class="container mt-5">';
        echo '<h1>Últimos alquileres</h1>';

        echo '<div class="row">';

        while ($fila = mysqli_fetch_assoc($resultados)) {
            $titulo = $fila['titulo'];
            $descripcion = $fila['descripcion'];
            $ubicacion = $fila['ubicacion'];
            $etiquetas = $fila['etiquetas'];
            $nombreUsuario = $fila['nombre'] . ' ' . $fila['apellido'];
            $galeriaFotos = json_decode($fila['galeria_fotos']);
            $idAlquiler = $fila['id'];

            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card">';
            echo '<div id="carouselExample' . $idAlquiler . '" class="carousel slide" data-ride="carousel">';
            echo '<div class="carousel-inner">';

            foreach ($galeriaFotos as $index => $foto) {
                echo '<div class="carousel-item';
                if ($index === 0) {
                    echo ' active';
                }
                echo '">';
                echo '<img src="' . htmlspecialchars($foto) . '" class="d-block w-100" alt="Imagen de alquiler" style="height: 400px; object-fit: cover;">';
                echo '</div>';
            }

            echo '</div>';
            echo '</div>';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . $titulo . '</h5>';
            echo '<p class="card-text text-clamp">' . $descripcion . '</p>';
            echo '<p class="card-text"><strong>Ubicación:</strong> ' . $ubicacion . '</p>';
            echo '<p class="card-text"><strong>Etiquetas:</strong> ' . $etiquetas . '</p>';
            echo '<p class="card-text"><strong>Publicado por:</strong> ' . $nombreUsuario . '</p>';
            echo '<div class="text-center">'; // Centrar el botón Ver Detalles
            echo '<a href="detalles_alquiler.php?id=' . $idAlquiler . '" class="btn btn-primary">Ver Detalles</a>';
            echo '</div>'; // Cierre del div centrado
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';

        // Calcular la cantidad total de páginas
        $sqlTotal = "SELECT COUNT(*) as total FROM alquileres WHERE activa = 1";
        $resultTotal = mysqli_query($conexion, $sqlTotal);
        $filaTotal = mysqli_fetch_assoc($resultTotal);
        $totalAlquileres = $filaTotal['total'];
        $totalPaginas = ceil($totalAlquileres / $alquileresPorPagina);

        // Mostrar la paginación
        echo '<nav aria-label="Navegación de páginas">';
        echo '<ul class="pagination justify-content-center">'; // Añadida la clase justify-content-center
        for ($i = 1; $i <= $totalPaginas; $i++) {
            echo '<li class="page-item';
            if ($i == $paginaActual) {
                echo ' active';
            }
            echo '"><a class="page-link" href="index.php?pagina=' . $i . '">' . $i . '</a></li>';
        }
        echo '</ul>';
        echo '</nav>';
        echo '</div>';
        mysqli_stmt_close($stmt);
    }
}
?>
