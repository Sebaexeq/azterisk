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

	.badge-destacado {
        background-color: #FF0000; /* Color dorado para destacado */
		z-index: 10; /* Un valor más alto que cualquier otro elemento con el que pueda solaparse */
        color: #FFF;
    }
    .badge-recomendado {
        background-color: #32CD32; /* Color verde para recomendado */
		z-index: 10; /* Un valor más alto que cualquier otro elemento con el que pueda solaparse */
        color: #FFF;
    }
	a {
			text-decoration: none !important;
		}
</style>

<?php
// Definir la cantidad de alquileres por página
$alquileresPorPagina = 6;

// Obtener la página actual
$paginaActual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Calcular el inicio de la consulta para la paginación
$inicioConsulta = ($paginaActual - 1) * $alquileresPorPagina;

// Consulta SQL para obtener el alquiler destacado
$sqlDestacado = "SELECT alquileres.*, usuarios.nombre, usuarios.apellido 
                 FROM alquileres
                 INNER JOIN usuarios ON alquileres.usuario_id = usuarios.id
                 WHERE alquileres.activa = 1 AND usuarios.verificado = 1
                 ORDER BY RAND()
                 LIMIT 1";
$resultadoDestacado = mysqli_query($conexion, $sqlDestacado);
$alquilerDestacado = mysqli_fetch_assoc($resultadoDestacado);

// Consulta SQL para obtener el alquiler recomendado
$sqlRecomendado = "SELECT alquileres.*, usuarios.nombre, usuarios.apellido, AVG(resenia.puntuacion) as promedio
                   FROM alquileres
                   INNER JOIN usuarios ON alquileres.usuario_id = usuarios.id
                   LEFT JOIN resenia ON alquileres.id = resenia.id_oferta
                   WHERE alquileres.activa = 1
                   GROUP BY alquileres.id
                   HAVING promedio >= 4.0
                   ORDER BY RAND()
                   LIMIT 1";
$resultadoRecomendado = mysqli_query($conexion, $sqlRecomendado);
$alquilerRecomendado = mysqli_fetch_assoc($resultadoRecomendado);

// Consulta SQL para obtener los alquileres ordenados por fecha
$sql = "SELECT alquileres.*, usuarios.nombre, usuarios.apellido 
        FROM alquileres
        INNER JOIN usuarios ON alquileres.usuario_id = usuarios.id
        WHERE alquileres.activa = 1
        ORDER BY alquileres.fecha_publicacion DESC
        LIMIT ?, ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "ii", $inicioConsulta, $alquileresPorPagina);
mysqli_stmt_execute($stmt);
$resultados = mysqli_stmt_get_result($stmt);

echo '<div class="container mt-5">';
echo '<h1>Últimos alquileres</h1>';
echo '<div class="row">';

// Función para mostrar alquiler
function mostrarAlquiler($fila, $badge = null) {
    $titulo = $fila['titulo'];
    $descripcion = $fila['descripcion'];
    $ubicacion = $fila['ubicacion'];
    $etiquetas = explode(',', $fila["etiquetas"]);
    $nombreUsuario = $fila['nombre'] . ' ' . $fila['apellido'];
    $galeriaFotos = json_decode($fila['galeria_fotos']);
    $idAlquiler = $fila['id'];

    echo '<div class="col-md-6 mb-4">';
    echo '<div class="card">';
    if ($badge) {
        echo '<span class="badge ' . $badge . ' position-absolute top-0 end-0 mt-2 me-2">' . ucfirst(str_replace('badge-', '', $badge)) . '</span>';
    }
    echo '<div class="carousel slide" data-ride="carousel">';
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
    echo '<p><strong>Etiquetas:</strong> ';
				foreach ($etiquetas as $q) {
					$q = trim($q);
					echo '<a href="buscador.php?q=' . urlencode($q) . '" class="q">#' . htmlspecialchars($q) . '</a> ';
				}
				echo '</p>';
    echo '<p class="card-text"><strong>Publicado por:</strong> ' . $nombreUsuario . '</p>';
    echo '<div class="text-center">';
    echo '<a href="detalles_alquiler.php?id=' . $idAlquiler . '" class="btn btn-primary">Ver Detalles</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

// Mostrar alquiler destacado
if ($alquilerDestacado) {
    mostrarAlquiler($alquilerDestacado, 'badge-destacado');
}

// Mostrar alquiler recomendado
if ($alquilerRecomendado) {
    mostrarAlquiler($alquilerRecomendado, 'badge-recomendado');
}

// Mostrar los demás alquileres
while ($fila = mysqli_fetch_assoc($resultados)) {
    mostrarAlquiler($fila);
}

echo '</div>'; // Cierre del div row

// Calcular la cantidad total de páginas
$sqlTotal = "SELECT COUNT(*) as total FROM alquileres WHERE activa = 1";
$resultTotal = mysqli_query($conexion, $sqlTotal);
$filaTotal = mysqli_fetch_assoc($resultTotal);
$totalAlquileres = $filaTotal['total'];
$totalPaginas = ceil($totalAlquileres / $alquileresPorPagina);

// Mostrar la paginación
echo '<nav aria-label="Navegación de páginas">';
echo '<ul class="pagination justify-content-center">';
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
?>
