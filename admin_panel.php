<?php
// Incluir el archivo de configuración de la base de datos
include('config.php');

// Verificar si la sesión está activa y si el usuario es administrador
session_start();
if (!isset($_SESSION['id']) || $_SESSION['admin'] != 1) {
    // Si no es un administrador, redirigir a otra página o mostrar un mensaje de error
    header("Location: index.php");
    exit();
}

// Procesar la modificación de estado de verificación
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id']) && isset($_POST['verificado'])) {
    $user_id = $_POST['user_id'];
    $verificado = $_POST['verificado'];
    $fecha_verificacion = null; // Inicializar la fecha de verificación

    // Si se establece como verificado (1), obtener la fecha de vencimiento
    if ($verificado == 1 && isset($_POST['fecha_verificacion'])) {
        $fecha_verificacion = $_POST['fecha_verificacion'];
    }

    // Actualizar el estado de verificación y la fecha de verificación del usuario en la base de datos
    $query = "UPDATE usuarios SET verificado = ?, fecha_verificacion = ? WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $verificado, $fecha_verificacion, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Procesar la eliminación de un usuario, sus alquileres asociados y sus reseñas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_usuario'])) {
    $user_id = $_POST['eliminar_usuario'];


	    // Eliminar respuestas asociadas a las reseñas del usuario
    $query = "DELETE FROM respuestas WHERE id_resena IN (SELECT id FROM resenia WHERE id_usuario = ?)";
    $stmt = mysqli_prepare($conexion, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }


    // Eliminar reseñas asociadas al usuario
    $query = "DELETE FROM resenia WHERE id_usuario = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Eliminar alquileres asociados al usuario
    $query = "DELETE FROM alquileres WHERE usuario_id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Eliminar el usuario
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// Obtener la lista de usuarios registrados
$query = "SELECT u.id, u.nombre, u.apellido, u.email, u.verificado, u.fecha_verificacion, 
          CASE WHEN v.usuario_id IS NOT NULL THEN 1 ELSE 0 END AS tiene_solicitud
          FROM usuarios u
          LEFT JOIN verificaciones v ON u.id = v.usuario_id
          ORDER BY tiene_solicitud DESC, u.id ASC";
$result = mysqli_query($conexion, $query);

$fecha_manana = date("Y-m-d", strtotime("+1 day"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    <style>
        /* Estilos para centrar el botón y separar los campos */
        .container {
            max-width: 600px; /* Ancho máximo del formulario */
            margin: 0 auto; /* Centrar el formulario en la página */
        }

        .container h1 {
            border-bottom: 2px solid #DAC0A3; /* Tercer color de la paleta */
            padding-bottom: 10px; /* Espaciado debajo del título */
            margin-bottom: 20px; /* Margen debajo del título */
            color: #102C57; /* Color más oscuro de la paleta */
        }

        .btn.btn-primary {
            background-color: #DAC0A3;
            border-color: #DAC0A3;
        }

        .btn.btn-primary:hover, .btn.btn-primary:focus, .btn.btn-primary:active {
            background-color: #EADBC8;
            border-color: #EADBC8;
        }

        .form-group {
            margin-bottom: 20px; /* Espacio entre los campos */
        }

        .btn-create-offer {
            margin-top: 20px;
        }
		
		a {
			text-decoration: none !important;
		}
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="container mt-4">
        <h1>Panel de Administración</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Email</th>
                    <th>Verificado</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td><a href='perfil.php?id={$row['id']}'>{$row['id']}</a></td>";
                    echo "<td>{$row['nombre']}</td>";
                    echo "<td>{$row['apellido']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['verificado']}</td>";
                    echo "<td>{$row['fecha_verificacion']}</td>";
                    echo "<td>";
                    echo '<form method="post">';
                    echo '<input type="hidden" name="user_id" value="' . $row['id'] . '">';
                    echo '<select name="verificado" class="form-select">';
                    echo '<option value="0" ' . ($row['verificado'] == 0 ? 'selected' : '') . '>No verificado</option>';
                    echo '<option value="1" ' . ($row['verificado'] == 1 ? 'selected' : '') . '>Verificado</option>';
                    echo '</select>';
                    echo '<div class="mb-3">';
                    echo '<label for="fecha_verificacion" class="form-label">Fecha de Vencimiento:</label>';
                    echo '<input type="date" class="form-control" name="fecha_verificacion" value="' . (empty($row['fecha_verificacion']) ? $fecha_manana : $row['fecha_verificacion']) . '" min="' . $fecha_manana . '">';
                    echo '</div>';
                    echo '<div class="text-center">';
                    echo '<button type="submit" class="btn btn-primary">Guardar</button>';
                    echo '</form>';
                    echo '<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-userid="' . $row['id'] . '">Eliminar Usuario</button>';
                    
                    // Agregar botón para ver ofertas del usuario
                    echo '<a href="ver_ofertas.php?user_id=' . $row['id'] . '" class="btn btn-info">Ver Ofertas</a>';
                    // Agregar el botón "Ver solicitud" aquí
					$query_verificacion = "SELECT * FROM verificaciones WHERE usuario_id = " . $row['id'];
					$result_verificacion = mysqli_query($conexion, $query_verificacion);
					if (mysqli_num_rows($result_verificacion) > 0) {
						echo '<a href="ver_solicitud.php?usuario_id=' . $row['id'] . '" class="btn btn-warning">Ver Solicitud</a>';
					} else {
						// Si no hay solicitud, mostrar el botón en gris y deshabilitado
						echo '<a href="#" class="btn btn-warning disabled">Ver Solicitud</a>';
					}
                    echo '</div>';
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar esta cuenta de usuario?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post">
                    <input type="hidden" name="eliminar_usuario" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <?php include('footer.php'); ?>

    <script>
        // Script para asignar el ID del usuario al input oculto del modal
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-userid');
            var modalInput = deleteModal.querySelector('#deleteUserId');
            modalInput.value = userId;
        });
    </script>
	
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
